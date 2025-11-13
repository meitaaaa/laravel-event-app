<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Attendance;

echo "=== FIX REGISTRATION #9 STATUS ===\n\n";

$reg = Registration::with('attendance')->find(9);

if (!$reg) {
    echo "Registration #9 not found!\n";
    exit;
}

echo "Registration #9:\n";
echo "  User ID: {$reg->user_id}\n";
echo "  Event ID: {$reg->event_id}\n";
echo "  Has attendance: " . ($reg->attendance ? 'YES' : 'NO') . "\n";

if ($reg->attendance) {
    echo "  Attendance status: {$reg->attendance->status}\n";
    echo "  Attendance time: {$reg->attendance->attendance_time}\n";
    
    echo "\nUpdating registration...\n";
    
    $reg->attendance_status = $reg->attendance->status;
    $reg->attended_at = $reg->attendance->attendance_time;
    $result = $reg->save();
    
    echo "Save result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
    // Reload from database
    $reg->refresh();
    
    echo "\nAfter update:\n";
    echo "  attendance_status: " . ($reg->attendance_status ?? 'NULL') . "\n";
    echo "  attended_at: " . ($reg->attended_at ?? 'NULL') . "\n";
} else {
    echo "  No attendance record found!\n";
}
