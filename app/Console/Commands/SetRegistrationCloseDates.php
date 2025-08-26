<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Carbon\Carbon;

class SetRegistrationCloseDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:set-registration-closes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set registration_closes_at for events that don\'t have it';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting registration close dates...');
        
        $events = Event::whereNull('registration_closes_at')->get();
        
        foreach ($events as $event) {
            $closeDate = Carbon::parse($event->event_date . ' ' . $event->start_time);
            $event->update(['registration_closes_at' => $closeDate]);
            $this->line("Event: {$event->title} - Close date set to: {$closeDate}");
        }
        
        $this->info("Updated {$events->count()} events.");
        
        return Command::SUCCESS;
    }
}
