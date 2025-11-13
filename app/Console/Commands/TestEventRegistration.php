<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

class TestEventRegistration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:registration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test event registration functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Event Registration Logic...');
        
        // Get current time
        $now = now();
        $this->info('Current time: ' . $now->format('Y-m-d H:i:s'));
        
        // Check existing events
        $events = Event::all(['id', 'title', 'event_date', 'start_time', 'end_time', 'registration_closes_at']);
        
        $this->info('Found ' . $events->count() . ' events:');
        
        foreach ($events as $event) {
            $this->info('---');
            $this->info('Event: ' . $event->title);
            $this->info('Date: ' . $event->event_date);
            $this->info('Start: ' . $event->start_time);
            $this->info('End: ' . ($event->end_time ?? 'NULL'));
            $this->info('Registration closes at: ' . ($event->registration_closes_at ?? 'NULL'));
            
            // Calculate registration deadline using same logic as controller
            $registrationDeadline = $event->registration_closes_at 
                ? Carbon::parse($event->registration_closes_at)
                : Carbon::parse($event->event_date.' '.($event->start_time ?? '00:00:00'));
            
            $this->info('Calculated deadline: ' . $registrationDeadline->format('Y-m-d H:i:s'));
            
            $isOpen = $now->lessThan($registrationDeadline);
            $this->info('Registration open: ' . ($isOpen ? 'YES' : 'NO'));
        }
        
        // Create a test event with future date if none exist
        if ($events->isEmpty()) {
            $this->info('Creating test event...');
            
            $user = User::first();
            if (!$user) {
                $this->error('No users found. Please create a user first.');
                return;
            }
            
            $testEvent = Event::create([
                'title' => 'Test Event - Future',
                'description' => 'Test event for registration testing',
                'event_date' => now()->addDays(7)->format('Y-m-d'),
                'start_time' => '10:00:00',
                'end_time' => '16:00:00',
                'location' => 'Test Location',
                'is_published' => true,
                'created_by' => $user->id
            ]);
            
            $this->info('Created test event: ' . $testEvent->title);
            $this->info('Event date: ' . $testEvent->event_date . ' ' . $testEvent->start_time);
        }
        
        $this->info('Test completed!');
    }
}
