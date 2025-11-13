<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\Attendance;
use App\Models\Certificate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DummyParticipantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all events
        $events = Event::all();
        
        if ($events->isEmpty()) {
            $this->command->error('No events found! Please create events first.');
            return;
        }
        
        $this->command->info('Creating dummy participants...');
        
        // Create or get 50 dummy users
        $dummyUsers = [];
        for ($i = 1; $i <= 50; $i++) {
            $email = 'dummy' . $i . '@example.com';
            
            // Check if user already exists
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => 'Peserta Dummy ' . $i,
                    'email' => $email,
                    'phone' => '08' . rand(1000000000, 9999999999),
                    'address' => 'Jl. Dummy No. ' . $i . ', Jakarta',
                    'education' => ['SMA', 'D3', 'S1', 'S2'][rand(0, 3)],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]);
            }
            
            $dummyUsers[] = $user;
        }
        
        $this->command->info('Prepared 50 dummy users');
        
        // Register dummy users to random events
        $registrationCount = 0;
        $paymentCount = 0;
        
        foreach ($events as $event) {
            // Random number of participants per event (10-30)
            $participantCount = rand(10, 30);
            
            // Shuffle users and take random participants
            $selectedUsers = collect($dummyUsers)->random(min($participantCount, count($dummyUsers)));
            
            foreach ($selectedUsers as $user) {
                // Check if already registered
                $existingReg = Registration::where('user_id', $user->id)
                    ->where('event_id', $event->id)
                    ->first();
                
                if ($existingReg) {
                    continue; // Skip if already registered
                }
                
                // Create registration
                $registration = Registration::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'status' => 'registered',
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'motivation' => 'Saya ingin mengikuti event ini untuk menambah wawasan dan pengalaman.',
                    'token_hash' => Hash::make($token = Str::random(6)),
                    'token_plain' => $token,
                    'token_sent_at' => now(),
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
                
                $registrationCount++;
                
                // Create payment if event is not free
                if (!$event->is_free && $event->price > 0) {
                    // 80% chance of paid, 20% chance of pending
                    $isPaid = rand(1, 100) <= 80;
                    
                    $payment = Payment::create([
                        'registration_id' => $registration->id,
                        'midtrans_order_id' => 'DUMMY-' . Str::upper(Str::random(10)),
                        'midtrans_transaction_id' => 'TRX-' . Str::upper(Str::random(15)),
                        'amount' => $event->price,
                        'status' => $isPaid ? 'paid' : 'pending',
                        'payment_method' => ['bank_transfer', 'credit_card', 'gopay', 'qris'][rand(0, 3)],
                        'paid_at' => $isPaid ? now()->subDays(rand(1, 25)) : null,
                        'created_at' => $registration->created_at,
                    ]);
                    
                    if ($isPaid) {
                        $paymentCount++;
                    }
                }
                
                // 70% chance of attendance
                if (rand(1, 100) <= 70) {
                    Attendance::create([
                        'registration_id' => $registration->id,
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'token_entered' => $registration->token_plain,
                        'status' => 'present',
                        'attendance_time' => now()->subDays(rand(0, 20)),
                    ]);
                    
                    // 60% chance of certificate if attended
                    if (rand(1, 100) <= 60) {
                        Certificate::create([
                            'registration_id' => $registration->id,
                            'serial_number' => 'CERT-' . strtoupper(Str::random(10)),
                            'file_path' => 'certificates/dummy-cert-' . $registration->id . '.pdf',
                            'issued_at' => now()->subDays(rand(0, 15)),
                        ]);
                    }
                }
            }
            
            $this->command->info("Event '{$event->title}': {$participantCount} participants");
        }
        
        $this->command->info('');
        $this->command->info('=== Summary ===');
        $this->command->info("Total registrations created: {$registrationCount}");
        $this->command->info("Total paid payments: {$paymentCount}");
        $this->command->info('');
        $this->command->info('✓ Dummy participants seeded successfully!');
    }
}
