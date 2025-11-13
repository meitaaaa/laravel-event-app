<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

class TestEventSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@smkn4bogor.sch.id',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'email_verified_at' => now()
            ]);
        }

        // Create test event for certificate testing
        $testEvent = Event::create([
            'title' => 'Workshop Testing Sertifikat - EduFest 2025',
            'description' => 'Event khusus untuk testing flow sertifikat. Event ini gratis dan terbuka untuk semua peserta. Setelah mengikuti event, peserta akan mendapatkan sertifikat keikutsertaan yang dapat didownload.',
            'event_date' => Carbon::now()->subDays(1), // Event kemarin (sudah selesai)
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'location' => 'Lab Computer SMKN 4 Bogor',
            'price' => 0,
            'is_free' => true,
            'is_published' => true,
            'created_by' => $admin->id,
            'registration_closes_at' => Carbon::now()->addDays(1) // Masih bisa daftar
        ]);

        echo "Test event created with ID: " . $testEvent->id . "\n";
        echo "Event Title: " . $testEvent->title . "\n";
        echo "Event Date: " . $testEvent->event_date . "\n";
        echo "Registration still open until: " . $testEvent->registration_closes_at . "\n";
    }
}


