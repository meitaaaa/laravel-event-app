<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;

echo "=== FIX ATTENDANCE TOKEN ===\n\n";

$regId = 15; // Registration #15 (Meita)

$registration = Registration::find($regId);

if (!$registration) {
    echo "Registration not found!\n";
    exit;
}

echo "Registration ID: {$registration->id}\n";
echo "User: {$registration->user->name}\n";
echo "token_plain: {$registration->token_plain}\n";
echo "attendance_token BEFORE: " . ($registration->attendance_token ?? 'NULL') . "\n\n";

// Update attendance_token to match token_plain
$registration->update([
    'attendance_token' => $registration->token_plain
]);

echo "✅ UPDATED!\n\n";
echo "attendance_token AFTER: {$registration->attendance_token}\n";

echo "\n=== DONE ===\n";
echo "\nSekarang token 7892749468 bisa digunakan untuk absen!\n";
