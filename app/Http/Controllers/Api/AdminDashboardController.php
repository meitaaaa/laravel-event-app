<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Attendance;
use App\Models\Registration;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $year = $request->get('year', now()->year);
        
        // Log for debugging
        \Log::info('Admin Dashboard called', [
            'year' => $year,
            'user' => auth()->user() ? auth()->user()->id : 'no auth'
        ]);
        
        $statistics = $this->getStatistics();
        \Log::info('Statistics calculated', $statistics);
        
        return response()->json([
            'monthly_events' => $this->getMonthlyEvents($year),
            'monthly_attendees' => $this->getMonthlyAttendees($year),
            'top_events' => $this->getTopEvents(),
            'statistics' => $statistics
        ]);
    }

    private function getMonthlyEvents($year)
    {
        $monthlyData = Event::selectRaw('MONTH(event_date) as month, COUNT(*) as count')
            ->whereYear('event_date', $year)
            ->groupBy('month')
            ->pluck('count', 'month');

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = [
                'month' => $i,
                'month_name' => Carbon::create()->month($i)->format('F'),
                'count' => (int)($monthlyData[$i] ?? 0)
            ];
        }

        return $result;
    }

    private function getMonthlyAttendees($year)
    {
        $monthlyData = Attendance::selectRaw('MONTH(attendance_time) as month, COUNT(*) as count')
            ->whereYear('attendance_time', $year)
            ->where('status', 'present')
            ->groupBy('month')
            ->pluck('count', 'month');

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = [
                'month' => $i,
                'month_name' => Carbon::create()->month($i)->format('F'),
                'count' => (int)($monthlyData[$i] ?? 0)
            ];
        }

        return $result;
    }

    private function getTopEvents()
    {
        return Event::select('events.id', 'events.title', 'events.event_date')
            ->selectRaw('COUNT(registrations.id) as participants_count')
            ->leftJoin('registrations', 'events.id', '=', 'registrations.event_id')
            ->groupBy('events.id', 'events.title', 'events.event_date')
            ->orderByDesc('participants_count')
            ->limit(10)
            ->get();
    }

    private function getStatistics()
    {
        // Get all events (published and unpublished for admin view)
        $totalEvents = Event::count();
        $totalRegistrations = Registration::count();
        
        // Check if Attendance model exists, if not use registrations as attendees
        try {
            $totalAttendees = Attendance::where('status', 'present')->count();
        } catch (\Exception $e) {
            // If no attendance table, assume all registered users attended
            $totalAttendees = $totalRegistrations;
        }
        
        $upcomingEvents = Event::where('event_date', '>=', now()->toDateString())
            ->count();

        // Calculate revenue
        $totalRevenue = Payment::where('status', 'paid')->sum('amount');
        
        // Revenue admin: 10% dari total revenue
        $adminRevenue = $totalRevenue * 0.10;
        
        // Revenue panitia: 90% dari total revenue
        $panitiaRevenue = $totalRevenue * 0.90;

        return [
            'total_events' => $totalEvents,
            'total_registrations' => $totalRegistrations,
            'total_attendees' => $totalAttendees,
            'upcoming_events' => $upcomingEvents,
            'attendance_rate' => $totalRegistrations > 0 ? round(($totalAttendees / $totalRegistrations) * 100, 2) : 0,
            'total_revenue' => $totalRevenue,
            'admin_revenue' => $adminRevenue,
            'panitia_revenue' => $panitiaRevenue
        ];
    }

    public function exportData(Request $request)
    {
        $type = $request->get('type', 'events'); // events, registrations, attendances
        $format = $request->get('format', 'csv'); // csv, xlsx
        
        switch ($type) {
            case 'events':
                return $this->exportEvents($format);
            case 'registrations':
                return $this->exportRegistrations($format);
            case 'attendances':
                return $this->exportAttendances($format);
            default:
                return response()->json(['error' => 'Invalid export type'], 400);
        }
    }

    private function exportEvents($format)
    {
        // Use leftJoin to avoid dependency on Eloquent relationship definitions
        $events = Event::leftJoin('users', 'events.created_by', '=', 'users.id')
            ->select(
                'events.id',
                'events.title',
                'events.event_date',
                'events.start_time',
                'events.end_time',
                'events.location',
                'events.category',
                'events.created_by',
                'events.created_at',
                DB::raw('COALESCE(users.name, "N/A") as creator_name')
            )
            ->get();

        $data = $events->map(function ($event) {
            return [
                'ID' => $event->id,
                'Judul Event' => $event->title,
                'Tanggal Event' => Carbon::parse($event->event_date)->format('d/m/Y'),
                'Waktu Mulai' => $event->start_time ? Carbon::parse($event->start_time)->format('H:i') : '-',
                'Waktu Selesai' => $event->end_time ? Carbon::parse($event->end_time)->format('H:i') : '-',
                'Lokasi' => $event->location ?? '-',
                'Kategori' => ucfirst($event->category ?? '-'),
                'Dibuat Oleh' => $event->creator_name,
                'Tanggal Dibuat' => $event->created_at->format('d/m/Y H:i')
            ];
        });

        return $this->generateExport($data, 'events', $format);
    }

    private function exportRegistrations($format)
    {
        $registrations = Registration::with(['user:id,name,email', 'event:id,title'])
            ->select('id', 'user_id', 'event_id', 'status', 'created_at')
            ->get();

        $data = $registrations->map(function ($registration) {
            return [
                'ID' => $registration->id,
                'Nama Peserta' => $registration->user->name ?? '-',
                'Email' => $registration->user->email ?? '-',
                'Event' => $registration->event->title ?? '-',
                'Status' => ucfirst($registration->status ?? 'pending'),
                'Tanggal Daftar' => $registration->created_at->format('d/m/Y H:i')
            ];
        });

        return $this->generateExport($data, 'registrations', $format);
    }

    private function exportAttendances($format)
    {
        $attendances = Attendance::with(['registration.user:id,name,email', 'registration.event:id,title'])
            ->select('id', 'registration_id', 'status', 'attendance_time')
            ->get();

        $data = $attendances->map(function ($attendance) {
            return [
                'ID' => $attendance->id,
                'Nama Peserta' => $attendance->registration->user->name ?? '-',
                'Email' => $attendance->registration->user->email ?? '-',
                'Event' => $attendance->registration->event->title ?? '-',
                'Status Kehadiran' => $attendance->status === 'present' ? 'Hadir' : 'Tidak Hadir',
                'Waktu Kehadiran' => $attendance->attendance_time ? $attendance->attendance_time->format('d/m/Y H:i') : '-'
            ];
        });

        return $this->generateExport($data, 'attendances', $format);
    }

    private function generateExport($data, $type, $format)
    {
        $filename = "{$type}_" . now()->format('Y-m-d') . ".{$format}";
        
        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for proper Excel encoding
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                if ($data->isNotEmpty()) {
                    // Write column headers (bold in Excel when opened)
                    $headers = array_keys($data->first());
                    fputcsv($file, $headers, ',', '"');
                    
                    // Write data rows
                    foreach ($data as $row) {
                        fputcsv($file, array_values($row), ',', '"');
                    }
                } else {
                    // If no data, write headers only
                    fputcsv($file, ['Tidak ada data'], ',', '"');
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // For now, default to CSV if xlsx is requested
        return $this->generateExport($data, $type, 'csv');
    }
}
