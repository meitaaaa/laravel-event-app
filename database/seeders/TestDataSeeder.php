<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Check if test user already exists
        $user = User::where('email', 'test@example.com')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'role' => 'participant'
            ]);
            echo "Created new test user\n";
        } else {
            echo "Test user already exists\n";
        }

        // Check if test event already exists
        $event = Event::where('title', 'Test Event 2025')->first();
        if (!$event) {
            $event = Event::create([
                'title' => 'Test Event 2025',
                'description' => 'This is a test event for testing certificate generation',
                'event_date' => Carbon::now()->addDays(7),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'location' => 'Test Location',
                'is_published' => true,
                'created_by' => $user->id
            ]);
            echo "Created new test event\n";
        } else {
            echo "Test event already exists\n";
        }

        // Check if registration already exists
        $registration = Registration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
        if (!$registration) {
            $registration = Registration::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'token_hash' => Str::random(10), // Generate 10 character hash
                'status' => 'registered' // Use correct enum value
            ]);
            echo "Created new registration\n";
        } else {
            echo "Registration already exists\n";
        }

        // Check if attendance already exists
        $attendance = Attendance::where('registration_id', $registration->id)->first();
        if (!$attendance) {
            $attendance = Attendance::create([
                'registration_id' => $registration->id,
                'event_id' => $event->id,
                'user_id' => $user->id,
                'token_entered' => Str::random(6), // Generate 6 character token
                'attendance_time' => Carbon::now(),
                'status' => 'present'
            ]);
            echo "Created new attendance\n";
        } else {
            echo "Attendance already exists\n";
        }

        echo "\n=== Test Data Summary ===\n";
        echo "User ID: {$user->id}\n";
        echo "Event ID: {$event->id}\n";
        echo "Registration ID: {$registration->id}\n";
        echo "Attendance ID: {$attendance->id}\n";
        echo "========================\n";
    }
}
