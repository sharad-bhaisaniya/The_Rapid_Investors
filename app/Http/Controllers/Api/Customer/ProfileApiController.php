<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// We no longer need Session, we will use Cache
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class ProfileApiController extends Controller
{
    public function getProfile()
    {
        $user = Auth::user();
        $user->profile_image_url = $user->getFirstMediaUrl('profile_image');

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function updateGeneralProfile(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name'    => 'required|string|max:255',
            'dob'     => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ]);

        $user->update($validatedData);

        if ($request->hasFile('profile_image')) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile details updated successfully!',
            'user' => $user->refresh()
        ]);
    }

    /**
     * Send OTP for Email/Phone change with Cache instead of Session
     */
    public function sendUpdateOtp(Request $request)
    {
        Log::info('📱 [API] OTP Request Received', [
            'user_id' => Auth::id(),
            'payload' => $request->all()
        ]);

        $request->validate([
            'type'  => 'required|in:email,phone',
            'value' => 'required',
        ]);

        $otp   = random_int(100000, 999999);
        $type  = $request->type;
        $value = $request->value;
        $user  = Auth::user();

        // 🛑 PRE-CHECK: Duplicate value
        $exists = User::where($type, $value)->where('id', '!=', $user->id)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => "This $type is already registered to another account."], 422);
        }

        // ✅ Store OTP in Cache using the User ID and Type as a unique key
        $cacheKey = "otp_verify_{$user->id}_{$type}";
        
        Cache::put($cacheKey, [
            'otp'        => $otp,
            'value'      => $value,
            'expires_at' => now()->addMinutes(10),
        ], now()->addMinutes(10));

        Log::info('🔑 [API] OTP Generated & Cache Stored', [
            'type' => $type,
            'otp'  => $otp, // Remember to hide this in production logs later
            'cache_key' => $cacheKey
        ]);

        if ($type === 'email') {
            return $this->sendEmailOtp($value, $otp, $user);
        }

        // Run SMS Logic
        return $this->sendSmsOtp($value, $otp);
    }

    public function verifyAndUpdate(Request $request)
    {
        $user = Auth::user();
        Log::info('📩 [API] Verify Request Received', ['payload' => $request->all(), 'user_id' => $user->id]);

        $request->validate([
            'type' => 'required|in:email,phone',
            'otp'  => 'required|numeric'
        ]);

        $type = $request->type;
        
        // Pull from Cache using the unique key
        $cacheKey = "otp_verify_{$user->id}_{$type}";
        $sessionData = Cache::get($cacheKey);

        if (!$sessionData) {
            Log::warning('⚠️ [API] No cache data found for OTP');
            return response()->json(['success' => false, 'message' => 'OTP session expired or not found.'], 422);
        }

        if (now()->gt($sessionData['expires_at'])) {
            return response()->json(['success' => false, 'message' => 'OTP expired.'], 422);
        }

        if ($request->otp != $sessionData['otp']) {
            return response()->json(['success' => false, 'message' => 'Incorrect OTP.'], 422);
        }

        $newValue = $sessionData['value'];

        // Save new value
        if ($type === 'email') {
            $user->email = $newValue;
        } else {
            $user->phone = $newValue;
        }

        $user->save();
        
        // Clear the cache so it can't be reused
        Cache::forget($cacheKey);

        Log::info('✅ [API] Profile Updated via OTP', ['type' => $type, 'user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($type) . ' updated successfully!',
            'user' => $user
        ]);
    }

    private function sendEmailOtp($email, $otp, $user)
    {
        try {
            Log::info('📧 [API] Attempting Email Send', ['to' => $email]);

            Mail::send([], [], function ($message) use ($email, $otp, $user) {
                $message->to($email)
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->subject('Verification OTP')
                    ->html("<h2>Hello " . ($user->name ?? 'User') . ",</h2><p>Your OTP is: <b>$otp</b></p>");
            });

            return response()->json(['success' => true, 'message' => 'OTP sent to email.']);
        } catch (\Exception $e) {
            Log::error('❌ [API] Email Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Email error: ' . $e->getMessage()], 500);
        }
    }

    private function sendSmsOtp($phone, $otp)
    {
        Log::info('💬 [API] Attempting SMS Send', ['phone' => $phone]);
        
        $name = 'User';
        // RESTORED: Your specific message template from old code
          $message = "Dear {$name},\n"
        . "Your OTP is {$otp}.\n"
        . "Login Link: https://therapidinvestors.com/Admin/login\n"
        . "This OTP is valid for 10 minutes.\n"
        . "Do not share this OTP with anyone.\n"
        . "If you need any help or face any issues, please feel free to reach out.\n"
        . "Best regards, Shubham Sharma\n"
        . "Properietor Of The Rapid Investors\n"
        . "Contact -8269981108.";

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

        if ($response->successful()) {
            Log::info('✅ [API] SMS Sent Successfully');
            return response()->json(['success' => true, 'message' => 'OTP sent to mobile successfully.']);
        }

        Log::error('❌ [API] SMS Gateway Error', ['status' => $response->status(), 'body' => $response->body()]);
        return response()->json(['success' => false, 'message' => 'Failed to send SMS.'], 500);
    }

    /**
     * 1. Password Reset ke liye OTP bhejna
     */
    public function sendPasswordOtp(Request $request)
    {
        $user = Auth::user();

        // Agar mobile app se hai aur session issue kare toh Cache use karein
        $otp = random_int(100000, 999999);

        // OTP ko 10 min ke liye store karein
        Cache::put("password_otp_{$user->id}", $otp, now()->addMinutes(10));

        Log::info("🔑 Password Reset OTP for User {$user->id}: {$otp}");

        // Aapka existing SMS logic call karein
        return $this->sendSmsOtp($user->phone, $otp);
    }

    /**
     * 2. OTP Verify karna
     */
    public function verifyPasswordOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        $user = Auth::user();
        $storedOtp = Cache::get("password_otp_{$user->id}");

        if (!$storedOtp || $request->otp != $storedOtp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'OTP verified. Now you can change password.']);
    }

    /**
     * 3. Final Password Update
     */
    public function updatePasswordFinal(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8',
            'otp'      => 'required' // Security ke liye dobara check karenge
        ]);

        $user = Auth::user();
        $storedOtp = Cache::get("password_otp_{$user->id}");

        if (!$storedOtp || $request->otp != $storedOtp) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please verify OTP again.'], 422);
        }

        // Password update
        $user->password = Hash::make($request->password);
        $user->save();

        // OTP clear karein
        Cache::forget("password_otp_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!'
        ]);
    }
}
