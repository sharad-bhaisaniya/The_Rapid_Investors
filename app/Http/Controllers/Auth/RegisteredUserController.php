<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class RegisteredUserController extends Controller
{


    public function create()
    {
        return view('auth.register');
    }

     // Step 1: Details validate karke session mein save karna
    public function handleDetails(Request $request)
    {
        // 1. Validation - Ensure annual_income is present in the request
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'annual_income' => 'required|string', // Check if this matches your HTML <select name="...">
            'password'      => 'required|min:8|confirmed',
        ]);

        // 2. Debugging - Let's see what the request actually has before saving
        Log::info('Registration Step 1 Data:', $request->all());

        // 3. Store in Session
        $data = [
            'name'            => $request->name,
            'email'           => $request->email,
            'annual_income'   => $request->annual_income, 
            'password'        => $request->password,
            'is_age_verified' => true, // Manually setting this to true
        ];

        Session::put('reg_data', $data);

        // 4. Verification - Double check session was written
        Log::info('Session Saved Data:', Session::get('reg_data'));

        // Redirect to phone step
        return redirect()->route('register.phone');
    }

    // Step 2: Mobile Number form dikhana
    public function showPhoneForm()
    {
        // CONDITION: Agar session mein 'reg_data' key nahi milti (yani user ne step 1 skip kiya)
        // ya phir agar session khali hai, toh use wapas register page pe bhej do.
        if (!Session::has('reg_data') || empty(Session::get('reg_data'))) {
            return redirect()->route('register')->withErrors(['msg' => 'Please fill your registration details first.']);
        }

        // Agar details hain, tabhi yeh view load hoga
        return view('auth.register-phone');
    }

    // Step 3: Mobile number lekar OTP bhejna
    public function sendRegistrationOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10|unique:users,phone',
        ]);

        $otp = rand(100000, 999999);
        
        Session::put('reg_otp', $otp);
        Session::put('reg_phone', $request->phone);
        $sessionOtp = Session::get('reg_otp');
        $userData = Session::get('reg_data');

        $this->sendOtpSms($userData['name'], $otp, $request->phone);

        // FIX 1: View ka sahi naam use karein (otp_verify_register)
        return view('auth.otp_verify_register', [
            'phone' => $request->phone,
            'route' => route('register.verify_otp') 
        ]);
    }

    // Step 4: Final Database Entry
    public function verifyAndRegister(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $sessionOtp = Session::get('reg_otp');
        $userData = Session::get('reg_data');
        $phone = Session::get('reg_phone'); // FIX 2: Phone session se lein

        if ($request->otp == $sessionOtp) {
            // OTP match ho gaya, ab user create karo
            $user = User::create([
                'name'     => $userData['name'],
                'email'    => $userData['email'],
                'annual_income'   => $userData['annual_income'], 
                'is_age_verified' => $userData['is_age_verified'],
                'phone'    => $phone, // Correct phone number                
                'password' => Hash::make($userData['password']), 
            ]);

            if (!Role::where('name', 'customer')->exists()) {
                Role::create(['name' => 'customer']);
            }
            $user->assignRole('customer');

            Auth::login($user);

            // Session saaf karo
            // Session::forget(['reg_otp', 'reg_data', 'reg_phone']);
            Session::forget(['reg_data', 'reg_otp', 'reg_phone']);

            return redirect('/dashboard')->with('success', 'Registration Successful!');
        }

        return back()->withErrors(['otp' => 'Invalid OTP, please try again.']);
    }

    private function sendOtpSms( $name, $otp,$phone)
    {
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
        'mobile'    => '+' . config('services.sms.country_code') . $phone,
        'message'   => $message,
        'senderid'  => config('services.sms.sender'),
        'accusage'  => 1,
        'entityid'  => config('services.sms.entity_id'),
        'tempid'    => config('services.sms.template_id'),
    ]);

    \Log::info('SMS API Response: ' . $response->body());

    return $response->successful();
}
}
