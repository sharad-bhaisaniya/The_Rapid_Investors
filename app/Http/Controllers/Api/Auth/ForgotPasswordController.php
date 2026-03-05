<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'identity' => 'required|string'
        ]);

        $identity = $request->identity;
        $isEmail  = filter_var($identity, FILTER_VALIDATE_EMAIL);

        // User check
        $user = $isEmail
            ? User::where('email', $identity)->first()
            : User::where('phone', $identity)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'This ' . ($isEmail ? 'email' : 'mobile number') . ' is not registered with us.'
            ], 404);
        }

        // Generate OTP
        $otp = random_int(100000, 999999);

        // ❌ NO DATABASE SAVE HERE

        if ($isEmail) {

            try {
                Mail::send([], [], function ($message) use ($identity, $otp, $user) {

                    $message->to($identity)
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                        ->subject('Your Password Reset OTP')
                        ->html("
                            <div style='font-family:Arial;padding:20px'>
                                <h2>Hello " . ($user->name ?? 'User') . ",</h2>
                                <p>Your OTP is:</p>
                                <h1 style='letter-spacing:4px;color:#2563eb'>{$otp}</h1>
                                <p>This OTP is valid for 10 minutes.</p>
                            </div>
                        ");
                });

            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unable to send OTP email.'
                ], 500);
            }

        } else {

            if (!$this->sendOtpSms($identity, $otp)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send SMS.'
                ], 500);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully.',
            'otp' => $otp // ⚠️ REMOVE THIS IN PRODUCTION
        ]);
    }



    private function sendOtpSms($phone, $otp)
    {
        $user = "User";
     $message = "Dear {$user},\n"
     . "Your OTP is {$otp}.\n"
     . "This OTP is valid for 10 minutes.\n"
     . "Do not share this OTP with anyone.\n"
     . "The Rapid Investors.";

        $response = Http::get(config('services.sms.base_url'), [
            'user'      => config('services.sms.user'),
            'key'       => config('services.sms.key'),
            'mobile'    => config('services.sms.country') . $phone,
            'message'   => $message,
            'senderid'  => config('services.sms.sender'),
            'accusage'  => 1,
            'entityid'  => config('services.sms.entity_id'),
            'tempid'    => config('services.sms.template_id'),
        ]);

        return $response->successful();
    }
}