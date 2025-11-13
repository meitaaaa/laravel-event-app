<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            $this->info('Testing email configuration...');
            
            Mail::raw('Test email from Laravel Event App', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - Laravel Event App')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info('Email sent successfully to: ' . $email);
            
        } catch (\Exception $e) {
            $this->error('Email failed: ' . $e->getMessage());
            $this->error('Error details: ' . $e->getTraceAsString());
        }
    }
}
