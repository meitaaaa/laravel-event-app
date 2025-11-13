<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Certificate;
use App\Jobs\GenerateCertificatePdfJob;

echo "=== GENERATE CERTIFICATES FOR ALL ELIGIBLE USERS ===\n\n";

// Find all registrations with attendance but no certificate
$eligibleRegistrations = Registration::whereHas('attendance', function($q) {
    $q->where('status', 'present');
})->whereDoesntHave('certificate')
  ->with(['user', 'event', 'attendance'])
  ->get();

echo "Found " . $eligibleRegistrations->count() . " eligible registrations\n\n";

if ($eligibleRegistrations->count() === 0) {
    echo "✅ All eligible users already have certificates!\n";
    exit;
}

$generated = 0;
$failed = 0;

foreach ($eligibleRegistrations as $registration) {
    echo "Processing Registration #{$registration->id}\n";
    echo "  User: {$registration->user->name}\n";
    echo "  Event: {$registration->event->title}\n";
    echo "  Attended: {$registration->attendance->attended_at}\n";
    
    try {
        // Generate certificate synchronously
        $job = new GenerateCertificatePdfJob($registration, 'random');
        $job->handle();
        
        $cert = $registration->fresh()->certificate;
        if ($cert) {
            echo "  ✅ Certificate generated: {$cert->serial_number}\n";
            $generated++;
        } else {
            echo "  ⚠️ Certificate record not created\n";
            $failed++;
        }
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "Total Eligible: " . $eligibleRegistrations->count() . "\n";
echo "Generated: $generated\n";
echo "Failed: $failed\n";
echo "\n";

// Show all certificates now
$totalCerts = Certificate::count();
echo "Total Certificates in Database: $totalCerts\n";

if ($totalCerts > 0) {
    echo "\n=== ALL CERTIFICATES ===\n";
    $certificates = Certificate::with(['registration.user', 'registration.event'])->get();
    
    foreach ($certificates as $cert) {
        echo "- {$cert->serial_number}: {$cert->registration->user->name} - {$cert->registration->event->title}\n";
    }
}

echo "\n✅ DONE!\n";
