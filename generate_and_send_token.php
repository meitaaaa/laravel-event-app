<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find registration
$reg = \App\Models\Registration::where('user_id', 8)
    ->where('event_id', 54)
    ->first();

if (!$reg) {
    echo "❌ Registration not found!\n";
    exit(1);
}

echo "✅ Registration found: ID {$reg->id}\n";
echo "   User: {$reg->user->name} ({$reg->user->email})\n";
echo "   Event: {$reg->event->title}\n";
echo "\n";

// Generate new token
$token = str_pad((string)random_int(0,9999999999), 10, '0', STR_PAD_LEFT);
echo "🔑 Generated new token: {$token}\n";

// Update registration
$reg->update([
    'token_plain' => $token,
    'attendance_token' => $token,
    'token_hash' => bcrypt($token),
    'token_sent_at' => now(),
]);

echo "✅ Token saved to database\n";
echo "\n";

// Send email
try {
    \App\Jobs\SendRegistrationTokenJob::dispatchSync($reg->user, $reg->event, $token);
    echo "✅ Email sent successfully!\n";
    echo "\n";
    echo "📧 Check your Gmail: {$reg->user->email}\n";
    echo "📋 Subject: Token Kehadiran - {$reg->event->title}\n";
    echo "🎫 Token: {$token}\n";
} catch (\Exception $e) {
    echo "❌ Email sending failed: {$e->getMessage()}\n";
    exit(1);
}
