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
            $now = now();
            $start = Carbon::parse($event->event_date.' '.$event->start_time);
            $active = $now->isSameDay($event->event_date) && $now->greaterThanOrEqualTo($start);
            
            return response()->json([
                'active' => $active,
                'event_id' => $event->id,
                'event_date' => $event->event_date,
                'start_time' => $event->start_time,
                'current_time' => $now->toDateTimeString()
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

    public function submit(Request $r, Event $event){
        try {
            $r->validate(['token'=>'required|digits:10']);
            $now = now();
            
            // Fix: Handle different date/time formats properly
            if (strpos($event->event_date, ' ') !== false) {
                $start = Carbon::parse($event->event_date);
            } else {
                $start = Carbon::parse($event->event_date.' '.$event->start_time);
            }

            if(!$now->isSameDay($event->event_date) || $now->lt($start)){
                return response()->json(['message'=>'Attendance is not open yet'],403);
            }

            $reg = Registration::where('user_id',$r->user()->id)
                    ->where('event_id',$event->id)
                    ->first();
                    
            if(!$reg){
                return response()->json(['message'=>'Registration not found. Please register first.'],404);
            }

            if(!Hash::check($r->token, $reg->token_hash)){
                Attendance::create([
                    'registration_id'=>$reg->id,
                    'event_id'=>$event->id,
                    'user_id'=>$r->user()->id,
                    'token_entered'=>$r->token,
                    'status'=>'invalid',
                    'attendance_time'=>now()
                ]);
                return response()->json(['message'=>'Invalid token'],422);
            }

            if($reg->attendance){
                return response()->json(['message'=>'Already attended'],409);
            }

            $att = Attendance::create([
                'registration_id'=>$reg->id,
                'event_id'=>$event->id,
                'user_id'=>$r->user()->id,
                'token_entered'=>'******',
                'status'=>'present',
                'attendance_time'=>now()
            ]);

            // Skip certificate generation for now
            // GenerateCertificatePdfJob::dispatch($reg);

            return response()->json([
                'message'=>'Attendance recorded successfully',
                'attendance_id' => $att->id,
                'status' => 'present'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Attendance submission failed',
                'error' => $e->getMessage(),
                'debug' => [
                    'user_id' => $r->user()->id ?? 'null',
                    'event_id' => $event->id ?? 'null',
                    'token_provided' => isset($r->token) ? 'yes' : 'no'
                ]
            ], 500);
        }
    }
}
