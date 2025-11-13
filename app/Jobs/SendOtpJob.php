<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendOtpJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $user;
    public $otp;
    public $type;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $otp, string $type)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subject = $this->type === 'verification' 
            ? 'Verifikasi Email - EduFest' 
            : 'Reset Password - EduFest';

        Mail::send('emails.otp', [
            'user' => $this->user,
            'otp' => $this->otp,
            'type' => $this->type
        ], function ($mail) use ($subject) {
            $mail->to($this->user->email, $this->user->name)
                 ->subject($subject);
        });
    }
}
