<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\User;

echo "=== CHECK ALL TOKENS FOR EVENT 52 ===\n\n";

$eventId = 52;

$registrations = Registration::where('event_id', $eventId)
    ->with('user')
    ->get();

echo "Total registrations: " . $registrations->count() . "\n\n";

foreach ($registrations as $reg) {
    echo "=== REGISTRATION #{$reg->id} ===\n";
    echo "User: {$reg->user->name} ({$reg->user->email})\n";
    echo "Status: {$reg->status}\n";
    echo "token_plain: " . ($reg->token_plain ?? 'NULL') . "\n";
    echo "attendance_token: " . ($reg->attendance_token ?? 'NULL') . "\n";
    echo "token_sent_at: " . ($reg->token_sent_at ?? 'NULL') . "\n";
    echo "Created: {$reg->created_at}\n";
    
    // Check if tokens match
    if ($reg->token_plain && $reg->attendance_token) {
        if ($reg->token_plain === $reg->attendance_token) {
            echo "✅ Tokens MATCH\n";
        } else {
            echo "❌ Tokens MISMATCH!\n";
            echo "   token_plain: {$reg->token_plain}\n";
            echo "   attendance_token: {$reg->attendance_token}\n";
        }
    } else {
        echo "⚠️ One or both tokens are NULL\n";
    }
    
    echo "\n";
}

echo "=== DONE ===\n";
