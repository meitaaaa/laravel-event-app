<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Attendance;

echo "=== UPDATE EXISTING ATTENDANCE STATUS ===\n\n";

// Find all registrations that have attendance but attendance_status is NULL
$registrationsWithAttendance = Registration::whereHas('attendance')
    ->whereNull('attendance_status')
    ->with('attendance')
    ->get();

echo "Found " . $registrationsWithAttendance->count() . " registrations with attendance but NULL status\n\n";

if ($registrationsWithAttendance->count() === 0) {
    echo "✅ All registrations with attendance have status set!\n";
    exit;
}

foreach ($registrationsWithAttendance as $reg) {
    echo "Registration #{$reg->id}:\n";
    echo "  User ID: {$reg->user_id}\n";
    echo "  Event ID: {$reg->event_id}\n";
    echo "  Attendance Time: {$reg->attendance->attendance_time}\n";
    echo "  OLD attendance_status: " . ($reg->attendance_status ?? 'NULL') . "\n";
    echo "  OLD attended_at: " . ($reg->attended_at ?? 'NULL') . "\n";
    
    // Update registration with attendance info
    $reg->update([
        'attendance_status' => $reg->attendance->status,
        'attended_at' => $reg->attendance->attendance_time
    ]);
    
    echo "  NEW attendance_status: {$reg->attendance_status}\n";
    echo "  NEW attended_at: {$reg->attended_at}\n";
    echo "  ✅ UPDATED!\n";
    echo "---\n";
}

echo "\n✅ All existing attendance statuses have been updated!\n";
