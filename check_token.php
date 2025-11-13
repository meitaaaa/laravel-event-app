<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Event;

echo "=== CHECK TOKEN ===\n\n";

$token = '7892749468';
$eventId = 52; // Latihan Frontend

echo "Token: $token\n";
echo "Event ID: $eventId\n\n";

// Find registration by token
$registration = Registration::where('event_id', $eventId)
    ->where('attendance_token', $token)
    ->with(['user', 'event'])
    ->first();

if ($registration) {
    echo "✅ REGISTRATION FOUND!\n\n";
    echo "Registration ID: {$registration->id}\n";
    echo "User: {$registration->user->name} ({$registration->user->email})\n";
    echo "Event: {$registration->event->title}\n";
    echo "Attendance Token: {$registration->attendance_token}\n";
    echo "Status: {$registration->status}\n";
    echo "Created: {$registration->created_at}\n";
} else {
    echo "❌ REGISTRATION NOT FOUND!\n\n";
    
    // Check all registrations for this event
    echo "Checking all registrations for event $eventId:\n\n";
    $allRegs = Registration::where('event_id', $eventId)
        ->with('user')
        ->get();
    
    echo "Total registrations: " . $allRegs->count() . "\n\n";
    
    foreach ($allRegs as $reg) {
        echo "- User: {$reg->user->name}\n";
        echo "  Token: {$reg->attendance_token}\n";
        echo "  Status: {$reg->status}\n\n";
    }
}

echo "=== DONE ===\n";
