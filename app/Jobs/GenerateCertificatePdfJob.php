<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Registration;
use App\Models\Certificate;

class GenerateCertificatePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $registration;
    protected $template;

    /**
     * Create a new job instance.
     */
    public function __construct(Registration $registration, $template = 'random')
    {
        $this->registration = $registration;
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reg = $this->registration->load(['user', 'event']);
        if ($reg->certificate) return;

        // Generate unique serial number
        $serial = 'CERT-' . date('Y') . '-' . strtoupper(Str::random(8));
        
        // Check if event has custom certificate template
        if ($reg->event->certificate_template_path && Storage::disk('public')->exists($reg->event->certificate_template_path)) {
            // Use custom template with overlay
            $this->generateWithCustomTemplate($reg, $serial);
        } else {
            // Use default blade template
            $this->generateWithBladeTemplate($reg, $serial);
        }
    }
    
    /**
     * Generate certificate with custom uploaded template (image overlay)
     */
    protected function generateWithCustomTemplate($reg, $serial)
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
        ]);
        
        // Get template image path
        $templatePath = storage_path('app/public/' . $reg->event->certificate_template_path);
        $templateUrl = 'file://' . $templatePath;
        
        // Generate description text
        $description = "Sebagai apresiasi atas semangat dan dedikasi selama mengikuti kegiatan {$reg->event->title}";
        
        // Create HTML with background image and overlay text
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
                top: 95mm;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 32pt;
                font-weight: bold;
                color: #1e3a8a;
                font-family: "Brush Script MT", cursive;
            }
            .description-text {
                position: absolute;
                top: 125mm;
                left: 50mm;
                right: 50mm;
                text-align: center;
                font-size: 14pt;
                color: #1e40af;
                line-height: 1.6;
            }
        </style>
        <div class="certificate-container">
            <div class="name-text">' . htmlspecialchars($reg->user->name) . '</div>
            <div class="description-text">' . htmlspecialchars($description) . '</div>
        </div>';
        
        $mpdf->WriteHTML($html);
        
        // Ensure certificates directory exists
        if (!Storage::disk('public')->exists('certificates')) {
            Storage::disk('public')->makeDirectory('certificates');
        }
        
        $path = "certificates/{$serial}.pdf";
        Storage::disk('public')->put($path, $mpdf->Output('', 'S'));

        Certificate::create([
            'registration_id' => $reg->id,
            'serial_number' => $serial,
            'file_path' => $path,
            'issued_at' => now(),
        ]);
        
        \Log::info("Certificate generated with custom template for registration {$reg->id}");
    }
    
    /**
     * Generate certificate with default blade template
     */
    protected function generateWithBladeTemplate($reg, $serial)
    {
        try {
            // Use simple template that's compatible with mPDF
            $template = 'certificate_simple';
            
            \Log::info("Starting certificate generation", [
                'registration_id' => $reg->id,
                'template' => $template,
                'serial' => $serial
            ]);
            
            // Generate PDF with selected template using mPDF
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'default_font' => 'dejavusans'
            ]);
            
            $html = view("pdf.{$template}", [
                'name' => $reg->user->name,
                'event' => $reg->event,
                'serial' => $serial,
                'date' => now()->toDateString(),
                'template' => $template
            ])->render();
            
            \Log::info("HTML rendered successfully", [
                'html_length' => strlen($html)
            ]);
            
            $mpdf->WriteHTML($html);
            
            \Log::info("PDF generated successfully");
            
            // Ensure certificates directory exists
            if (!Storage::disk('public')->exists('certificates')) {
                Storage::disk('public')->makeDirectory('certificates');
            }
            
            $path = "certificates/{$serial}.pdf";
            $pdfContent = $mpdf->Output('', 'S');
            Storage::disk('public')->put($path, $pdfContent);

            \Log::info("PDF saved to storage", [
                'path' => $path,
                'size' => strlen($pdfContent)
            ]);

            Certificate::create([
                'registration_id' => $reg->id,
                'serial_number' => $serial,
                'file_path' => $path,
                'issued_at' => now(),
            ]);
            
            \Log::info("Certificate record created in database", [
                'registration_id' => $reg->id,
                'serial' => $serial
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Certificate generation failed", [
                'registration_id' => $reg->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
