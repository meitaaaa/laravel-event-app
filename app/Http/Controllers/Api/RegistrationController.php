<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Jobs\SendRegistrationTokenJob;

class RegistrationController extends Controller {
  public function register(Request $r, Event $event){
    try {
      // Ensure authenticated user (defensive even with middleware)
      $user = $r->user();
      if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
      }

      // Simple validation without Laravel validator to avoid issues
      if (empty($r->name) || empty($r->email) || empty($r->phone)) {
        return response()->json([
          'message' => 'Nama, email, dan nomor HP harus diisi'
        ], 422);
      }
      
      // Validasi: Cek apakah event sudah kadaluarsa (tanggal sudah lewat)
      try {
        $eventDate = Carbon::parse($event->event_date)->startOfDay();
        $today = Carbon::now()->startOfDay();
        
        // Check if event date has passed
        if ($eventDate->lt($today)) {
          return response()->json([
            'message' => 'Event sudah kadaluarsa. Pendaftaran tidak dapat dilakukan untuk event yang sudah terlewat tanggalnya.',
            'event_expired' => true
          ], 403);
        }
        
        // Additional check: registration closed when event starts
        if (strpos($event->event_date, ' ') !== false) {
          $eventDateTime = Carbon::parse($event->event_date);
        } else {
          $eventDateTime = Carbon::parse($event->event_date . ' ' . ($event->start_time ?? '00:00:00'));
        }
        
        if(now()->greaterThanOrEqualTo($eventDateTime)){
          return response()->json([
            'message' => 'Pendaftaran sudah ditutup. Event telah dimulai.',
            'registration_closed' => true
          ], 403);
        }
      } catch (\Exception $dtErr) {
        \Log::warning('Failed to parse event date for registration', [
          'event_id' => $event->id,
          'event_date' => $event->event_date,
          'start_time' => $event->start_time,
          'error' => $dtErr->getMessage(),
        ]);
        // Fallback: allow registration if parsing fails
      }
      
      $exists = Registration::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->exists();
      if($exists) return response()->json(['message'=>'Anda sudah terdaftar untuk event ini.'],409);

      $plain = str_pad((string)random_int(0,9999999999),10,'0',STR_PAD_LEFT);
      $reg = Registration::create([
        'user_id'=>$user->id,
        'event_id'=>$event->id,
        'token_hash'=>Hash::make($plain),
        'token_plain'=>$plain,
        'attendance_token'=>$plain, // For attendance verification
        'token_sent_at'=>now(),
        'name' => $r->name,
        'email' => $r->email,
        'phone' => $r->phone,
        'motivation' => $r->motivation ?? null,
      ]);
      
      // Send token via email - make it synchronous for debugging
      try {
        SendRegistrationTokenJob::dispatchSync($user, $event, $plain);
      } catch (\Exception $emailError) {
        \Log::warning('Email sending failed but registration successful: ' . $emailError->getMessage());
        // Don't fail registration if email fails
      }
      
      return response()->json([
        'message'=>'Registered successfully',
        'registration_id' => $reg->id
      ],201);
    } catch (\Exception $e) {
      \Log::error('Registration failed: ' . $e->getMessage(), [
        'user_id' => optional($r->user())->id,
        'event_id' => $event->id ?? 'null',
        'trace' => $e->getTraceAsString(),
        'request_data' => $r->all()
      ]);
      
      // Return more specific error message
      $errorMessage = 'Registration failed: ' . $e->getMessage();
      if (str_contains($e->getMessage(), 'validation')) {
        $errorMessage = 'Data tidak valid. Pastikan semua field diisi dengan benar.';
      } elseif (str_contains($e->getMessage(), 'Duplicate entry')) {
        $errorMessage = 'Anda sudah terdaftar untuk event ini.';
      } elseif (str_contains($e->getMessage(), 'Unauthenticated')) {
        $errorMessage = 'Sesi Anda telah berakhir. Silakan login kembali.';
      }
      
      return response()->json([
        'message' => $errorMessage,
        'error' => $e->getMessage(),
        'debug' => [
          'user_id' => optional($r->user())->id,
          'event_id' => $event->id ?? 'null',
          'line' => $e->getLine(),
          'file' => basename($e->getFile())
        ]
      ], 500);
    }
  }

  public function cancelRegistration(Request $r, Registration $registration){
    try {
      // Check if user owns this registration
      if($registration->user_id !== $r->user()->id) {
        return response()->json(['message'=>'Unauthorized'],403);
      }

      // Check if event has started
      $event = $registration->event;
      if (strpos($event->event_date, ' ') !== false) {
        $startAt = Carbon::parse($event->event_date);
      } else {
        $startAt = Carbon::parse($event->event_date.' '.$event->start_time);
      }
      
      if(now()->greaterThanOrEqualTo($startAt)){
        return response()->json(['message'=>'Cannot cancel registration after event has started'],403);
      }

      // Check if user has attended
      $hasAttended = Attendance::where('registration_id', $registration->id)->exists();
      if($hasAttended) {
        return response()->json(['message'=>'Cannot cancel registration after attendance'],403);
      }

      // Handle payment refund if exists
      $payment = $registration->payment;
      if($payment && $payment->status === 'paid') {
        // Mark payment as cancelled (actual refund would need Midtrans API)
        $payment->update(['status' => 'cancelled']);
      }

      // Delete registration
      $registration->delete();

      return response()->json(['message'=>'Registration cancelled successfully'],200);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Failed to cancel registration',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function myRegistrations(Request $r){
    try {
      $registrations = Registration::with(['event', 'payment', 'attendance'])
        ->where('user_id', $r->user()->id)
        ->orderByDesc('created_at')
        ->get();
        
      return response()->json($registrations);
    } catch (\Exception $e) {
      \Log::error('MyRegistrations error: ' . $e->getMessage());
      return response()->json([
        'error' => 'Failed to fetch registrations',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function myHistory(Request $r){
    try {
      $history = Attendance::with('registration.event')
        ->whereHas('registration', function($query) use ($r) {
          $query->where('user_id', $r->user()->id);
        })
        ->orderByDesc('attendance_time')
        ->get();
        
      return response()->json($history);
    } catch (\Exception $e) {
      \Log::error('MyHistory error: ' . $e->getMessage());
      return response()->json([
        'error' => 'Failed to fetch history',
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
