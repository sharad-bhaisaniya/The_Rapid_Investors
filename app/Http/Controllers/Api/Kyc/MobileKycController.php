<?php

namespace App\Http\Controllers\Api\Kyc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\KycVerification;

class MobileKycController extends Controller
{
    /**
     * ==================================================
     * 📱 START KYC (MOBILE)
     * ==================================================
     * - Same logic as testDirectRedirect()
     * - Returns Digio redirect URL for WebView
     */
    public function start(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'UNAUTHENTICATED'
            ], 401);
        }

        $mobile = $request->phone ?? $user->phone;
        $name   = $request->name  ?? $user->name;

        // 1️⃣ Get last KYC
        $lastKyc = KycVerification::where('user_id', $user->id)
            ->latest()
            ->first();

        // 🚫 Block active KYC
        if ($lastKyc && in_array($lastKyc->status, [
            'pending',
            'approval_pending',
            'approved'
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'KYC already in progress or completed'
            ], 422);
        }

        // 🧹 Cleanup failed KYC
        if ($lastKyc && in_array($lastKyc->status, [
            'rejected',
            'failed',
            'expired',
            'initiated'
        ])) {
            KycVerification::where('user_id', $user->id)
                ->whereIn('status', ['rejected', 'failed', 'expired','initiated'])
                ->delete();
        }

        try {
            // 2️⃣ Create Digio KYC
            $referenceId   = 'KYC_' . time();
            $transactionId = $referenceId;

            $payload = [
                "template_name"       => env('DIGIO_WORKFLOW_NAME'),
                "customer_identifier" => $mobile,
                "customer_name"       => $name,
                "reference_id"        => $referenceId,
                "transaction_id"      => $transactionId,
                "notify_customer"     => false,
                "expire_in_days"      => 1,
                "message"             => "KYC Verification"
            ];

            $apiUrl = rtrim(env('DIGIO_API_BASE_URL'), '/')
                . '/client/kyc/v2/request/with_template';

            Log::info('📱 MOBILE_DIGIO_KYC_REQUEST', [
                'user_id' => $user->id,
                'payload' => $payload
            ]);

            $response = Http::withBasicAuth(
                    env('DIGIO_CLIENT_ID'),
                    env('DIGIO_CLIENT_SECRET')
                )
                ->timeout(30)
                ->post($apiUrl, $payload);

            if (!$response->successful()) {
                Log::error('❌ DIGIO_API_ERROR', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'error'   => 'DIGIO_API_ERROR',
                    'details' => $response->json()
                ], 500);
            }

            $data = $response->json();
            $documentId = $data['id'] ?? null;

            if (!$documentId || str_starts_with($documentId, 'DGO')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'INVALID_DIGIO_DOCUMENT'
                ], 422);
            }

            // 3️⃣ Save KYC record
            KycVerification::create([
                'user_id'           => $user->id,
                'digio_document_id' => $documentId,
                'customer_name'     => $name,
                'customer_mobile'   => $mobile,
                'customer_email'    => $user->email,
                'reference_id'      => $referenceId,
                'transaction_id'    => $transactionId,
                'status'            => 'initiated',
                'kyc_details'       => json_encode(['type' => 'mobile-kyc']),
                'raw_response'      => json_encode($data),
            ]);

            // 4️⃣ Build Redirect URL
            $redirectBase = str_contains(env('DIGIO_API_BASE_URL'), 'ext.digio')
                ? 'https://ext.digio.in/#/gateway/login/'
                : 'https://app.digio.in/#/gateway/login/';

            $redirectUrl = $redirectBase
                . $documentId . '/'
                . time() . '/'
                . $mobile;

            return response()->json([
                'success'      => true,
                'document_id'  => $documentId,
                'kyc_url'      => $redirectUrl
            ]);

        } catch (\Exception $e) {
            Log::error('❌ MOBILE_KYC_EXCEPTION', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * ==================================================
     * 📊 CHECK KYC STATUS (MOBILE POLLING)
     * ==================================================
     */
    public function status(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'UNAUTHENTICATED'
            ], 401);
        }

        $kyc = KycVerification::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$kyc) {
            return response()->json([
                'success'    => true,
                'kyc_status' => 'none'
            ]);
        }

        try {
            $apiUrl = env('DIGIO_API_BASE_URL')
                . '/client/kyc/v2/' . $kyc->digio_document_id . '/response';

            $response = Http::withBasicAuth(
                env('DIGIO_CLIENT_ID'),
                env('DIGIO_CLIENT_SECRET')
            )->timeout(30)->post($apiUrl);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'DIGIO_API_ERROR'
                ], 500);
            }

            $digioData = $response->json();
            $status = strtolower($digioData['status'] ?? 'pending');

            $kyc->update([
                'status' => $status,
                'kyc_completed_at' =>
                    in_array($status, ['approved', 'completed', 'success'])
                        ? now()
                        : null,
                'raw_response' => $digioData
            ]);

            return response()->json([
                'success'      => true,
                'kyc_status'   => $kyc->status,
                'document_id'  => $kyc->digio_document_id,
                'digio_data'   => $digioData,
                'completed_at' => $kyc->kyc_completed_at
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'CONNECTION_ERROR'
            ], 500);
        }
    }
}
