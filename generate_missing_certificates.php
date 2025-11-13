<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Jobs\GenerateCertificatePdfJob;

echo "=== GENERATE MISSING CERTIFICATES ===\n\n";

// Find all registrations with attendance but no certificate
$registrationsWithoutCert = Registration::whereHas('attendance')
    ->whereDoesntHave('certificate')
    ->with(['user', 'event', 'attendance'])
    ->get();

echo "Found " . $registrationsWithoutCert->count() . " registrations with attendance but no certificate\n\n";

if ($registrationsWithoutCert->count() === 0) {
    echo "✅ All attended registrations have certificates!\n";
    exit;
}

foreach ($registrationsWithoutCert as $reg) {
    echo "Registration #{$reg->id}:\n";
    echo "  User: {$reg->user->name} ({$reg->user->email})\n";
    echo "  Event: {$reg->event->title}\n";
    echo "  Attended at: {$reg->attendance->attendance_time}\n";
    echo "  Generating certificate...\n";
    
    try {
        // Dispatch certificate generation job synchronously
        GenerateCertificatePdfJob::dispatchSync($reg, 'random');
        
        // Check if certificate was created
        $cert = Certificate::where('registration_id', $reg->id)->first();
        
        if ($cert) {
            echo "  ✅ Certificate generated successfully!\n";
            echo "     Serial: {$cert->serial_number}\n";
            echo "     Path: {$cert->file_path}\n";
        } else {
            echo "  ❌ Certificate generation failed (no record created)\n";
        }
        
    } catch (\Exception $e) {
        echo "  ❌ ERROR: {$e->getMessage()}\n";
        echo "     Check laravel.log for details\n";
    }
    
    echo "---\n";
}

echo "\n=== CERTIFICATE GENERATION COMPLETE ===\n";
echo "\nSummary:\n";

$totalWithCert = Registration::whereHas('attendance')
    ->whereHas('certificate')
    ->count();
    
$totalWithAttendance = Registration::whereHas('attendance')->count();

echo "Registrations with attendance: {$totalWithAttendance}\n";
echo "Registrations with certificate: {$totalWithCert}\n";

if ($totalWithCert === $totalWithAttendance) {
    echo "\n✅ All attended registrations now have certificates!\n";
} else {
    $missing = $totalWithAttendance - $totalWithCert;
    echo "\n⚠️  Still missing {$missing} certificate(s)\n";
    echo "Check laravel.log for errors\n";
}
