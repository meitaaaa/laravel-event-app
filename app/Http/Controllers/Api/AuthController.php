<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Models\EmailOtp;
use App\Jobs\SendOtpJob;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller {
    public function register(RegisterRequest $req){
        $user = User::create([
            'name'=>$req->name,
            'email'=>$req->email,
            'phone'=>$req->phone,
            'address'=>$req->address,
            'education'=>$req->education,
            'password'=>Hash::make($req->password)
        ]);

        $otp = str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT);
        EmailOtp::create([
            'user_id'=>$user->id,
            'code_hash'=>Hash::make($otp),
            'type'=>'verification',
            'expires_at'=>now()->addMinutes(10)
        ]);

        // Send OTP via email
        SendOtpJob::dispatch($user, $otp, 'verification');

        return response()->json([
            'message'=>'Registered. Check email for OTP.',
            'user_id'=>$user->id
        ],201);
    }

    public function verifyEmail(VerifyOtpRequest $req){
        // Debug: Check all OTPs for this user
        $allOtps = EmailOtp::where('user_id',$req->user_id)
            ->where('type','verification')
            ->get();

        // Check if EmailOtp exists first
        $otp = EmailOtp::where('user_id',$req->user_id)
            ->where('type','verification')
            ->whereNull('used_at')
            ->where('expires_at','>=',now())
            ->latest()
            ->first();

        if(!$otp){
            return response()->json([
                'message'=>'No valid OTP found. Please register first or request new OTP.',
                'debug_info' => [
                    'user_id' => $req->user_id,
                    'total_otps' => $allOtps->count(),
                    'otps' => $allOtps->map(function($item) {
                        return [
                            'id' => $item->id,
                            'used_at' => $item->used_at,
                            'expires_at' => $item->expires_at,
                            'expired' => $item->expires_at < now(),
                            'used' => !is_null($item->used_at)
                        ];
                    })
                ]
            ], 404);
        }

        if(!Hash::check($req->code, $otp->code_hash)){
            return response()->json(['message'=>'Invalid OTP'],422);
        }

        $otp->update(['used_at'=>now()]);
        
        // Use DB query instead of Eloquent for direct update
        $updated = \DB::table('users')
            ->where('id', $req->user_id)
            ->update(['email_verified_at' => now()]);
        
        // Get fresh user data
        $user = User::find($req->user_id);

        return response()->json([
            'message'=>'Email verified successfully',
            'user_id' => $user->id,
            'email_verified_at' => $user->email_verified_at,
            'update_success' => $updated > 0
        ]);
    }

    public function login(LoginRequest $req){
        $user = User::where('email',$req->email)->first();

        if(!$user){
            return response()->json(['message'=>'User not found'],404);
        }

        if(!Hash::check($req->password, $user->password)){
            return response()->json(['message'=>'Invalid password'],401);
        }

        // Debug: Check email verification status
        if(!$user->email_verified_at){
            return response()->json([
                'message'=>'Email not verified. Please verify your email first.',
                'debug_info' => [
                    'user_id' => $user->id,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at
                ]
            ],403);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['token'=>$token,'user'=>$user]);
    }

    public function requestReset(Request $req){
        $req->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $req->email)->first();

        if(!$user->email_verified_at){
            return response()->json(['message'=>'Email not verified. Please verify your email first.'],403);
        }

        // Generate OTP for password reset
        $otp = str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT);
        EmailOtp::create([
            'user_id'=>$user->id,
            'code_hash'=>Hash::make($otp),
            'type'=>'reset',
            'expires_at'=>now()->addMinutes(10)
        ]);

        // Send OTP via email
        SendOtpJob::dispatch($user, $otp, 'reset');

        return response()->json([
            'message'=>'Password reset OTP sent to your email.',
            'user_id'=>$user->id
        ]);
    }

    public function resetPassword(Request $req){
        $req->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $otp = EmailOtp::where('user_id',$req->user_id)
            ->where('type','reset')
            ->whereNull('used_at')
            ->where('expires_at','>=',now())
            ->latest()
            ->first();

        if(!$otp){
            return response()->json(['message'=>'Invalid or expired OTP'],404);
        }

        if(!Hash::check($req->code, $otp->code_hash)){
            return response()->json([
                'message'=>'Invalid OTP',
                'debug_info' => [
                    'provided_code' => $req->code,
                    'otp_id' => $otp->id,
                    'expires_at' => $otp->expires_at,
                    'created_at' => $otp->created_at
                ]
            ],422);
        }

        // Update password
        $user = User::findOrFail($req->user_id);
        $user->update(['password' => Hash::make($req->password)]);

        // Mark OTP as used
        $otp->update(['used_at'=>now()]);

        return response()->json(['message'=>'Password reset successfully']);
    }
}
