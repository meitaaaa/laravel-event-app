<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Check if admin already exists
        $admin = User::where('email', 'admin@example.com')->first();
        
        if (!$admin) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now()
            ]);
            
            echo "✅ Admin user created successfully!\n";
            echo "📧 Email: admin@example.com\n";
            echo "🔑 Password: admin123\n";
        } else {
            echo "⚠️ Admin user already exists\n";
        }
    }
}
