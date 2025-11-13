<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Certificate;
use App\Models\Registration;

echo "\n=== CHECK CERTIFICATE FOR KOMPETISI SAINS ===\n\n";

// Find registration for "Kompetisi Sains" event
$registration = Registration::whereHas('event', function($q) {
    $q->where('title', 'like', '%Kompetisi Sains%');
})->with(['user', 'event', 'certificate'])->first();

if (!$registration) {
    echo "❌ Registration not found\n";
    exit;
}

echo "Registration Details:\n";
echo "  ID: {$registration->id}\n";
echo "  User: {$registration->user->name}\n";
echo "  Event: {$registration->event->title}\n";
echo "\n";

// Get certificate
$cert = $registration->certificate;

if (!$cert) {
    echo "❌ No certificate found for this registration\n";
    exit;
}

echo "Certificate Details:\n";
echo "  ID: {$cert->id}\n";
echo "  Serial: {$cert->serial_number}\n";
echo "  File: {$cert->file_path}\n";
echo "  Registration ID: {$cert->registration_id}\n";
echo "\n";

// Check if file exists
if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cert->file_path)) {
    $size = \Illuminate\Support\Facades\Storage::disk('public')->size($cert->file_path);
    echo "✅ File exists! Size: " . number_format($size) . " bytes\n";
} else {
    echo "❌ File NOT found in storage!\n";
}

echo "\nDownload URL:\n";
echo "http://127.0.0.1:8000/api/certificates/{$cert->id}/download\n";
echo "\nDirect URL:\n";
echo "http://127.0.0.1:8000/storage/{$cert->file_path}\n";

echo "\n";
