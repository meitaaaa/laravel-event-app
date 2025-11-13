<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Event;
use App\Models\User;

// Check event 52
$event = Event::find(52);
if (!$event) {
    echo "Event 52 tidak ditemukan!\n";
    exit;
}

echo "Event: {$event->title}\n";
echo "Event ID: {$event->id}\n\n";

// Check registrations for this event
$registrations = Registration::where('event_id', 52)->with('user')->get();

echo "Total Registrations: " . $registrations->count() . "\n\n";

foreach ($registrations as $reg) {
    echo "Registration ID: {$reg->id}\n";
    echo "User: {$reg->user->name} ({$reg->user->email})\n";
    echo "Token: {$reg->attendance_token}\n";
    echo "Status: {$reg->status}\n";
    echo "---\n";
}
