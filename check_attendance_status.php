<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Registration;

// Check attendance for event 52
$attendance = Attendance::where('event_id', 52)->with('registration.user')->get();

echo "=== ATTENDANCE STATUS ===\n";
echo "Total Attendance: " . $attendance->count() . "\n\n";

foreach ($attendance as $att) {
    echo "Attendance ID: {$att->id}\n";
    echo "User: {$att->registration->user->name}\n";
    echo "Status: {$att->status}\n";
    echo "Time: {$att->attendance_time}\n";
    echo "Registration ID: {$att->registration_id}\n";
    echo "---\n";
}

// Check certificates
$certificates = Certificate::whereHas('registration', function($q) {
    $q->where('event_id', 52);
})->with('registration.user')->get();

echo "\n=== CERTIFICATES ===\n";
echo "Total Certificates: " . $certificates->count() . "\n\n";

foreach ($certificates as $cert) {
    echo "Certificate ID: {$cert->id}\n";
    echo "User: {$cert->registration->user->name}\n";
    echo "Serial: {$cert->serial_number}\n";
    echo "File: {$cert->file_path}\n";
    echo "Issued: {$cert->issued_at}\n";
    echo "---\n";
}

// Check failed jobs
$failedJobs = DB::table('failed_jobs')->count();
echo "\n=== QUEUE STATUS ===\n";
echo "Failed Jobs: {$failedJobs}\n";
