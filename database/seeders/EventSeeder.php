<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user or create one
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

        $events = [
            [
                'title' => 'Lomba Programming Competition 2025',
                'description' => 'Kompetisi pemrograman tingkat sekolah untuk siswa SMKN 4 Bogor. Peserta akan mengerjakan algoritma dan struktur data dalam waktu terbatas. Lomba ini bertujuan untuk mengasah kemampuan logika dan problem solving siswa.',
                'event_date' => Carbon::today()->format('Y-m-d'),
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'location' => 'Lab Computer SMKN 4 Bogor',
                'price' => 0,
                'is_free' => true,
                'is_published' => true,
                'created_by' => $admin->id,
                'registration_closes_at' => Carbon::now()->addDays(1)
            ],
            [
                'title' => 'Festival Seni dan Budaya SMKN 4',
                'description' => 'Pameran karya seni siswa dan pertunjukan budaya tradisional. Menampilkan kreativitas siswa dalam berbagai bidang seni seperti lukis, musik, tari, dan teater. Event ini merayakan kekayaan budaya Indonesia.',
                'event_date' => Carbon::now()->addDays(8),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'location' => 'Aula SMKN 4 Bogor',
                'price' => 25000,
                'is_free' => false,
                'is_published' => true,
                'created_by' => $admin->id,
                'registration_closes_at' => Carbon::now()->addDays(7)
            ],
            [
                'title' => 'Expo Karya Siswa 2025',
                'description' => 'Pameran proyek akhir dan karya inovatif siswa dari berbagai jurusan. Showcase teknologi dan kreativitas terbaru dari siswa SMKN 4 Bogor. Menampilkan aplikasi, robot, desain grafis, dan produk kreatif lainnya.',
                'event_date' => Carbon::now()->addDays(12),
                'start_time' => '08:30:00',
                'end_time' => '16:30:00',
                'location' => 'Gedung Pameran SMKN 4 Bogor',
                'price' => 15000,
                'is_free' => false,
                'is_published' => true,
                'created_by' => $admin->id,
                'registration_closes_at' => Carbon::now()->addDays(10)
            ],
            [
                'title' => 'Workshop Digital Marketing',
                'description' => 'Pelatihan intensif tentang strategi pemasaran digital untuk era modern. Materi meliputi social media marketing, SEO, content creation, dan analytics. Dibimbing oleh praktisi berpengalaman di bidang digital marketing.',
                'event_date' => Carbon::now()->addDays(15),
                'start_time' => '09:00:00',
                'end_time' => '15:00:00',
                'location' => 'Ruang Multimedia SMKN 4 Bogor',
                'price' => 50000,
                'is_free' => false,
                'is_published' => true,
                'created_by' => $admin->id,
                'registration_closes_at' => Carbon::now()->addDays(13)
            ],
            [
                'title' => 'Seminar Karir dan Entrepreneurship',
                'description' => 'Seminar inspiratif tentang peluang karir dan kewirausahaan bagi lulusan SMK. Menghadirkan alumni sukses dan pengusaha muda sebagai pembicara. Memberikan wawasan tentang dunia kerja dan peluang bisnis.',
                'event_date' => Carbon::now()->addDays(18),
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
                'location' => 'Auditorium SMKN 4 Bogor',
                'price' => 0,
                'is_free' => true,
                'is_published' => true,
                'created_by' => $admin->id,
                'registration_closes_at' => Carbon::now()->addDays(16)
            ],
            [
                'title' => 'Turnamen E-Sports Mobile Legends',
                'description' => 'Kompetisi game Mobile Legends Bang Bang antar siswa SMKN 4 Bogor. Turnamen sistem gugur dengan hadiah menarik untuk juara. Ajang untuk menunjukkan skill gaming dan kerja sama tim.',
                'event_date' => Carbon::now()->addDays(20),
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'location' => 'Lab Gaming SMKN 4 Bogor',
                'price' => 20000,
                'is_free' => false,
                'is_published' => true,
                'created_by' => $admin->id,
                'registration_closes_at' => Carbon::now()->addDays(18)
            ]
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }

        echo "Successfully created " . count($events) . " sample events!\n";
    }
}
