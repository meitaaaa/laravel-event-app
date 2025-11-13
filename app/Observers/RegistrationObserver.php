<?php

namespace App\Observers;

use App\Models\Registration;
use Illuminate\Support\Facades\Log;

class RegistrationObserver
{
    /**
     * Handle the Registration "creating" event.
     * Ensure attendance_token is set correctly before saving
     */
    public function creating(Registration $registration): void
    {
        // If attendance_token is not set, use token_plain
        if (empty($registration->attendance_token) && !empty($registration->token_plain)) {
            $registration->attendance_token = $registration->token_plain;
            
            Log::info('RegistrationObserver: Set attendance_token from token_plain', [
                'token_plain' => $registration->token_plain,
                'attendance_token' => $registration->attendance_token
            ]);
        }
    }

    /**
     * Handle the Registration "updating" event.
     * Prevent attendance_token from being changed to invalid format
     */
    public function updating(Registration $registration): void
    {
        // Check if attendance_token is being changed
        if ($registration->isDirty('attendance_token')) {
            $newToken = $registration->attendance_token;
            $oldToken = $registration->getOriginal('attendance_token');
            
            // Validate new token format (must be 10 digits)
            if (!empty($newToken) && !preg_match('/^\d{10}$/', $newToken)) {
                Log::warning('RegistrationObserver: Prevented invalid attendance_token update', [
                    'registration_id' => $registration->id,
                    'old_token' => $oldToken,
                    'new_token' => $newToken,
                    'token_plain' => $registration->token_plain
                ]);
                
                // Revert to token_plain if invalid format
                $registration->attendance_token = $registration->token_plain;
            }
        }
    }

    /**
     * Handle the Registration "created" event.
     * Verify attendance_token is correctly set after creation
     */
    public function created(Registration $registration): void
    {
        // Verify token consistency
        if ($registration->attendance_token !== $registration->token_plain) {
            Log::error('RegistrationObserver: Token mismatch detected after creation!', [
                'registration_id' => $registration->id,
                'token_plain' => $registration->token_plain,
                'attendance_token' => $registration->attendance_token,
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id
            ]);
            
            // Auto-fix the mismatch
            $registration->update(['attendance_token' => $registration->token_plain]);
            
            Log::info('RegistrationObserver: Auto-fixed token mismatch', [
                'registration_id' => $registration->id,
                'corrected_token' => $registration->token_plain
            ]);
        }
    }

    /**
     * Handle the Registration "updated" event.
     * Verify attendance_token remains valid after update
     */
    public function updated(Registration $registration): void
    {
        // Verify token consistency after update
        if ($registration->attendance_token !== $registration->token_plain) {
            Log::warning('RegistrationObserver: Token mismatch detected after update!', [
                'registration_id' => $registration->id,
                'token_plain' => $registration->token_plain,
                'attendance_token' => $registration->attendance_token
            ]);
        }
    }
}
