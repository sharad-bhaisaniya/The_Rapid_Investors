<?php

namespace App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\KycVerification;
use App\Models\UserSubscription;
use App\Models\Invoice;
use App\Models\UserAgreement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class UserAgreementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Latest approved KYC
        $kyc = KycVerification::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->latest()
                    ->first();

                     if (!$kyc) {
            return redirect()->route('user.settings.profile')
                ->with('error', 'Please complete KYC first');
        }

            // Latest PAID subscription only
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('payment_status', 'paid')
                ->with(['plan', 'duration', 'invoices'])
                ->latest()
                ->first();

            if (!$subscription) {
                return redirect()->route('subscription.index')
                    ->with('error','Please purchase a plan first');
            }

            // get latest invoice from collection
            $invoice = $subscription->invoices()->latest()->first();


              /* -----------------------------
     * 4️⃣ AGREEMENT CHECK (KEY LOGIC)
     * ----------------------------- */
    $agreement = UserAgreement::where('invoice_id', $invoice->id)->first();

    if ($agreement) {
        // ✅ Agreement already exists → show latest agreement page
        return redirect()->route('agreement.latest');
    }

        return view('UserDashboard.templeteKyc.aggrement', compact(
            'user',
            'kyc',
            'subscription',
            'invoice'
        ));
    }




        public function downloadAgreement()
        {
        $user = auth()->user();

                // Latest approved KYC
                $kyc = KycVerification::where('user_id', $user->id)
                            ->where('status', 'approved')
                            ->latest()
                            ->first();

                // Latest PAID subscription only
            $subscription = UserSubscription::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->with(['plan', 'duration', 'invoices'])
            ->latest()
            ->first();

            if (!$subscription) {
                return redirect()->route('subscription.index')
                    ->with('error','Please purchase a plan first');
            }

            // get latest invoice from collection
            $invoice = $subscription->invoices()->latest()->first();
            $signedAt = now()->format('d M Y, h:i A');

            $pdf = Pdf::loadView('UserDashboard.templeteKyc.full-agreement', compact(
                'user',
                'kyc',
                'subscription',
                'invoice',
                'signedAt'
            ))->setPaper('a4');

            // return $pdf->download('Research-Analyst-Agreement.pdf');
            return $pdf->stream('Research-Analyst-Agreement.pdf');
        }




    // public function generateAgreement()
    // {
    //     $user = auth()->user();

    //     $kyc = KycVerification::where('user_id', $user->id)
    //         ->where('status', 'approved')
    //         ->latest('id')
    //         ->firstOrFail();

    //     $subscription = UserSubscription::where('user_id', $user->id)
    //         ->where('payment_status', 'paid')
    //         ->with(['plan', 'duration', 'invoices'])
    //         ->latest('id')
    //         ->firstOrFail();

    //     $invoice = $subscription->invoices()->latest('id')->firstOrFail();

    //     // 🔒 Invoice already mapped?
    //     $existingAgreement = UserAgreement::where('invoice_id', $invoice->id)->first();
    //     if ($existingAgreement) {
    //         return redirect()
    //             ->route('agreement.success')
    //             ->with('info', 'Agreement already exists for this invoice.');
    //     }

    //     // ✅ USER BASED AGREEMENT NUMBER
    //     $agreementNumber = $this->generateNextAgreementNumber($user->id);
    //     $signedAt = now();

    //     $agreement = UserAgreement::create([
    //         'user_id' => $user->id,
    //         'subscription_id' => $subscription->id,
    //         'invoice_id' => $invoice->id,
    //         'invoice_number' => $invoice->invoice_number,
    //         'agreement_number' => $agreementNumber,
    //         'signed_at' => $signedAt,
    //         'is_signed' => true,
    //         'status' => 'signed',

    //         'user_snapshot' => $user->only(['id','name','email','mobile']),
    //         'kyc_snapshot' => $kyc->kyc_details,
    //         'subscription_snapshot' => [
    //             'plan' => $subscription->plan,
    //             'duration' => $subscription->duration,
    //         ],
    //         'invoice_snapshot' => $invoice->toArray(),
    //     ]);

    //     // 📄 Generate PDF
    //     $pdf = Pdf::loadView(
    //         'UserDashboard.templeteKyc.full-agreement',
    //         compact('user','kyc','subscription','invoice','signedAt','agreement')
    //     )->setPaper('a4');

    //     // 💾 Store PDF
    //     $agreement
    //         ->addMediaFromString($pdf->output())
    //         ->usingFileName("Agreement-{$agreementNumber}.pdf")
    //         ->toMediaCollection('agreement_pdf');

    //     return redirect()
    //         ->route('agreement.success')
    //         ->with('success', 'Agreement signed and saved successfully');
    // }

    public function generateAgreement()
{
    Log::info('Agreement generation started');

    $user = auth()->user();
    Log::info('Authenticated user', ['user_id' => $user?->id]);

    try {

        // 🔹 KYC
        $kyc = KycVerification::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest('id')
            ->firstOrFail();

        Log::info('KYC found', ['kyc_id' => $kyc->id]);

        // 🔹 Subscription
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->with(['plan', 'duration', 'invoices'])
            ->latest('id')
            ->firstOrFail();

        Log::info('Subscription found', ['subscription_id' => $subscription->id]);

        // 🔹 Invoice
        $invoice = $subscription->invoices()->latest('id')->firstOrFail();
        Log::info('Invoice found', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number
        ]);

        // 🔒 Check duplicate agreement
        $existingAgreement = UserAgreement::where('invoice_id', $invoice->id)->first();
        if ($existingAgreement) {
            Log::warning('Agreement already exists for invoice', [
                'agreement_id' => $existingAgreement->id,
                'invoice_id' => $invoice->id
            ]);

            return redirect()
                ->route('agreement.success')
                ->with('info', 'Agreement already exists for this invoice.');
        }

        // 🔢 Agreement number
        $agreementNumber = $this->generateNextAgreementNumber($user->id);
        Log::info('Generated agreement number', [
            'agreement_number' => $agreementNumber
        ]);

        $signedAt = now();

        // 📝 Create agreement
        $agreement = UserAgreement::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'agreement_number' => $agreementNumber,
            'signed_at' => $signedAt,
            'is_signed' => true,
            'status' => 'signed',

            'user_snapshot' => $user->only(['id','name','email','mobile']),
            'kyc_snapshot' => $kyc->kyc_details,
            'subscription_snapshot' => [
                'plan' => $subscription->plan,
                'duration' => $subscription->duration,
            ],
            'invoice_snapshot' => $invoice->toArray(),
        ]);

        Log::info('Agreement DB record created', [
            'agreement_id' => $agreement->id
        ]);

        // 📄 Generate PDF
        $pdf = Pdf::loadView(
            'UserDashboard.templeteKyc.full-agreement',
            compact('user','kyc','subscription','invoice','signedAt','agreement')
        )->setPaper('a4');

        Log::info('Agreement PDF generated');

        // 💾 Store PDF via Spatie
        $agreement
            ->addMediaFromString($pdf->output())
            ->usingFileName("Agreement-{$agreementNumber}.pdf")
            ->toMediaCollection('agreement_pdf');

        Log::info('Agreement PDF stored via Spatie', [
            'media_count' => $agreement->getMedia('agreement_pdf')->count()
        ]);

        Log::info('Agreement generation completed successfully');

        return response()->json([
    'status' => 'ok',
    'redirect_url' => route('agreement.latest'), // Yahan sahi route name likhein
    'message' => 'Agreement signed successfully'
    ]);

    } catch (\Throwable $e) {

        Log::error('Agreement generation FAILED', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => substr($e->getTraceAsString(), 0, 2000),
        ]);

        return redirect()
            ->back()
            ->with('error', 'Something went wrong while generating agreement.');
    }
}

        private function generateNextAgreementNumber(int $userId): string
    {
        $lastAgreement = UserAgreement::where('user_id', $userId)
            ->latest('id')
            ->first();

        $year = now()->year;

        if (!$lastAgreement) {
            return "AGR-{$year}-0001";
        }

        // AGR-2025-0007 → 0007
        $parts = explode('-', $lastAgreement->agreement_number);
        $lastNumber = intval(end($parts));

        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return "AGR-{$year}-{$nextNumber}";
    }

    public function viewPdf(UserAgreement $agreement)
{
    abort_unless($agreement->user_id === auth()->id(), 403);

    return response()->file(
        $agreement->getFirstMediaPath('agreement_pdf')
    );
}





public function latest()
{
    $user = auth()->user();

    // 🔹 Fetch ALL signed agreements (latest first)
    $agreements = UserAgreement::where('user_id', $user->id)
        ->where('status', 'signed')
        ->orderByDesc('signed_at')
        ->get();

    if ($agreements->isEmpty()) {
        return redirect()
            ->route('user.dashboard')
            ->with('info', 'No agreement found yet.');
    }

    return view(
        'UserDashboard.agreements.latest',
        compact('agreements')
    );
}

}
