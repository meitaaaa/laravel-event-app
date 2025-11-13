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
        $certificates = Certificate::with('registration.event')
            ->whereHas('registration', fn($q) => $q->where('user_id', $r->user()->id))
            ->orderByDesc('issued_at')
            ->get();
        
        // Add download URL to each certificate
        $certificates = $certificates->map(function($cert) {
            $cert->download_url = url("/api/certificates/{$cert->id}/download");
            return $cert;
        });
        
        return response()->json($certificates);
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
        // Ensure certificate file exists and is a PDF; if not, (re)generate synchronously
        $path = $certificate->file_path;
        if (!$path || !\Illuminate\Support\Str::endsWith(strtolower($path), '.pdf')) {
            $path = 'certificates/'.$certificate->serial_number.'.pdf';
        }

        $fullPath = storage_path('app/public/'.$path);

        if (!file_exists($fullPath)) {
            $registration = $certificate->registration()->with(['user','event'])->first();
            
            // Check if event has custom template
            if ($registration->event->certificate_template_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($registration->event->certificate_template_path)) {
                // Use custom template
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4-L',
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                ]);
                
                $templatePath = storage_path('app/public/' . $registration->event->certificate_template_path);
                $templateUrl = 'file://' . $templatePath;
                $description = "Sebagai apresiasi atas semangat dan dedikasi selama mengikuti kegiatan {$registration->event->title}";
                
                $html = '
                <style>
                    body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
                    .certificate-container {
                        position: relative;
                        width: 297mm;
                        height: 210mm;
                        background-image: url(\'' . $templateUrl . '\');
                        background-size: cover;
                        background-position: center;
                        background-repeat: no-repeat;
                    }
                    .name-text {
                        position: absolute;
                        top: 112mm;
                        left: 0;
                        right: 0;
                        text-align: center;
                        font-size: 42pt;
                        font-weight: bold;
                        color: #1e3a8a;
                        font-family: "Brush Script MT", "Lucida Handwriting", cursive;
                        font-style: italic;
                    }
                    .description-text {
                        position: absolute;
                        top: 137mm;
                        left: 40mm;
                        right: 40mm;
                        text-align: center;
                        font-size: 11pt;
                        color: #1e40af;
                        line-height: 1.4;
                    }
                </style>
                <div class="certificate-container">
                    <div class="name-text">' . htmlspecialchars($registration->user->name) . '</div>
                    <div class="description-text">' . htmlspecialchars($description) . '</div>
                </div>';
                
                $mpdf->WriteHTML($html);
                $pdfContent = $mpdf->Output('', 'S');
            } else {
                // Use default blade template
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4-L',
                    'margin_left' => 15,
                    'margin_right' => 15,
                    'margin_top' => 15,
                    'margin_bottom' => 15,
                ]);
                
                $html = view('pdf.certificate_modern', [
                    'name' => $registration->user->name,
                    'event' => $registration->event,
                    'serial' => $certificate->serial_number,
                    'date' => optional($certificate->issued_at)->toDateString() ?? now()->toDateString(),
                ])->render();
                
                $mpdf->WriteHTML($html);
                $pdfContent = $mpdf->Output('', 'S');
            }

            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $pdfContent);

            // persist corrected path if changed
            if ($certificate->file_path !== $path) {
                $certificate->update(['file_path' => $path]);
            }
        }

        return response()->download($fullPath, 'Sertifikat_'.$certificate->serial_number.'.pdf');
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
            return response()->json([
                'message' => 'Certificate already exists',
                'certificate' => $registration->certificate,
                'download_url' => url("/api/certificates/{$registration->certificate->id}/download")
            ], 400);
        }

        // Check if user attended the event
        if (!$registration->attendance || $registration->attendance->status !== 'present') {
            return response()->json(['message' => 'User must attend the event first'], 400);
        }

        // Get template preference from request
        $template = $r->input('template', 'random');
        $validTemplates = ['modern', 'classic', 'default', 'random'];
        
        if (!in_array($template, $validTemplates)) {
            $template = 'random';
        }

        // Dispatch job to generate certificate with template
        GenerateCertificatePdfJob::dispatch($registration, $template);

        return response()->json([
            'message' => 'Certificate generation started',
            'status' => 'processing',
            'template' => $template,
            'estimated_time' => '30-60 seconds'
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
