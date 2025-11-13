<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Attendance;
use App\Models\Certificate;

echo "=== FINAL VERIFICATION ===\n\n";

// Check Registration #9
$reg = Registration::with(['event', 'attendance', 'certificate'])->find(9);

if (!$reg) {
    echo "❌ Registration #9 not found!\n";
    exit;
}

echo "REGISTRATION #9 STATUS:\n";
echo "========================\n";
echo "User: {$reg->user->name} ({$reg->user->email})\n";
echo "Event: {$reg->event->title}\n";
echo "Event Date: {$reg->event->event_date}\n\n";

echo "ATTENDANCE:\n";
if ($reg->attendance) {
    echo "  ✅ Status: {$reg->attendance->status}\n";
    echo "  ✅ Time: {$reg->attendance->attendance_time}\n";
    echo "  ✅ Token: {$reg->attendance->token_entered}\n";
} else {
    echo "  ❌ No attendance record\n";
}

echo "\nREGISTRATION STATUS:\n";
echo "  attendance_status: " . ($reg->attendance_status ?? 'NULL') . "\n";
echo "  attended_at: " . ($reg->attended_at ?? 'NULL') . "\n";

echo "\nCERTIFICATE:\n";
if ($reg->certificate) {
    echo "  ✅ ID: {$reg->certificate->id}\n";
    echo "  ✅ Serial: {$reg->certificate->serial_number}\n";
    echo "  ✅ File: {$reg->certificate->file_path}\n";
    echo "  ✅ Issued: {$reg->certificate->issued_at}\n";
    
    $filePath = storage_path('app/public/' . $reg->certificate->file_path);
    if (file_exists($filePath)) {
        $fileSize = filesize($filePath);
        echo "  ✅ File exists: " . number_format($fileSize) . " bytes\n";
    } else {
        echo "  ❌ File not found!\n";
    }
} else {
    echo "  ❌ No certificate\n";
}

echo "\n=== OVERALL STATUS ===\n";

$checks = [
    'Attendance recorded' => $reg->attendance !== null,
    'Attendance status set' => $reg->attendance_status !== null,
    'Certificate generated' => $reg->certificate !== null,
    'Certificate file exists' => $reg->certificate && file_exists(storage_path('app/public/' . $reg->certificate->file_path)),
];

$allPassed = true;
foreach ($checks as $check => $passed) {
    echo ($passed ? "✅" : "❌") . " {$check}\n";
    if (!$passed) $allPassed = false;
}

echo "\n";
if ($allPassed) {
    echo "🎉 ALL CHECKS PASSED! User should see correct status in frontend.\n";
} else {
    echo "⚠️  Some checks failed. Please review.\n";
}

echo "\n=== API RESPONSE PREVIEW ===\n";
echo json_encode([
    'id' => $reg->id,
    'event' => [
        'title' => $reg->event->title,
        'event_date' => $reg->event->event_date,
    ],
    'attendance_status' => $reg->attendance ? $reg->attendance->status : 'not_attended',
    'attended_at' => $reg->attendance ? $reg->attendance->attendance_time : null,
    'has_certificate' => $reg->certificate !== null,
    'certificate' => $reg->certificate ? [
        'serial_number' => $reg->certificate->serial_number,
        'download_url' => url('storage/' . $reg->certificate->file_path)
    ] : null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
