<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Event;

echo "=== CHECKING ATTENDANCE & CERTIFICATE ISSUE ===\n\n";

$eventId = 39; // Workshop Testing - 12 Sep 2025
$token = '5642773088';

$event = Event::find($eventId);
echo "Event: {$event->title}\n";
echo "Event ID: {$eventId}\n";
echo "Certificate Template: " . ($event->certificate_template_path ?? 'NULL') . "\n\n";

// Find registration by token
$registration = Registration::where('event_id', $eventId)
    ->where('attendance_token', $token)
    ->with(['user', 'attendance', 'certificate'])
    ->first();

if (!$registration) {
    echo "❌ Registration not found for token: {$token}\n";
    exit;
}

echo "=== REGISTRATION DATA ===\n";
echo "Registration ID: {$registration->id}\n";
echo "User: {$registration->user->name} ({$registration->user->email})\n";
echo "Token: {$registration->attendance_token}\n";
echo "Attendance Status: " . ($registration->attendance_status ?? 'NULL') . "\n";
echo "Attended At: " . ($registration->attended_at ?? 'NULL') . "\n\n";

// Check attendance record
echo "=== ATTENDANCE RECORD ===\n";
$attendance = Attendance::where('registration_id', $registration->id)->first();

if ($attendance) {
    echo "✅ Attendance EXISTS\n";
    echo "Attendance ID: {$attendance->id}\n";
    echo "Status: {$attendance->status}\n";
    echo "Attendance Time: {$attendance->attendance_time}\n";
    echo "Token Entered: {$attendance->token_entered}\n";
} else {
    echo "❌ NO ATTENDANCE RECORD FOUND!\n";
    echo "This means attendance was NOT saved to database.\n";
}

echo "\n=== CERTIFICATE RECORD ===\n";
$certificate = Certificate::where('registration_id', $registration->id)->first();

if ($certificate) {
    echo "✅ Certificate EXISTS\n";
    echo "Certificate ID: {$certificate->id}\n";
    echo "Certificate Number: {$certificate->certificate_number}\n";
    echo "PDF Path: " . ($certificate->pdf_path ?? 'NULL') . "\n";
    echo "Generated At: " . ($certificate->generated_at ?? 'NULL') . "\n";
    echo "Status: " . ($certificate->status ?? 'pending') . "\n";
} else {
    echo "❌ NO CERTIFICATE FOUND!\n";
    echo "Certificate was not generated.\n";
}

echo "\n=== DIAGNOSIS ===\n";

if (!$attendance) {
    echo "🔴 MASALAH: Attendance record tidak ada di database!\n";
    echo "   Kemungkinan penyebab:\n";
    echo "   1. Error saat menyimpan attendance\n";
    echo "   2. Transaction rollback\n";
    echo "   3. Database connection issue\n";
} else if (!$certificate) {
    echo "🔴 MASALAH: Certificate tidak di-generate!\n";
    echo "   Kemungkinan penyebab:\n";
    echo "   1. Certificate generation job gagal\n";
    echo "   2. Certificate template tidak ada\n";
    echo "   3. Queue worker tidak berjalan\n";
    
    if (empty($event->certificate_template_path)) {
        echo "   ⚠️  CONFIRMED: Event tidak memiliki certificate template!\n";
    }
}

echo "\n=== SOLUTION ===\n";
if (!$attendance) {
    echo "Anda perlu mengisi absensi lagi karena data tidak tersimpan.\n";
} else if (!$certificate) {
    echo "Attendance sudah tercatat, tapi certificate gagal di-generate.\n";
    echo "Solusi: Trigger manual certificate generation.\n";
}
