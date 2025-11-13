<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Jobs\GenerateCertificatePdfJob;

class CertificateTestSeeder extends Seeder
{
    public function run(): void
    {
        echo "🚀 Creating certificate test data...\n";

        // 1. Get or create test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User Sertifikat',
                'password' => bcrypt('password123'),
                'role' => 'participant',
                'email_verified_at' => now()
            ]
        );
        echo "✅ User: {$user->email}\n";

        // 2. Get test event
        $event = Event::where('title', 'LIKE', '%Testing Sertifikat%')->first();
        if (!$event) {
            echo "❌ Test event not found. Creating one...\n";
            $event = Event::create([
                'title' => 'Workshop Testing Sertifikat - EduFest 2025',
                'description' => 'Event khusus untuk testing flow sertifikat.',
                'event_date' => now()->subDays(1),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'location' => 'Lab Computer SMKN 4 Bogor',
                'price' => 0,
                'is_free' => true,
                'is_published' => true,
                'created_by' => 1,
                'registration_closes_at' => now()->addDays(1)
            ]);
        }
        echo "✅ Event: {$event->title}\n";

        // 3. Get or create registration
        $registration = Registration::firstOrCreate(
            ['user_id' => $user->id, 'event_id' => $event->id],
            [
                'token_hash' => hash('sha256', 'test-token-' . time()),
                'status' => 'registered'
            ]
        );
        echo "✅ Registration: {$registration->id}\n"; 

        // 4. Get or create attendance
        $attendance = Attendance::firstOrCreate(
            ['registration_id' => $registration->id],
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'token_entered' => 'test-token-' . time(),
                'status' => 'present',
                'attendance_time' => now()
            ]
        );
        echo "✅ Attendance: {$attendance->id}\n";

        // 5. Generate certificate
        GenerateCertificatePdfJob::dispatch($registration);
        echo "✅ Certificate generation job dispatched\n";

        echo "\n🎉 Test data created successfully!\n";
        echo "\n📋 Login credentials:\n";
        echo "Email: test@example.com\n";
        echo "Password: password123\n";
        echo "\n📋 Test data:\n";
        echo "User ID: {$user->id}\n";
        echo "Event ID: {$event->id}\n";
        echo "Registration ID: {$registration->id}\n";
        echo "Attendance ID: {$attendance->id}\n";
    }
}
