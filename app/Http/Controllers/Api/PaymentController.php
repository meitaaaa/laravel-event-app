<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Payment;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }

    public function createPayment(Request $request, Event $event)
    {
        try {
            \Log::info('Payment creation started', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'event_price' => $event->price,
                'server_key' => config('midtrans.server_key') ? 'SET' : 'NOT SET',
                'is_production' => config('midtrans.is_production')
            ]);
            
            $user = $request->user();
            
            // Check if event requires payment
            if ($event->is_free) {
                \Log::warning('Attempted payment for free event', ['event_id' => $event->id]);
                return response()->json(['message' => 'Event is free, no payment required'], 400);
            }

            // Check if user already registered
            $existingRegistration = Registration::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();

            if ($existingRegistration) {
                // Check if payment already exists
                $existingPayment = Payment::where('registration_id', $existingRegistration->id)
                    ->where('status', 'paid')
                    ->first();
                
                if ($existingPayment) {
                    return response()->json(['message' => 'Already registered and paid'], 409);
                }
            }

            // Create registration if not exists
            if (!$existingRegistration) {
                $plain = str_pad((string)random_int(0,9999999999), 10, '0', STR_PAD_LEFT);
                $existingRegistration = Registration::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '-',
                    'motivation' => 'Payment registration',
                    'token_hash' => bcrypt($plain),
                    'token_plain' => $plain,
                    'attendance_token' => $plain,
                    'token_sent_at' => now(),
                ]);
                \Log::info('Registration created for payment', ['registration_id' => $existingRegistration->id]);
                
                // Send token via email
                try {
                    \App\Jobs\SendRegistrationTokenJob::dispatchSync($user, $event, $plain);
                    \Log::info('Token email sent for paid event', ['registration_id' => $existingRegistration->id]);
                } catch (\Exception $emailError) {
                    \Log::warning('Email sending failed but registration successful: ' . $emailError->getMessage());
                }
            }

            // Generate unique order ID
            $orderId = 'EVENT-' . $event->id . '-' . $user->id . '-' . time();

            // Create payment record
            $payment = Payment::create([
                'registration_id' => $existingRegistration->id,
                'midtrans_order_id' => $orderId,
                'amount' => $event->price,
                'status' => 'pending'
            ]);

            // Prepare transaction details for Midtrans
            $transactionDetails = [
                'order_id' => $orderId,
                'gross_amount' => (int) $event->price,
            ];

            $itemDetails = [
                [
                    'id' => 'event-' . $event->id,
                    'price' => (int) $event->price,
                    'quantity' => 1,
                    'name' => $event->title,
                    'category' => 'Event Registration'
                ]
            ];

            $customerDetails = [
                'first_name' => $user->name,
                'email' => $user->email,
            ];

            $transactionData = [
                'transaction_details' => $transactionDetails,
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
                'callbacks' => [
                    'finish' => config('app.url') . '/payment/finish'
                ]
            ];

            // Get Snap token
            \Log::info('Requesting Snap token from Midtrans', ['order_id' => $orderId]);
            $snapToken = Snap::getSnapToken($transactionData);
            \Log::info('Snap token received', ['order_id' => $orderId, 'token_length' => strlen($snapToken)]);

            return response()->json([
                'message' => 'Payment created successfully',
                'payment_id' => $payment->id,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'amount' => $event->price
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Payment creation failed', [
                'event_id' => $event->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Payment creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleNotification(Request $request)
    {
        try {
            $notification = new Notification();
            
            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? null;

            // Find payment by order ID
            $payment = Payment::where('midtrans_order_id', $orderId)->first();
            
            if (!$payment) {
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // Update payment with Midtrans response
            $payment->midtrans_response = $request->all();
            $payment->midtrans_transaction_id = $notification->transaction_id;
            $payment->payment_method = $notification->payment_type ?? null;

            // Handle transaction status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $payment->status = 'pending';
                } else if ($fraudStatus == 'accept') {
                    $payment->status = 'paid';
                    $payment->paid_at = now();
                }
            } else if ($transactionStatus == 'settlement') {
                $payment->status = 'paid';
                $payment->paid_at = now();
            } else if ($transactionStatus == 'pending') {
                $payment->status = 'pending';
            } else if ($transactionStatus == 'deny') {
                $payment->status = 'failed';
            } else if ($transactionStatus == 'expire') {
                $payment->status = 'expired';
            } else if ($transactionStatus == 'cancel') {
                $payment->status = 'cancelled';
            }

            $payment->save();

            return response()->json(['message' => 'Notification handled successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Notification handling failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkPaymentStatus(Request $request, $paymentId)
    {
        $payment = Payment::with('registration.event')->find($paymentId);
        
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Check if user owns this payment
        if ($payment->registration->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'payment' => $payment,
            'event' => $payment->registration->event
        ]);
    }
}
