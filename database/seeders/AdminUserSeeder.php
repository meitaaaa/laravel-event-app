<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@smkn4bogor.sch.id'],
            [
                'name' => 'Admin SMKN 4 Bogor',
                'email' => 'admin@smkn4bogor.sch.id',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create another admin user with simple credentials
        User::updateOrCreate(
            ['email' => 'admin@edufest.com'],
            [
                'name' => 'EduFest Admin',
                'email' => 'admin@edufest.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        echo "Admin users created successfully!\n";
        echo "Admin 1: admin@smkn4bogor.sch.id / admin123\n";
        echo "Admin 2: admin@edufest.com / password\n";
    }
}
