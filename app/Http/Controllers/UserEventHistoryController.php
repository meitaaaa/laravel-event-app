<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Registration;
use App\Models\Event;
use App\Models\Attendance;
use App\Models\Certificate;

class UserEventHistoryController extends Controller
{
    /**
     * Get user's event history with complete details
     */
    public function getEventHistory(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get all registrations for this user with related data
            $registrations = Registration::with([
                'event:id,title,description,event_date,start_time,end_time,location,price,is_free',
                'attendance:id,registration_id,status,attendance_time,token_entered',
                'certificate:id,registration_id,serial_number,file_path,issued_at'
            ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

            // Format the response
            $eventHistory = $registrations->map(function ($registration) {
                $event = $registration->event;
                $attendance = $registration->attendance;
                $certificate = $registration->certificate;

                return [
                    'registration_id' => $registration->id,
                    'registration_date' => $registration->created_at->format('Y-m-d H:i:s'),
                    'registration_status' => $registration->status,
                    'token_sent_at' => $registration->token_sent_at ? $registration->token_sent_at->format('Y-m-d H:i:s') : null,
                    'registration' => [
                        'id' => $registration->id,
                        'status' => $registration->status,
                        'token_plain' => $registration->token_plain,
                        'token_sent_at' => $registration->token_sent_at ? $registration->token_sent_at->format('Y-m-d H:i:s') : null,
                    ],
                    
                    // Event details
                    'event' => [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'event_date' => $event->event_date,
                        'start_time' => $event->start_time,
                        'end_time' => $event->end_time,
                        'location' => $event->location,
                        'price' => $event->price,
                        'is_free' => $event->is_free,
                        'formatted_date' => \Carbon\Carbon::parse($event->event_date)->format('d M Y'),
                        'formatted_time' => \Carbon\Carbon::parse($event->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($event->end_time)->format('H:i'),
                    ],
                    
                    // Attendance status
                    'attendance' => $attendance ? [
                        'status' => $attendance->status,
                        'attendance_time' => $attendance->attendance_time->format('Y-m-d H:i:s'),
                        'formatted_attendance_time' => $attendance->attendance_time->format('d M Y, H:i'),
                        'token_used' => substr($attendance->token_entered, 0, 3) . '****' . substr($attendance->token_entered, -3), // Mask token for security
                        'is_present' => $attendance->status === 'present'
                    ] : [
                        'status' => 'not_attended',
                        'attendance_time' => null,
                        'formatted_attendance_time' => null,
                        'token_used' => null,
                        'is_present' => false
                    ],
                    
                    // Certificate status
                    'certificate' => $certificate ? [
                        'id' => $certificate->id,
                        'available' => true,
                        'serial_number' => $certificate->serial_number,
                        'issued_at' => $certificate->issued_at->format('Y-m-d H:i:s'),
                        'formatted_issued_at' => $certificate->issued_at->format('d M Y'),
                        'download_url' => url('api/certificates/' . $certificate->id . '/download')
                    ] : [
                        'id' => null,
                        'available' => false,
                        'serial_number' => null,
                        'issued_at' => null,
                        'formatted_issued_at' => null,
                        'download_url' => null
                    ],
                    
                    // Overall status for easy filtering
                    'overall_status' => $this->determineOverallStatus($registration, $attendance, $certificate)
                ];
            });

            // Statistics
            $stats = [
                'total_events' => $registrations->count(),
                'attended_events' => $registrations->filter(function($reg) {
                    return $reg->attendance && $reg->attendance->status === 'present';
                })->count(),
                'certificates_earned' => $registrations->filter(function($reg) {
                    return $reg->certificate !== null;
                })->count(),
                'upcoming_events' => $registrations->filter(function($reg) {
                    return \Carbon\Carbon::parse($reg->event->event_date)->isFuture();
                })->count()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'events' => $eventHistory,
                    'statistics' => $stats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch event history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed information for a specific event registration
     */
    public function getEventDetail($registrationId)
    {
        try {
            $user = Auth::user();
            
            $registration = Registration::with([
                'event',
                'attendance',
                'certificate',
                'user:id,name,email'
            ])
            ->where('id', $registrationId)
            ->where('user_id', $user->id)
            ->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration not found'
                ], 404);
            }

            $event = $registration->event;
            $attendance = $registration->attendance;
            $certificate = $registration->certificate;

            $detail = [
                'registration' => [
                    'id' => $registration->id,
                    'status' => $registration->status,
                    'registered_at' => $registration->created_at->format('Y-m-d H:i:s'),
                    'token_sent_at' => $registration->token_sent_at ? $registration->token_sent_at->format('Y-m-d H:i:s') : null,
                    'token_plain' => $registration->token_plain,
                    'token_reference' => 'Token sent to: ' . $registration->user->email
                ],
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_date' => $event->event_date,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'location' => $event->location,
                    'price' => $event->price,
                    'is_free' => $event->is_free
                ],
                'attendance' => $attendance ? [
                    'attended' => true,
                    'status' => $attendance->status,
                    'attendance_time' => $attendance->attendance_time->format('Y-m-d H:i:s'),
                    'token_used' => $attendance->token_entered
                ] : [
                    'attended' => false,
                    'status' => 'not_attended',
                    'attendance_time' => null,
                    'token_used' => null
                ],
                'certificate' => $certificate ? [
                    'available' => true,
                    'serial_number' => $certificate->serial_number,
                    'issued_at' => $certificate->issued_at->format('Y-m-d H:i:s'),
                    'file_path' => $certificate->file_path,
                    'download_url' => url('api/certificates/download/' . $certificate->id)
                ] : [
                    'available' => false
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $detail
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch event detail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine overall status for an event registration
     * Priority: completed > attended > upcoming > missed
     */
    private function determineOverallStatus($registration, $attendance, $certificate)
    {
        // Highest priority: Has certificate (completed)
        if ($certificate) {
            return 'completed'; // Event completed with certificate
        }
        
        // Second priority: Has attended but no certificate yet
        if ($attendance && $attendance->status === 'present') {
            return 'attended'; // Attended but certificate pending
        }
        
        // Third priority: Check if event is upcoming or missed
        if ($registration->status === 'registered') {
            $eventDate = \Carbon\Carbon::parse($registration->event->event_date);
            if ($eventDate->isFuture()) {
                return 'upcoming'; // Event hasn't happened yet
            } else {
                return 'missed'; // Event passed but didn't attend
            }
        }
        
        return 'cancelled';
    }
}
