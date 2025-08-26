<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailOtp;

class CleanExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired OTPs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning expired OTPs...');
        
        $deleted = EmailOtp::where('expires_at', '<', now())
            ->whereNull('used_at')
            ->delete();
            
        $this->info("Deleted {$deleted} expired OTPs.");
        
        return Command::SUCCESS;
    }
}
