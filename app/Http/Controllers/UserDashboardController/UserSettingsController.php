<?php

namespace App\Http\Controllers\UserDashboardController;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use App\Models\ServicePlan;
use App\Models\KycVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class UserSettingsController extends Controller
{




// public function profile()
// {
//     $user = auth()->user();

//     /** ✅ Get approved KYC data via private function */
//     $approvedKycData = $this->getApprovedKycProfileData($user->id);

//     /** Existing profile data */
//     $data = $this->getProfileData($user);

//     // --- ADDED SUSPENSION CHECK ---
//     // Check the latest subscription status
//     $lastSub = \App\Models\UserSubscription::where('user_id', $user->id)
//                 ->latest()
//                 ->first();
                
//     $isSuspended = $lastSub && $lastSub->status === 'suspended';




//     return view('UserDashboard.settings.profile', array_merge(
//         ['user' => $user],
//         $data,
//         $approvedKycData,
//         ['isSuspended' => $isSuspended],
//         ['lastSub' => $lastSub],
        
//     ));
// }


public function profile()
{
    $user = auth()->user();

    /** ✅ Get approved KYC data */
    $approvedKycData = $this->getApprovedKycProfileData($user->id);

    /** Existing profile data */
    $data = $this->getProfileData($user);

    // 🔹 Get last subscription (latest by start or id)
    $lastSub = \App\Models\UserSubscription::where('user_id', $user->id)
        ->latest()
        ->first();

    // 🔹 Status flags
    $isSuspended = $lastSub && $lastSub->status === 'suspended';
    $isCancelled = $lastSub && $lastSub->status === 'cancelled';

    // 🔹 Refund check (ONLY if cancelled)
    $refund = null;
    $isRefunded = false;

    if ($lastSub) {
        $refund = \App\Models\RefundRequest::where('user_id', $user->id)
            ->where('user_subscription_id', $lastSub->id)
            ->where('status', 'refunded')
            ->latest()
            ->first();

        $isRefunded = (bool) $refund;
    }

    return view(
        'UserDashboard.settings.profile',
        array_merge(
            ['user' => $user],
            $data,
            $approvedKycData,
            [
                'lastSub'     => $lastSub,
                'isSuspended' => $isSuspended,
                'isCancelled' => $isCancelled,
                'isRefunded'  => $isRefunded,
                'refund'      => $refund, // contains refund_amount, refunded_at, reason
            ]
        )
    );
}

private function getApprovedKycProfileData(int $userId): array
{
    $kyc = \App\Models\KycVerification::where('user_id', $userId)
        ->where('status', 'approved')
        ->latest()
        ->first();

            // 🔥 Sync user profile from KYC
            if($kyc){

                $this->syncUserProfileFromKyc($kyc->user_id);
            }

    if (!$kyc) {
        return [
            'approvedKyc' => null,
            'aadhaar'     => null,
            'signature'   => null,
            'selfie'      => null,
        ];
    }

    /** Decode JSON safely (model untouched) */
    $details = is_array($kyc->kyc_details)
        ? $kyc->kyc_details
        : json_decode($kyc->kyc_details, true);

    /** 🔁 Ensure media exists (self-healing) */
    $signature = $this->ensureKycMediaExists(
        $kyc,
        'signature',
        $details['signature_local_path'] ?? null
    );

    $selfie = $this->ensureKycMediaExists(
        $kyc,
        'selfie',
        $details['selfie_local_path'] ?? null
    );

    return [
        'approvedKyc' => $kyc,
        'aadhaar'     => $details['aadhaar'] ?? null,
        'signature'   => $signature,
        'selfie'      => $selfie,
    ];
}

    private function ensureKycMediaExists(\App\Models\KycVerification $kyc,string $type,?string $localPath)
{
    $collection = "kyc_{$type}";

    /** If already attached → just return */
    $existing = $kyc->getFirstMedia($collection);
    if ($existing) {
        return $existing;
    }

    /** No stored path → nothing to attach */
    if (!$localPath || !file_exists($localPath)) {
        return null;
    }

    try {
        /** Attach existing file into Spatie */
        $media = $kyc->addMedia($localPath)
            ->preservingOriginal()
            ->usingFileName(basename($localPath))
            ->usingName('KYC ' . ucfirst($type))
            ->toMediaCollection($collection);

        return $media;

    } catch (\Throwable $e) {
        \Log::error('Failed to reattach KYC media', [
            'type' => $type,
            'path' => $localPath,
            'error' => $e->getMessage(),
        ]);
    }

    return null;
}




private function syncUserProfileFromKyc(int $userId): void
{
    Log::info('KYC → User sync started', ['user_id' => $userId]);

    /** Fetch user */
    $user = User::find($userId);
    if (!$user) {
        Log::error('User not found for KYC sync', ['user_id' => $userId]);
        return;
    }

    /** 🚫 Already synced once → STOP */
    if ($user->is_kyc_synced == 1) {
        Log::info('KYC already synced once, skipping', ['user_id' => $userId]);
        return;
    }

    /** Fetch approved KYC */
    $kyc = KycVerification::where('user_id', $userId)
        ->where('status', 'approved')
        ->latest()
        ->first();

    if (!$kyc || empty($kyc->raw_response)) {
        Log::warning('Approved KYC or raw_response missing', ['user_id' => $userId]);
        return;
    }

    /** Decode RAW response */
    $raw = is_array($kyc->raw_response)
        ? $kyc->raw_response
        : json_decode($kyc->raw_response, true);

    if (!$raw || empty($raw['actions'])) {
        Log::error('Invalid raw_response structure', ['user_id' => $userId]);
        return;
    }

    /** Find Digilocker block */
    $digilocker = collect($raw['actions'])->firstWhere('type', 'digilocker');
    if (!$digilocker || empty($digilocker['details'])) {
        Log::warning('Digilocker details not found', ['user_id' => $userId]);
        return;
    }

    /** Aadhaar data */
    $details  = $digilocker['details'];
    $aadhaar  = $details['aadhaar'] ?? [];
    $address  = $aadhaar['current_address_details'] ?? [];

    /** ✅ Father name (ADDED) */
    $fatherName = $aadhaar['father_name'] ?? null;

    /** Gender mapping */
    $genderRaw = strtoupper($aadhaar['gender'] ?? '');
    $gender = match ($genderRaw) {
        'M' => 'Male',
        'F' => 'Female',
        default => null,
    };

    Log::info('User BEFORE update', [
        'user_id' => $userId,
        'is_kyc_synced' => $user->is_kyc_synced,
    ]);

    DB::beginTransaction();

    try {
        /** Name */
        if (!empty($aadhaar['name'])) {
            $user->name = $aadhaar['name'];
        }

        /** ✅ Father Name (ADDED) */
        if (!empty($fatherName)) {
            $user->father_name = $fatherName;
        }

        /** DOB */
        if (!empty($aadhaar['dob'])) {
            $user->dob = Carbon::createFromFormat('d/m/Y', $aadhaar['dob'])->format('Y-m-d');
        }

        /** Gender */
        if ($gender) {
            $user->gender = $gender;
        }

        /** Address */
        if (!empty($address)) {
            $user->address = $address['address'] ?? $user->address;
            $user->city    = $address['district_or_city'] ?? $user->city;
            $user->state   = $address['state'] ?? $user->state;
            $user->pincode = $address['pincode'] ?? $user->pincode;
            $user->country = 'India';
        }

        /** ✅ Mark as synced (IMPORTANT) */
        $user->is_kyc_synced = 1;

        $user->save();
        DB::commit();

        Log::info('KYC → User sync completed & locked', [
            'user_id' => $userId,
            'is_kyc_synced' => 1,
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('KYC → User sync failed', [
            'user_id' => $userId,
            'error' => $e->getMessage(),
        ]);
    }
}



public function dashboard()
{
    $user = auth()->user();

    $data = $this->getProfileData($user);

    $activeSubscription = \App\Models\UserSubscription::where('user_id', $user->id ?? null)
        ->where('status', 'active')
        ->where('end_date', '>', now())
        ->first();

    // ✅ Only Intraday tips
    $highlights = \App\Models\Tip::with(['category', 'planAccess'])
        ->whereIn('status', ['active', 'Active'])        
        ->latest()
        ->take(8)
        ->get();

    return view('UserDashboard.userdashboard', array_merge(
        [
            'user' => $user,
            'highlights' => $highlights,
            'activeSubscription' => $activeSubscription
        ],
        $data
    ));
}


private function getProfileData($user)
{
    $activeSubscription = UserSubscription::with(['plan', 'duration'])
        ->where('user_id', $user->id)
        ->where('status', 'active')
        ->first();

    $currentPlan = $activeSubscription?->plan?->name ?? 'No Active Plan';
    $validityTill = $activeSubscription?->end_date?->format('d M Y') ?? '-';
    $daysRemaining = $activeSubscription?->end_date
        ? max(0, now()->diffInDays($activeSubscription->end_date, false))
        : null;

    $latestKyc = KycVerification::where('user_id', $user->id)->latest()->first();
    $kycStatus = $latestKyc->status ?? 'none';
    $isKycCompleted = in_array($kycStatus, ['approved', 'completed', 'success']);

    $plans = ServicePlan::with('durations.features')
        ->where('status', 1)
        ->orderBy('sort_order')
        ->get();

    return compact(
        'activeSubscription',
        'currentPlan',
        'validityTill',
        'daysRemaining',
        'plans',
        'kycStatus',
        'isKycCompleted'
    );
}


    public function edit() {
        $user = auth()->user();
        return view('UserDashboard.settings.edit-profile', compact('user'));
    }

    public function updateGeneralProfile(Request $request)
    {
        $user = auth()->user();
        
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'dob'     => 'nullable|date', // Added Date of Birth
            'address' => 'nullable|string|max:500', // Added Address
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ]);

        $user->update($data);

        if ($request->hasFile('profile_image')) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }

        return back()->with('success', 'Profile details updated successfully!');
    }
   
    public function sendOtp(Request $request)
    {
        Log::info('OTP Request Received', [
            'user_id' => auth()->id(),
            'payload' => $request->all()
        ]);

        $request->validate([
            'type'  => 'required|in:email,phone',
            'value' => 'required',
        ]);

        $otp   = random_int(100000, 999999);
        $type  = $request->type;
        $value = $request->value;
        $user  = auth()->user();

        Log::info('OTP Generated', [
            'type'  => $type,
            'value' => $value,
            'otp'   => $otp, // ❗ remove in production
        ]);

        // ✅ Store OTP in session
        Session::put("otp_verify_{$type}", [
            'otp'        => $otp,
            'value'      => $value,
            'expires_at' => now()->addMinutes(10),
        ]);

        Log::info('OTP Stored in Session', [
            'session_key' => "otp_verify_{$type}",
            'session_data' => Session::get("otp_verify_{$type}")
        ]);

        // ================= EMAIL OTP =================
        if ($type === 'email') {
            try {

                Log::info('Attempting to send OTP email', [
                    'to' => $value,
                    'from' => env('MAIL_FROM_ADDRESS')
                ]);

                Mail::send([], [], function ($message) use ($value, $otp, $user) {
                    $message->to($value)
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                        ->subject('Email Verification OTP')
                        ->html("
                            <div style='font-family:Arial;padding:20px;border:1px solid #eee;border-radius:10px;'>
                                <h2>Hello " . ($user->name ?? 'User') . ",</h2>
                                <p>You are requesting to update your email address.</p>
                                <p>Your Verification OTP is:</p>
                                <h1 style='letter-spacing:4px;color:#2563eb;'>{$otp}</h1>
                                <p>This OTP is valid for 10 minutes.</p>
                            </div>
                        ");
                });

                Log::info('OTP Email Sent Successfully', [
                    'email' => $value
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent to your email successfully.'
                ]);

            } catch (\Exception $e) {

                Log::error('OTP Email Failed', [
                    'email' => $value,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Mail error: ' . $e->getMessage()
                ], 500);
            }
        }

        // ================= PHONE OTP =================
        Log::info('Attempting to send OTP SMS', [
            'phone' => $value
        ]);

        if ($this->sendOtpSms($value, $otp)) {

            Log::info('OTP SMS Sent Successfully', [
                'phone' => $value
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your mobile successfully.'
            ]);
        }

        Log::error('OTP SMS Failed', [
            'phone' => $value
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to send SMS. Please try again.'
        ], 500);
    }  

        public function verifyAndUpdate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:email,phone',
            'otp' => 'required|numeric'
        ]);

        $type = $request->type;
        $sessionData = Session::get("otp_verify_{$type}");

        if (!$sessionData || now()->gt($sessionData['expires_at'])) {
            return response()->json(['success' => false, 'message' => 'OTP expired. Please try again.'], 422);
        }

        if ($request->otp != $sessionData['otp']) {
            return response()->json(['success' => false, 'message' => 'The OTP you entered is incorrect.'], 422);
        }

        $user = auth()->user();
        $newValue = $sessionData['value'];

        // --- FIX STARTS HERE: Check for duplicate entry before saving ---
        $exists = \App\Models\User::where($type, $newValue)
                    ->where('id', '!=', $user->id)
                    ->exists();

        if ($exists) {
            return response()->json([
                'success' => false, 
                'message' => "This " . $type . " is already registered to another account."
            ], 422);
        }
        // --- FIX ENDS HERE ---

        if ($type === 'email') {
            $user->email = $newValue;
        } else {
            $user->phone = $newValue;
        }
        
        $user->save();

        Session::forget("otp_verify_{$type}");

        return response()->json(['success' => true, 'message' => ucfirst($type) . ' updated successfully!']);
    }

    private function sendOtpSms($phone, $otp)
    {
        // Using your specific message template
        $message = "Hello, Your https://bharatstockmarketresearch.com registration OTP is {$otp}. Use this code to complete your sign-up process. Regards, Bharat Stock Market Research.";

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

        public function kycDetails()
    {
        $user = auth()->user();

        $kycData = $this->getApprovedKycFullDetails($user->id);

        if (!$kycData['approvedKyc']) {
            abort(404, 'Approved KYC not found');
        }

        return view('UserDashboard.kyc_details.details', $kycData);
    }

    private function getApprovedKycFullDetails(int $userId): array
    {
        $kyc = \App\Models\KycVerification::where('user_id', $userId)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (!$kyc) {
            return ['approvedKyc' => null];
        }

        /* -------------------------------
        1️⃣ Load stored column data
        -------------------------------- */
        $details = is_array($kyc->kyc_details)
            ? $kyc->kyc_details
            : json_decode($kyc->kyc_details, true);

        $aadhaar = $details['aadhaar'] ?? [];
        $pan     = $details['pan'] ?? [];

        /* -----------------------------------------
        2️⃣ Fallback: read from raw_response column
        ------------------------------------------ */
        if (empty($pan)) {
            $raw = is_array($kyc->raw_response)
                ? $kyc->raw_response
                : json_decode($kyc->raw_response, true);

            foreach ($raw['actions'] ?? [] as $action) {
                if (
                    ($action['type'] ?? '') === 'digilocker' &&
                    isset($action['details']['pan'])
                ) {
                    $pan = $action['details']['pan'];
                    break;
                }
            }
        }

        /* -------------------------------
        3️⃣ Normalized response
        -------------------------------- */
        return [
            'approvedKyc' => $kyc,

            /* Overview */
            'kyc_status'     => 'Verified',
            'last_refreshed' => $aadhaar['last_refresh_date'] ?? null,

            /* Personal */
            'full_name'   => $aadhaar['name'] ?? ($pan['name'] ?? null),
            'father_name' => $aadhaar['father_name'] ?? null,
            'dob'         => $aadhaar['dob'] ?? ($pan['dob'] ?? null),
            'gender'      => $aadhaar['gender'] ?? ($pan['gender'] ?? null),

            /* Aadhaar */
            'aadhaar_number' => $aadhaar['id_number'] ?? null,
            'document_type'  => $aadhaar['document_type'] ?? 'aadhaar',

            /* ✅ PAN (FIXED KEYS) */
            'pan_number' => $pan['id_number'] ?? null,
            'pan_name'   => $pan['name'] ?? null,
            'pan_dob'    => $pan['dob'] ?? null,

            /* Address */
            'current_address'   => $aadhaar['current_address_details'] ?? [],
            'permanent_address' => $aadhaar['permanent_address_details'] ?? [],

            /* Media */
            'selfie'    => $kyc->getFirstMedia('kyc_selfie'),
            'signature' => $kyc->getFirstMedia('kyc_signature'),
        ];
    }



}