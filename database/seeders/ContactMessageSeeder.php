<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactMessage;
use Carbon\Carbon;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            [
                'name' => 'Mehwish',
                'email' => 'mehwish@example.com',
                'subject' => 'Informasi Kegiatan',
                'message' => 'Saya ingin menanyakan informasi lebih lanjut tentang kegiatan yang akan datang.',
                'is_read' => false,
                'created_at' => Carbon::now()->subDays(1)
            ],
            [
                'name' => 'Elizabeth Jett',
                'email' => 'elizabeth.jett@example.com',
                'subject' => 'Pendaftaran Event',
                'message' => 'Bagaimana cara mendaftar untuk event teknologi bulan depan?',
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(12)
            ],
            [
                'name' => 'Emily Thomas',
                'email' => 'emily.thomas@example.com',
                'subject' => 'Sertifikat',
                'message' => 'Kapan sertifikat untuk event workshop akan diterbitkan?',
                'is_read' => true,
                'read_at' => Carbon::now()->subHours(6),
                'created_at' => Carbon::now()->subDays(2)
            ],
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@smkn4bogor.sch.id',
                'subject' => 'Kerjasama',
                'message' => 'Saya tertarik untuk mengadakan kerjasama dalam event teknologi. Apakah bisa diatur pertemuan untuk membahas lebih lanjut?',
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(3)
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@gmail.com',
                'subject' => 'Lainnya',
                'message' => 'Bagaimana cara mendapatkan sertifikat untuk event yang sudah saya ikuti bulan lalu? Terima kasih.',
                'is_read' => true,
                'read_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subDays(3)
            ]
        ];

        foreach ($messages as $message) {
            ContactMessage::create($message);
        }
    }
}
