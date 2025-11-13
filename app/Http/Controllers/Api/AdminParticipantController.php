<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Event;
use Carbon\Carbon;

class AdminParticipantController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get all registrations with related data
            $query = Registration::with(['user', 'event', 'attendance']);
            
            // Search functionality
            if ($search = $request->get('q')) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                })->orWhereHas('event', function($q) use ($search) {
                    $q->where('title', 'like', "%$search%");
                });
            }
            
            // Status filter
            if ($status = $request->get('status')) {
                switch ($status) {
                    case 'confirmed':
                        $query->where('status', 'confirmed');
                        break;
                    case 'pending':
                        $query->where('status', 'pending');
                        break;
                    case 'cancelled':
                        $query->where('status', 'cancelled');
                        break;
                    case 'attended':
                        $query->whereHas('attendance', function($q) {
                            $q->where('status', 'present');
                        });
                        break;
                }
            }
            
            $registrations = $query->orderBy('created_at', 'desc')->paginate(15);
            
            // Transform data for frontend
            $participants = $registrations->getCollection()->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'user_name' => $registration->user->name,
                    'user_email' => $registration->user->email,
                    'event_title' => $registration->event->title,
                    'event_date' => $registration->event->event_date,
                    'registration_date' => $registration->created_at,
                    'status' => $registration->status,
                    'attendance_status' => $registration->attendance ? $registration->attendance->status : null,
                    'attendance_time' => $registration->attendance ? $registration->attendance->created_at : null,
                ];
            });
            
            $registrations->setCollection($participants);
            
            return response()->json([
                'data' => $registrations->items(),
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
                'total' => $registrations->total()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Admin participants error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch participants'], 500);
        }
    }
    
    public function statistics()
    {
        try {
            $totalParticipants = Registration::count();
            $confirmedParticipants = Registration::where('status', 'confirmed')->count();
            $pendingParticipants = Registration::where('status', 'pending')->count();
            $attendedParticipants = Attendance::where('status', 'present')->count();
            
            return response()->json([
                'total_participants' => $totalParticipants,
                'confirmed' => $confirmedParticipants,
                'pending' => $pendingParticipants,
                'attended' => $attendedParticipants,
                'cancelled' => Registration::where('status', 'cancelled')->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Admin participants statistics error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }
}
