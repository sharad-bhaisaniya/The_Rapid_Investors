<?php

namespace App\Http\Controllers\UserDashboardController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
    use App\Models\User;

class EmailOtpController extends Controller
{
    /**
     * Send OTP to Email
     */

        public function send(Request $request)
        {
            try {
                $validator = Validator::make($request->all(), [
                    'email' => 'required|email'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid email address'
                    ], 422);
                }

                $email = $request->email;
                $user  = auth()->user();

                /**
                 * 🚫 CHECK: Email already used by another user
                 */
                $emailExists = User::where('email', $email)
                    ->where('id', '!=', $user->id) // exclude current user
                    ->exists();

                if ($emailExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email address is already in use'
                    ], 409);
                }

                // 🔄 Clear previous OTP
                Session::forget('email_otp');

                // 🔐 Generate OTP
                $otp = random_int(100000, 999999);

                // 🕒 Store OTP in session
                Session::put('email_otp', [
                    'email'      => $email,
                    'otp'        => $otp,
                    'expires_at' => time() + (5 * 60), // 5 minutes
                    'attempts'   => 0,
                ]);

                // ✉️ Send OTP
                Mail::raw(
                    "Your OTP for email verification is: {$otp}. This OTP is valid for 5 minutes.",
                    function ($message) use ($email) {
                        $message->to($email)
                                ->subject('Email Verification OTP');
                    }
                );

                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent successfully'
                ]);

            } catch (\Throwable $e) {

                Log::error('EMAIL OTP SEND ERROR', [
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to send OTP. Please try again.'
                ], 500);
            }
        }
    /**
     * Verify Email OTP
     */

        public function verify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp'   => 'required|digits:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP or email format'
                ], 422);
            }

            $sessionData = Session::get('email_otp');

            if (!$sessionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP expired. Please resend.'
                ]);
            }

            // ⏰ Expiry check
            if (time() > $sessionData['expires_at']) {
                Session::forget('email_otp');

                return response()->json([
                    'success' => false,
                    'message' => 'OTP expired. Please resend.'
                ]);
            }

            // ❌ Email mismatch
            if ($sessionData['email'] !== $request->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email does not match OTP request'
                ]);
            }

            // ❌ Wrong OTP
            if ((string)$sessionData['otp'] !== (string)$request->otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect OTP'
                ]);
            }

            /**
             * 🚫 SAFETY: Re-check email uniqueness
             */
            $user = auth()->user();

            $emailExists = User::where('email', $request->email)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email address is already in use'
                ], 409);
            }

            // ✅ SUCCESS — Update email
            $user->update([
                'email' => $request->email
            ]);

            Session::forget('email_otp');

            return response()->json([
                'success' => true,
                'message' => 'Email verified and updated successfully'
            ]);

        } catch (\Throwable $e) {

            Log::error('EMAIL OTP VERIFY ERROR', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error while verifying OTP'
            ], 500);
        }
    }
}