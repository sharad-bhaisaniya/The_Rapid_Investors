



@php
    $isPdf = isset($pdfMode) && $pdfMode === true;

    // already an array – DO NOT json_decode
    $kycDetails = $kyc->kyc_details;

     $signatureMedia = $kyc->getFirstMedia('kyc_signature');
    
    $signaturePath = $signatureMedia ? $signatureMedia->getPath() : null;


    $selfieMedia = $kyc->getFirstMedia('kyc_selfie');

    $aadhaar = $kycDetails['aadhaar'] ?? null;
    $aadhaarNumber = $aadhaar['id_number'] ?? null;
@endphp


{{-- ===================================================== --}}
{{-- ===================== PDF MODE ====================== --}}
{{-- ===================================================== --}}
@if ($isPdf)

    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <title>Final Review & Digital Consent</title>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
                line-height: 1.6;
            }

            h2,
            h3 {
                margin-bottom: 6px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 14px;
            }

            td {
                padding: 6px;
                vertical-align: top;
            }

            .label {
                font-weight: bold;
                width: 35%;
            }

            .box {
                border: 1px solid #ccc;
                padding: 10px;
                margin-bottom: 12px;
            }

            .signature {
                margin-top: 12px;
            }

            .consent {
                margin-top: 16px;
                font-weight: bold;
            }
        </style>
    </head>

    <body>

        <h2 style="text-align:center;">Final Review & Digital Consent</h2>
        <p style="text-align:center; font-size:11px;">
            Summary of subscription, identity verification, and final declaration
        </p>

        <hr>

        <div class="box">
            <h3>Subscription Summary</h3>
            <table>
                <tr>
                    <td class="label">Client Name</td>
                    <td>{{ auth()->user()->name }}</td>
                </tr>
                <tr>
                    <td class="label">Plan</td>
                    <td>{{ $subscription->plan->name ?? 'Research Plan' }}</td>
                </tr>
                <tr>
                    <td class="label">Invoice Number</td>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td class="label">Amount Paid</td>
                    <td>₹ {{ number_format($subscription->amount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Filing Date</td>
                    <td>{{ now()->format('d M, Y') }}</td>
                </tr>
            </table>
        </div>

        <div class="box">
            <h3>Identity Verification</h3>
            <table>
                <tr>
                    <td class="label">PAN</td>
                    <td>{{ $kyc->pan_number ?? 'Verified' }}</td>
                </tr>
                <tr>
                    <td class="label">Aadhaar</td>
                    <td>**** **** {{ $aadhaarNumber ? substr($aadhaarNumber, -4) : 'XXXX' }}</td>
                </tr>
                <tr>
                    <td class="label">KYC Status</td>
                    <td>{{ strtoupper($kyc->status) }}</td>
                </tr>
            </table>       

                    @if ($signatureMedia && file_exists($signatureMedia->getPath()))
                <img 
                    src="data:image/png;base64,{{ base64_encode(file_get_contents($signatureMedia->getPath())) }}" 
                    style="height: 60px; width: auto; display: block;"
                    alt="Digital Signature"
                >
            @endif

        </div>

        <div class="box">
            <p>
                I hereby declare that all information provided by me is true and accurate to the best of my knowledge.
                I confirm that I have read, understood, and agreed to all sections of this Research Analyst Agreement.
            </p>

            <p class="consent">
                Client Consent: ☑ Accepted
            </p>

            <p>
                Signed By: <strong>{{ auth()->user()->name }}</strong><br>
                Signed At: <strong>{{ $signedAt ?? now()->format('d M Y, H:i') }}</strong><br>
                Agreement Version: <strong>v1.0</strong>
            </p>
        </div>

    </body>

    </html>

    {{-- ===================================================== --}}
    {{-- ===================== UI MODE ======================= --}}
    {{-- ===================================================== --}}
@else
    <div x-show="step === 5" class="bg-white rounded-lg border border-blue-100 p-6 space-y-6 overflow-hidden">

        <div class="border-b border-blue-50 pb-4">
            <h2 class="text-base font-bold text-blue-900 tracking-tight uppercase">
                Final Review & Digital Consent
            </h2>
            <p class="text-[11px] text-slate-500 italic">
                Please verify the summary below before final submission.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="md:col-span-2 space-y-4">

                <div class="bg-blue-50/40 border border-blue-100 rounded p-4">
                    <h3
                        class="text-[10px] uppercase tracking-widest font-bold text-blue-800/60 mb-3 border-b border-blue-100 pb-1">
                        Subscription Summary
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] text-slate-400 uppercase font-semibold">Client Name</p>
                            <p class="text-xs font-bold text-blue-900">{{ auth()->user()->name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 uppercase font-semibold">Plan & Invoice</p>
                            <p class="text-xs font-bold text-blue-900">
                                {{ $subscription->plan->name }}
                                <span class="text-blue-400">#{{ $invoice->invoice_number }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 uppercase font-semibold">Amount Paid</p>
                            <p class="text-xs font-bold text-blue-900">
                                ₹ {{ number_format($subscription->amount ?? 0, 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 uppercase font-semibold">Filing Date</p>
                            <p class="text-xs font-bold text-blue-900">{{ now()->format('d M, Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-3 border-l-2 border-blue-600 bg-blue-50/20">
                    <p class="text-[11px] italic text-slate-600">
                        "I confirm that I have read and understood the terms of the Research Analyst services and
                        acknowledge the risks associated with capital markets."
                    </p>
                </div>

            </div>

            <div>
                <div class="bg-white border border-blue-100 rounded p-4 shadow-sm">
                    <h3 class="text-[10px] uppercase tracking-widest font-bold text-blue-800/60 mb-3">
                        Identity Status
                    </h3>

                    <p class="text-[11px]"><strong>PAN:</strong> {{ $kyc->pan_number }}</p>
                    <p class="text-[11px]"><strong>Aadhaar:</strong> **** {{ substr($aadhaarNumber, -4) }}</p>
                    <p class="text-[11px] text-green-600 font-bold">
                        ● {{ strtoupper($kyc->status) }}
                    </p>

                   
                 @if ($signatureMedia && file_exists($signatureMedia->getPath()))
    <img 
        src="data:image/png;base64,{{ base64_encode(file_get_contents($signatureMedia->getPath())) }}" 
        style="height: 60px; width: auto; display: block;"
        alt="Digital Signature"
    >
@endif
                            </div>
            </div>

        </div>

        <div class="bg-slate-50 p-4 rounded border">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" x-model="signed" class="h-4 w-4">
                <span class="text-[11px] font-semibold text-blue-900">
                    I hereby declare that all information provided is accurate and I am the authorized signatory.
                </span>
            </label>
        </div>

        <div class="flex justify-between pt-2">
            <button @click="back()" class="text-[11px] font-bold text-slate-400 uppercase">
                ← Previous
            </button>

          <button
    @click="submitAgreement()"
    :disabled="!signed || loading"
    class="text-[11px] font-bold uppercase px-10 py-3 rounded"
    :class="signed
        ? 'bg-blue-900 text-white'
        : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
>
    <span x-show="!loading">Finalize & Submit</span>
    <span x-show="loading">Submitting…</span>
</button>

   <!-- 👁 PREVIEW / DOWNLOAD PDF -->
        <!-- <a
            href="{{ route('agreement.download') }}"
            target="_blank"
            class="text-[11px] font-bold uppercase px-8 py-3 rounded
                   border border-blue-900 text-blue-900
                   hover:bg-blue-50 transition"
        >
            Preview Agreement (PDF)
        </a>
        </div> -->

    </div>
    <script>
        function agreementWizard() {
            return {
                step: 1,

                // STEP 1
                clientConfirm: false,
                consent1: false,

                // STEP 2
                consent2: false,

                // STEP 3
                consent3: false,

                // STEP 4
                consent4: false,

                // STEP 5
                signed: false,

                next() {
                    if (this.step === 1 && !(this.clientConfirm && this.consent1)) return;
                    if (this.step === 2 && !this.consent2) return;
                    if (this.step === 3 && !this.consent3) return;
                    if (this.step === 4 && !this.consent4) return;

                    if (this.step < 6) {
                        this.step++;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                back() {
                    if (this.step > 1) {
                        this.step--;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                submitAgreement() {
                    // 🔒 FINAL HARD BLOCK
                    if (!this.signed) return;

                    // 🔥 submit logic here (API / form submit)
                    console.log('Agreement submitted');

                    // optional success step
                    this.step = 6;
                }
            }
        }
    </script>

@endif
