<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;

echo "=== FIXING INVALID TOKEN FORMATS ===\n\n";

// Find registrations with invalid token format (not 10 digits)
$invalidRegistrations = Registration::all()->filter(function($reg) {
    return !preg_match('/^\d{10}$/', $reg->attendance_token ?? '') || 
           !preg_match('/^\d{10}$/', $reg->token_plain ?? '');
});

echo "Found " . $invalidRegistrations->count() . " registrations with invalid token format\n\n";

if ($invalidRegistrations->count() === 0) {
    echo "✅ All tokens have valid format!\n";
    exit;
}

foreach ($invalidRegistrations as $reg) {
    echo "Registration #{$reg->id}:\n";
    echo "  User: {$reg->user->email}\n";
    echo "  Event: {$reg->event->title}\n";
    echo "  OLD token_plain: '{$reg->token_plain}'\n";
    echo "  OLD attendance_token: '{$reg->attendance_token}'\n";
    
    // Generate new 10-digit token
    $newToken = str_pad((string)random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
    
    // Update both token_plain and attendance_token
    $reg->token_plain = $newToken;
    $reg->attendance_token = $newToken;
    $reg->save();
    
    echo "  NEW token: '{$newToken}'\n";
    echo "  ✅ FIXED!\n";
    echo "  ⚠️  NOTE: User needs to be notified of new token via email!\n";
    echo "---\n";
}

echo "\n✅ All invalid token formats have been fixed!\n";
echo "\n⚠️  IMPORTANT: Users with updated tokens should receive new email notifications.\n";
echo "Consider running a script to resend tokens to affected users.\n";
