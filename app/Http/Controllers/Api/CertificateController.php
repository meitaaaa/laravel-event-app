<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Models\Registration;
use App\Jobs\GenerateCertificatePdfJob;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function myCertificates(Request $r){
        return Certificate::with('registration.event')
            ->whereHas('registration', fn($q) => $q->where('user_id', $r->user()->id))
            ->orderByDesc('issued_at')
            ->get();
    }

    // publik
    public function search(Request $r){
        $r->validate(['q' => 'required|string|max:100']);
        $q = $r->q;

        try {
            $certificates = Certificate::with(['registration.user','registration.event'])
                ->where('serial_number','like',"%$q%")
                ->orWhereHas('registration.user', fn($w) =>
                    $w->where('name','like',"%$q%")
                      ->orWhere('email','like',"%$q%")
                )
                ->orWhereHas('registration.event', fn($w) =>
                    $w->where('title','like',"%$q%")
                )
                ->limit(50)
                ->get();

            return response()->json([
                'message' => 'Search completed',
                'data' => $certificates,
                'count' => $certificates->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Search failed',
                'error' => $e->getMessage(),
                'debug' => [
                    'query' => $q,
                    'certificates_table_exists' => \Schema::hasTable('certificates')
                ]
            ], 500);
        }
    }

    public function download(Certificate $certificate){
        // bisa kasih link sementara (signed url) atau langsung response download
        return response()->download(storage_path('app/public/'.$certificate->file_path));
    }

    /**
     * Generate certificate for a registration
     */
    public function generate(Request $r, Registration $registration)
    {
        // Check if user is authorized to generate certificate for this registration
        if ($r->user()->id !== $registration->user_id && !$r->user()->can('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if certificate already exists
        if ($registration->certificate) {
            return response()->json(['message' => 'Certificate already exists'], 400);
        }

        // Check if user attended the event
        if (!$registration->attendance || $registration->attendance->status !== 'present') {
            return response()->json(['message' => 'User must attend the event first'], 400);
        }

        // Dispatch job to generate certificate
        GenerateCertificatePdfJob::dispatch($registration);

        return response()->json([
            'message' => 'Certificate generation started',
            'status' => 'processing'
        ]);
    }

    /**
     * Check certificate generation status
     */
    public function status(Registration $registration)
    {
        $certificate = $registration->certificate;
        
        if (!$certificate) {
            return response()->json([
                'status' => 'not_generated',
                'message' => 'Certificate not yet generated'
            ]);
        }

        return response()->json([
            'status' => 'generated',
            'certificate' => $certificate,
            'download_url' => url("/api/certificates/{$certificate->id}/download")
        ]);
    }
}
