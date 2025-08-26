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
      // Skip authorization for now to debug
      // $this->authorize('register', $event); 
      
      // Fix: Handle different date/time formats properly
      if (strpos($event->event_date, ' ') !== false) {
        // event_date already contains time
        $startAt = Carbon::parse($event->event_date);
      } else {
        // event_date is just date, append start_time
        $startAt = Carbon::parse($event->event_date.' '.$event->start_time);
      }
      if(now()->greaterThanOrEqualTo($startAt)){
        return response()->json(['message'=>'Registration closed'],403);
      }
      
      $exists = Registration::where('user_id',$r->user()->id)
                ->where('event_id',$event->id)
                ->exists();
      if($exists) return response()->json(['message'=>'Already registered'],409);

      $plain = str_pad((string)random_int(0,9999999999),10,'0',STR_PAD_LEFT);
      $reg = Registration::create([
        'user_id'=>$r->user()->id,
        'event_id'=>$event->id,
        'token_hash'=>Hash::make($plain),
        'token_sent_at'=>now(),
      ]);
      
      // Skip job dispatch for now to debug
      // SendRegistrationTokenJob::dispatch($r->user(), $event, $plain);
      
      return response()->json([
        'message'=>'Registered successfully',
        'registration_id' => $reg->id,
        'token' => $plain // Remove this in production
      ],201);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Registration failed',
        'error' => $e->getMessage(),
        'debug' => [
          'user_id' => $r->user()->id ?? 'null',
          'event_id' => $event->id ?? 'null'
        ]
      ], 500);
    }
  }

  public function myHistory(Request $r){
    return Attendance::with('registration.event')
      ->where('user_id',$r->user()->id)
      ->orderByDesc('attendance_time')
      ->get();
  }
}
