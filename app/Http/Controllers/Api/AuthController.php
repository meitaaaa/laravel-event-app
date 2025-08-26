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

        SendOtpJob::dispatch($user, $otp, 'verification');

        return response()->json(['message'=>'Registered. Check email for OTP.'],201);
    }

    public function verifyEmail(VerifyOtpRequest $req){
        $otp = EmailOtp::where('user_id',$req->user_id)
            ->where('type','verification')
            ->whereNull('used_at')
            ->where('expires_at','>=',now())
            ->latest()
            ->firstOrFail();

        if(!Hash::check($req->code, $otp->code_hash)){
            return response()->json(['message'=>'Invalid OTP'],422);
        }

        $otp->update(['used_at'=>now()]);
        $user = User::findOrFail($req->user_id);
        $user->update(['email_verified_at'=>now()]);

        return response()->json(['message'=>'Email verified']);
    }

    public function login(LoginRequest $req){
        $user = User::where('email',$req->email)->first();

        if(!$user || !Hash::check($req->password, $user->password)){
            return response()->json(['message'=>'Invalid credentials'],401);
        }

        if(!$user->email_verified_at){
            return response()->json(['message'=>'Email not verified'],403);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['token'=>$token,'user'=>$user]);
    }
}
