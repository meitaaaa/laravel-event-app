<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Jobs\GenerateCertificatePdfJob;

class AttendanceController extends Controller
{
    public function status(Event $event){
        try {
            $now = Carbon::now();
            $eventDate = Carbon::parse($event->event_date);
            $eventStartTime = Carbon::parse($event->event_date . ' ' . $event->start_time);
            
            // Check if event date has passed (event is in the past)
            $isEventPassed = $eventDate->lt(Carbon::now()->startOfDay());
            
            // Check if today is the event date
            $isEventDay = $now->toDateString() === $eventDate->toDateString();
            
            // Check if current time is after event start time
            $isAfterStartTime = $now->greaterThanOrEqualTo($eventStartTime);
            
            // Attendance is active only on event day, after start time, and event hasn't passed
            $isActive = $isEventDay && $isAfterStartTime && !$isEventPassed;
            
            $message = '';
            if ($isEventPassed) {
                $message = 'Event ini sudah selesai. Absensi tidak dapat dilakukan lagi.';
            } elseif (!$isEventDay) {
                $message = 'Tombol absensi hanya aktif pada hari kegiatan (' . $eventDate->format('d/m/Y') . ')';
            } elseif (!$isAfterStartTime) {
                $message = 'Tombol absensi akan aktif setelah jam ' . Carbon::parse($event->start_time)->format('H:i');
            } else {
                $message = 'Tombol absensi aktif. Silakan masukkan token untuk absensi.';
            }
            
            return response()->json([
                'active' => $isActive,
                'event_id' => $event->id,
                'event_date' => $event->event_date,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'current_time' => $now->toDateTimeString(),
                'is_event_day' => $isEventDay,
                'is_after_start_time' => $isAfterStartTime,
                'is_event_passed' => $isEventPassed,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error checking attendance status',
                'error' => $e->getMessage(),
                'debug' => [
                    'event_id' => $event->id ?? 'null',
                    'event_date' => $event->event_date ?? 'null',
                    'start_time' => $event->start_time ?? 'null'
                ]
            ], 500);
        }
    }

    public function submit(Request $request, Event $event){
        try {
            $token = $request->input('token');
            
            // Log incoming request for debugging
            \Log::info('Attendance submission attempt', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'token_received' => $token,
                'token_length' => strlen($token ?? ''),
                'user_id' => $request->user()->id ?? 'guest'
            ]);
            
            if (empty($token)) {
                return response()->json([
                    'message' => 'Token absensi harus diisi'
                ], 422);
            }
            
            // Validate token format (should be 10 digits)
            if (!preg_match('/^\d{10}$/', $token)) {
                \Log::warning('Invalid token format', [
                    'token' => $token,
                    'event_id' => $event->id
                ]);
                return response()->json([
                    'message' => 'Format token tidak valid. Token harus berupa 10 digit angka.'
                ], 422);
            }
            
            // Validasi: Absensi hanya dapat dilakukan pada hari event dan setelah jam mulai
            $now = Carbon::now();
            $eventDate = Carbon::parse($event->event_date);
            $eventStartTime = Carbon::parse($event->event_date . ' ' . $event->start_time);
            
            // Check if event date has passed
            $isEventPassed = $eventDate->lt(Carbon::now()->startOfDay());
            
            if ($isEventPassed) {
                return response()->json([
                    'message' => 'Event ini sudah selesai. Absensi tidak dapat dilakukan lagi.'
                ], 422);
            }
            
            $isEventDay = $now->toDateString() === $eventDate->toDateString();
            $isAfterStartTime = $now->greaterThanOrEqualTo($eventStartTime);
            
            if (!$isEventDay) {
                return response()->json([
                    'message' => 'Absensi hanya dapat dilakukan pada hari kegiatan (' . $eventDate->format('d/m/Y') . ')'
                ], 422);
            }
            
            if (!$isAfterStartTime) {
                return response()->json([
                    'message' => 'Absensi belum dapat dilakukan. Silakan tunggu hingga jam ' . Carbon::parse($event->start_time)->format('H:i')
                ], 422);
            }
            
            // Find registration by attendance_token (not hashed token)
            $registration = Registration::where('event_id', $event->id)
                ->where('attendance_token', $token)
                ->first();
            
            if (!$registration) {
                // Enhanced debugging for token not found
                \Log::error('Token not found in database', [
                    'event_id' => $event->id,
                    'token_searched' => $token,
                    'total_registrations' => Registration::where('event_id', $event->id)->count(),
                    'sample_tokens' => Registration::where('event_id', $event->id)
                        ->limit(3)
                        ->pluck('attendance_token', 'id')
                        ->toArray()
                ]);
                
                return response()->json([
                    'message' => 'Token tidak valid atau tidak ditemukan untuk event ini. Pastikan Anda memasukkan token 10 digit yang dikirim ke email Anda.'
                ], 404);
            }
            
            // Log successful token match
            \Log::info('Token matched successfully', [
                'registration_id' => $registration->id,
                'user_id' => $registration->user_id,
                'event_id' => $event->id
            ]);
            
            // Check if already attended
            $existingAttendance = Attendance::where('registration_id', $registration->id)->first();
            if ($existingAttendance) {
                return response()->json([
                    'message' => 'Anda sudah melakukan absensi untuk event ini',
                    'attendance_time' => $existingAttendance->attendance_time,
                    'certificate_available' => true
                ], 422);
            }
            
            // Create attendance record
            $attendance = Attendance::create([
                'registration_id' => $registration->id,
                'event_id' => $event->id,
                'user_id' => $registration->user_id,
                'token_entered' => $token,
                'status' => 'present',
                'attendance_time' => now()
            ]);
            
            // Update registration status to indicate attendance completed
            $registration->update([
                'attendance_status' => 'present',
                'attended_at' => now()
            ]);
            
            // Generate certificate automatically after attendance
            try {
                GenerateCertificatePdfJob::dispatch($registration, 'random');
                \Log::info('Certificate generation job dispatched', [
                    'registration_id' => $registration->id,
                    'user_id' => $registration->user_id,
                    'event_id' => $event->id
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to dispatch certificate generation job', [
                    'error' => $e->getMessage(),
                    'registration_id' => $registration->id
                ]);
            }
            
            return response()->json([
                'message' => 'Absensi berhasil dicatat! Sertifikat sedang dibuat dan akan tersedia dalam beberapa saat.',
                'attendance' => $attendance,
                'certificate_available' => true,
                'certificate_status' => 'Sertifikat sedang diproses (30-60 detik)'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mencatat absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
