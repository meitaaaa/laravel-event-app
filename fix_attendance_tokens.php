<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;

// Find all registrations without attendance_token
$registrations = Registration::whereNull('attendance_token')
    ->orWhere('attendance_token', '')
    ->get();

echo "Found " . $registrations->count() . " registrations without attendance_token\n\n";

foreach ($registrations as $reg) {
    // Use existing token_plain if available, otherwise generate new
    $token = $reg->token_plain ?: str_pad((string)random_int(0,9999999999),10,'0',STR_PAD_LEFT);
    
    $reg->update(['attendance_token' => $token]);
    
    echo "Updated Registration ID {$reg->id}: Token = {$token}\n";
}

echo "\n✅ All attendance tokens updated!\n";
