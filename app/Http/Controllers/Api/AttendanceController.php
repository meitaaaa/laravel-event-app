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
        $now = now();
        $start = Carbon::parse($event->event_date.' '.$event->start_time);
        $active = $now->isSameDay($event->event_date) && $now->greaterThanOrEqualTo($start);
        return ['active'=>$active];
    }

    public function submit(Request $r, Event $event){
        $r->validate(['token'=>'required|digits:10']);
        $now = now();
        $start = Carbon::parse($event->event_date.' '.$event->start_time);

        if(!$now->isSameDay($event->event_date) || $now->lt($start)){
            return response()->json(['message'=>'Attendance is not open yet'],403);
        }

        $reg = Registration::where('user_id',$r->user()->id)
                ->where('event_id',$event->id)
                ->firstOrFail();

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

        // generate sertifikat async
        GenerateCertificatePdfJob::dispatch($reg);

        return response()->json(['message'=>'Attendance recorded. Certificate will be generated.']);
    }
}
