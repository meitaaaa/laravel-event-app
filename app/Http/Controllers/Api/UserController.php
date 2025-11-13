<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Certificate;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getEventHistory(Request $request)
    {
        try {
            $user = $request->user();
            
            \Log::info('getEventHistory called', ['user_id' => $user->id]);
            
            $registrations = Registration::with(['event', 'attendance', 'certificate'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($registration) {
                    $hasCertificate = $registration->certificate !== null;
                    $certificateUrl = null;
                    
                    if ($hasCertificate && $registration->certificate->file_path) {
                        $certificateUrl = url('storage/' . $registration->certificate->file_path);
                    }
                    
                    return [
                        'id' => $registration->id,
                        'event' => [
                            'id' => $registration->event->id,
                            'title' => $registration->event->title,
                            'description' => $registration->event->description,
                            'category' => $registration->event->category,
                            'event_date' => $registration->event->event_date,
                            'location' => $registration->event->location,
                        ],
                        'registration_date' => $registration->created_at->format('Y-m-d H:i:s'),
                        'attendance_status' => $registration->attendance ? $registration->attendance->status : 'not_attended',
                        'attended_at' => $registration->attendance ? $registration->attendance->attendance_time : null,
                        'has_certificate' => $hasCertificate,
                        'certificate' => $hasCertificate ? [
                            'id' => $registration->certificate->id,
                            'serial_number' => $registration->certificate->serial_number,
                            'issued_at' => $registration->certificate->issued_at,
                            'file_path' => $registration->certificate->file_path,
                            'download_url' => $certificateUrl
                        ] : null,
                        'attendance_token' => substr($registration->attendance_token, 0, 3) . '****' . substr($registration->attendance_token, -3),
                    ];
                });

            return response()->json([
                'message' => 'Event history retrieved successfully',
                'data' => $registrations
            ]);
        } catch (\Exception $e) {
            \Log::error('getEventHistory error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to retrieve event history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCertificates(Request $request)
    {
        try {
            $user = $request->user();
            
            $certificates = Certificate::with(['registration.event'])
                ->whereHas('registration', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($certificate) {
                    return [
                        'id' => $certificate->id,
                        'title' => $certificate->title,
                        'event_name' => $certificate->registration->event->title,
                        'participant_name' => $certificate->participant_name,
                        'issued_date' => $certificate->issued_date,
                        'status' => $certificate->status,
                        'category' => $certificate->registration->event->category,
                        'achievement' => $certificate->achievement ?? 'Peserta',
                        'certificate_url' => $certificate->certificate_url,
                    ];
                });

            return response()->json([
                'message' => 'Certificates retrieved successfully',
                'data' => $certificates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve certificates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadCertificate(Request $request, $certificateId)
    {
        try {
            $user = $request->user();
            
            $certificate = Certificate::with('registration.event')
                ->whereHas('registration', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('id', $certificateId)
                ->first();

            if (!$certificate) {
                return response()->json(['message' => 'Certificate not found or not available'], 404);
            }

            if (!$certificate->file_path || !file_exists(storage_path('app/public/' . $certificate->file_path))) {
                return response()->json(['message' => 'Certificate file not found'], 404);
            }

            $filePath = storage_path('app/public/' . $certificate->file_path);
            $fileName = 'Sertifikat_' . str_replace(' ', '_', $certificate->registration->event->title) . '.pdf';

            return response()->download($filePath, $fileName);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to download certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
