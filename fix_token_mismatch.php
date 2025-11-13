<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;

echo "=== FIXING TOKEN MISMATCH ISSUE ===\n\n";

// Get all registrations where attendance_token doesn't match token_plain
$registrations = Registration::whereColumn('attendance_token', '!=', 'token_plain')
    ->orWhereNull('attendance_token')
    ->get();

echo "Found " . $registrations->count() . " registrations with mismatched tokens\n\n";

if ($registrations->count() === 0) {
    echo "✅ No token mismatches found!\n";
    echo "Checking all registrations...\n\n";
    
    $all = Registration::all();
    foreach ($all as $reg) {
        echo "Registration #{$reg->id}:\n";
        echo "  Event: {$reg->event->title}\n";
        echo "  User: {$reg->user->email}\n";
        echo "  token_plain: '{$reg->token_plain}'\n";
        echo "  attendance_token: '{$reg->attendance_token}'\n";
        echo "  Match: " . ($reg->token_plain === $reg->attendance_token ? '✅ YES' : '❌ NO') . "\n";
        echo "---\n";
    }
    exit;
}

foreach ($registrations as $reg) {
    echo "Registration #{$reg->id}:\n";
    echo "  Event: {$reg->event->title}\n";
    echo "  User: {$reg->user->email}\n";
    echo "  OLD attendance_token: '{$reg->attendance_token}'\n";
    echo "  token_plain: '{$reg->token_plain}'\n";
    
    // Fix: Set attendance_token to match token_plain
    $reg->attendance_token = $reg->token_plain;
    $reg->save();
    
    echo "  NEW attendance_token: '{$reg->attendance_token}'\n";
    echo "  ✅ FIXED!\n";
    echo "---\n";
}

echo "\n✅ All tokens have been synchronized!\n";
echo "\nNOTE: Users should use the token from their email (token_plain).\n";
