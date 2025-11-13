<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\User;

echo "\n=== TEST API RESPONSE FOR KOMPETISI SAINS ===\n\n";

// Find user "Meitanti Fadilah"
$user = User::where('name', 'like', '%Meitanti Fadilah%')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit;
}

echo "User: {$user->name} (ID: {$user->id})\n\n";

// Get registration for Kompetisi Sains
$registration = Registration::with(['event', 'attendance', 'certificate'])
    ->where('user_id', $user->id)
    ->whereHas('event', function($q) {
        $q->where('title', 'like', '%Kompetisi Sains%');
    })
    ->first();

if (!$registration) {
    echo "❌ Registration not found\n";
    exit;
}

echo "Registration Details:\n";
echo "  ID: {$registration->id}\n";
echo "  Event: {$registration->event->title}\n";
echo "\n";

echo "Certificate from Relationship:\n";
if ($registration->certificate) {
    echo "  ID: {$registration->certificate->id}\n";
    echo "  Serial: {$registration->certificate->serial_number}\n";
    echo "  File: {$registration->certificate->file_path}\n";
} else {
    echo "  ❌ No certificate found\n";
}

echo "\n";

// Check what API would return
$hasCertificate = $registration->certificate !== null;
$certificateUrl = null;

if ($hasCertificate && $registration->certificate->file_path) {
    $certificateUrl = url('storage/' . $registration->certificate->file_path);
}

echo "API Response would be:\n";
echo json_encode([
    'certificate' => $hasCertificate ? [
        'id' => $registration->certificate->id,
        'serial_number' => $registration->certificate->serial_number,
        'issued_at' => $registration->certificate->issued_at,
        'file_path' => $registration->certificate->file_path,
        'download_url' => $certificateUrl
    ] : null
], JSON_PRETTY_PRINT);

echo "\n\n";
