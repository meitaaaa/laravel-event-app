<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Event;

echo "=== CHECKING TOKEN ISSUE FOR EVENT 39 ===\n\n";

$event = Event::find(39);
if (!$event) {
    echo "Event 39 not found!\n";
    exit;
}

echo "Event: {$event->title}\n";
echo "Event ID: {$event->id}\n\n";

$registrations = Registration::where('event_id', 39)->get();

echo "Total registrations: " . $registrations->count() . "\n\n";

foreach ($registrations as $reg) {
    echo "Registration ID: {$reg->id}\n";
    echo "User ID: {$reg->user_id}\n";
    echo "User Email: {$reg->user->email}\n";
    echo "Attendance Token: '{$reg->attendance_token}'\n";
    echo "Token Length: " . strlen($reg->attendance_token) . "\n";
    echo "Token Type: " . gettype($reg->attendance_token) . "\n";
    echo "Is Numeric: " . (is_numeric($reg->attendance_token) ? 'YES' : 'NO') . "\n";
    echo "---\n";
}

// Test the token from the screenshot
$testToken = '5642773088';
echo "\n=== TESTING TOKEN: {$testToken} ===\n";

$found = Registration::where('event_id', 39)
    ->where('attendance_token', $testToken)
    ->first();

if ($found) {
    echo "✅ Token FOUND!\n";
    echo "Registration ID: {$found->id}\n";
    echo "User: {$found->user->name} ({$found->user->email})\n";
} else {
    echo "❌ Token NOT FOUND!\n";
    echo "Trying to find similar tokens...\n";
    
    $similar = Registration::where('event_id', 39)
        ->where('attendance_token', 'LIKE', '%' . substr($testToken, 0, 5) . '%')
        ->get();
    
    if ($similar->count() > 0) {
        echo "Found similar tokens:\n";
        foreach ($similar as $s) {
            echo "  - Token: '{$s->attendance_token}' (User: {$s->user->email})\n";
        }
    }
}
