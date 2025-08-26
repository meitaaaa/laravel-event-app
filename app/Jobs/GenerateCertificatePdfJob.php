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

    /**
     * Create a new job instance.
     */
    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reg = $this->registration->load(['user', 'event']);
        if ($reg->certificate) return;

        $serial = strtoupper(Str::uuid()); // atau format lain
        $pdf = \PDF::loadView('pdf.certificate', [
            'name' => $reg->user->name,
            'event' => $reg->event,
            'serial' => $serial,
            'date' => now()->toDateString()
        ]);
        
        Storage::disk('public')->put($path = "certificates/{$serial}.pdf", $pdf->output());

        Certificate::create([
            'registration_id' => $reg->id,
            'serial_number' => $serial,
            'file_path' => $path,
            'issued_at' => now(),
        ]);
    }
}
