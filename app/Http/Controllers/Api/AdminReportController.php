<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Attendance;
use App\Models\Registration;

class AdminReportController extends Controller
{
    public function monthlyEvents(Request $r){
        $year = (int)($r->get('year', now()->year));
        $rows = Event::selectRaw('MONTH(event_date) m, COUNT(*) c')
            ->whereYear('event_date',$year)
            ->groupBy('m')
            ->pluck('c','m');

        return collect(range(1,12))
            ->map(fn($m)=>['month'=>$m,'count'=>(int)($rows[$m]??0)]);
    }

    public function monthlyAttendees(Request $r){
        $year = (int)($r->get('year', now()->year));
        $rows = Attendance::selectRaw('MONTH(attendance_time) m, COUNT(*) c')
            ->whereYear('attendance_time',$year)
            ->where('status','present')
            ->groupBy('m')
            ->pluck('c','m');

        return collect(range(1,12))
            ->map(fn($m)=>['month'=>$m,'count'=>(int)($rows[$m]??0)]);
    }

    public function top10Events(){
        return Registration::select('event_id')
            ->selectRaw('COUNT(*) as participants')
            ->groupBy('event_id')
            ->orderByDesc('participants')
            ->with('event')
            ->limit(10)
            ->get();
    }

    public function exportParticipants(Event $event, Request $r){
        $fmt = $r->get('format','csv'); // csv|xlsx
        $export = new \App\Exports\EventParticipantsExport($event->id);
        return $export->export($fmt);
    }

    public function exportAllParticipants(Request $r){
        $fmt = $r->get('format','csv'); // csv|xlsx
        $export = new \App\Exports\EventParticipantsExport();
        return $export->export($fmt);
    }
}
