


@php
    $isPdf = isset($pdfMode) && $pdfMode === true;

    // already an array – DO NOT json_decode
    $kycDetails = $kyc->kyc_details;

     $signatureMedia = $kyc->getFirstMedia('kyc_signature');
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
        <title>Research Analyst Agreement</title>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
                line-height: 1.6;
            }

            h2,
            h3,
            h4 {
                margin-bottom: 6px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
            }

            table td {
                border: 1px solid #000;
                padding: 6px;
            }

            hr {
                margin: 15px 0;
            }
        </style>
    </head>

    <body>

        <h2 style="text-align:center;">
            Part A: Client Agreement & Introduction
        </h2>

        <p style="text-align:center; font-size:11px;">
            Registration No. INH000023728 | Bharat Stock Market Research
        </p>

        <hr>

        <h4>Regulatory Compliance Notice</h4>
        <p>
            This Agreement incorporates the minimum mandatory provisions prescribed under
            <strong>SEBI Circular No. SEBI/HO/MIRSD/MIRSD-PoD-1/P/CIR/2025/004</strong>
            dated January 08, 2025. In the event of any conflict, SEBI regulations shall prevail.
        </p>

        <h4>Verified Client Identity</h4>

        <table>
            <tr>
                <td width="30%"><strong>Client Name</strong></td>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <td><strong>Registration Date</strong></td>
                <td>{{ now()->format('d M Y') }}</td>
            </tr>
            <tr>
                <td><strong>Aadhaar Number</strong></td>
                <td>{{ $aadhaarNumber ? '**** **** ' . substr($aadhaarNumber, -4) : 'Not Available' }}</td>
            </tr>
            <tr>
                <td><strong>KYC Status</strong></td>
                
                <td>Verified (Digio)</td>
            </tr>
        </table>

        <div
            class="border border-blue-50 rounded bg-white p-5 space-y-6 text-[13px] text-slate-700 leading-relaxed shadow-inner">

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">1. Parties & Registration
                </h3>
                <p>
                    This Agreement is entered into between <strong>Namita Rathore</strong>, Proprietor of
                    <strong>Bharat Stock Market Research</strong> (Registration No. <strong>INH000023728</strong>),
                    hereinafter referred to as the "RA," and the registered user, hereinafter referred to as the
                    "Client."
                </p>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider tracking-wider">2. Scope of
                    Research Services</h3>
                <p class="mb-2">The RA provides research reports, model portfolios, and analyses pertaining to
                    Indian-listed securities. By subscribing, you acknowledge:</p>
                <ul class="list-disc ml-5 space-y-1 text-slate-600">
                    <li><strong>Research-Only:</strong> We provide analysis only and do not execute trades on your
                        behalf[cite: 5, 24].</li>
                    <li><strong>No Funds/Assets:</strong> We do not hold client funds or securities[cite: 24].</li>
                    <li><strong>Personal Use:</strong> Research content is for your exclusive use and cannot be
                        redistributed[cite: 31, 48].</li>
                </ul>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">3. Eligibility & Capacity
                </h3>
                <p>
                    Only individuals at least 18 years of age and legally competent to contract may register[cite: 34].
                    The RA reserves the right to reject or cancel registration if information is found to be false[cite:
                    43].
                </p>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">4. Security of Credentials
                </h3>
                <p>
                    The Client is solely responsible for maintaining the confidentiality of login credentials.
                    The RA shall not be liable for any unauthorized access resulting from client negligence.
                </p>
            </section>
        </div>

    </body>

    </html>

    {{-- ===================================================== --}}
    {{-- ===================== UI MODE ======================= --}}
    {{-- ===================================================== --}}
@else
    <div x-show="step === 1" class="bg-white rounded-lg border border-blue-100 p-6 space-y-6 overflow-hidden">

        <div class="border-b border-blue-50 pb-4">
            <h2 class="text-base font-bold text-blue-900 tracking-tight uppercase">Part A: Client Agreement &
                Introduction
            </h2>
            <p class="text-[11px] text-slate-500 italic">Registration No. INH000023728 | Bharat Stock Market Research
            </p>
        </div>

        <div
            class="bg-blue-50/50 border border-blue-100 p-4 rounded text-[12px] text-blue-900 leading-relaxed shadow-sm">
            <p class="font-semibold mb-1 uppercase tracking-wider text-[10px]">Regulatory Compliance Notice</p>
            This Agreement incorporates the minimum mandatory provisions prescribed under
            <strong>SEBI Circular No. SEBI/HO/MIRSD/MIRSD-PoD-1/P/CIR/2025/004</strong> dated January 08, 2025.
            In the event of any conflict with these terms, SEBI regulations shall prevail.
        </div>

        
        @php

    // already an array – DO NOT json_decode
    $kycDetails = $kyc->kyc_details;
        $signatureMedia = $kyc->getFirstMedia('kyc_signature');
    $selfieMedia = $kyc->getFirstMedia('kyc_selfie');
                        

    $aadhaar = $kycDetails['aadhaar'] ?? null;
    $aadhaarNumber = $aadhaar['id_number'] ?? null;
@endphp


        <div class="bg-slate-50 border border-slate-200 rounded p-5 space-y-4 shadow-inner">
            <h3
                class="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-2 border-b border-slate-200 pb-1">
                Verified Client Identity</h3>

            <div class="grid grid-cols-2 gap-4 text-[12px]">
                <div>
                    <p class="text-slate-400 font-semibold uppercase text-[9px]">Client Name</p>
                    <p class="font-bold text-blue-900">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-slate-400 font-semibold uppercase text-[9px]">Registration Date</p>
                    <p class="font-bold text-blue-900">{{ now()->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 font-semibold uppercase text-[9px]">Aadhaar Number</p>
                    <p class="font-bold text-blue-900">
                        @if ($aadhaarNumber)
                            **** **** {{ substr($aadhaarNumber, -4) }}
                        @else
                            Not available
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-slate-400 font-semibold uppercase text-[9px]">KYC Status</p>
                    <p class="font-bold text-green-600 uppercase tracking-tighter">✔ Verified (Digio)</p>
                </div>
            </div>

          @if ($signatureMedia)
    <img
        src="{{ $signatureMedia->getUrl() }}"
        alt="Digital Signature"
        class="h-12 w-auto grayscale opacity-80"
    >
@endif


            <label class="flex items-start gap-3 mt-4 group cursor-pointer">
                <input type="checkbox" x-model="clientConfirm"
                    class="mt-1 h-4 w-4 rounded required-checkbox border-slate-300 text-blue-700 focus:ring-blue-600 transition">
                <span class="text-[11px] font-semibold text-blue-900 group-hover:text-blue-700 leading-tight">
                    I confirm that the Aadhaar and KYC details displayed above belong to me and are accurate for the
                    purpose
                    of this Agreement.
                </span>
            </label>
        </div>

        <div
            class="border border-blue-50 rounded bg-white p-5 space-y-6 text-[13px] text-slate-700 leading-relaxed shadow-inner">

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">1. Parties & Registration
                </h3>
                <p>
                    This Agreement is entered into between <strong>Namita Rathore</strong>, Proprietor of
                    <strong>Bharat Stock Market Research</strong> (Registration No. <strong>INH000023728</strong>),
                    hereinafter referred to as the "RA," and the registered user, hereinafter referred to as the
                    "Client."
                </p>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider tracking-wider">2. Scope of
                    Research Services</h3>
                <p class="mb-2">The RA provides research reports, model portfolios, and analyses pertaining to
                    Indian-listed securities. By subscribing, you acknowledge:</p>
                <ul class="list-disc ml-5 space-y-1 text-slate-600">
                    <li><strong>Research-Only:</strong> We provide analysis only and do not execute trades on your
                        behalf[cite: 5, 24].</li>
                    <li><strong>No Funds/Assets:</strong> We do not hold client funds or securities[cite: 24].</li>
                    <li><strong>Personal Use:</strong> Research content is for your exclusive use and cannot be
                        redistributed[cite: 31, 48].</li>
                </ul>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">3. Eligibility & Capacity
                </h3>
                <p>
                    Only individuals at least 18 years of age and legally competent to contract may register[cite: 34].
                    The RA reserves the right to reject or cancel registration if information is found to be false[cite:
                    43].
                </p>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">4. Security of Credentials
                </h3>
                <p>
                    The Client is solely responsible for maintaining the confidentiality of login credentials.
                    The RA shall not be liable for any unauthorized access resulting from client negligence.
                </p>
            </section>
        </div>

        <div class="space-y-4 pt-2">
            <label
                class="group flex items-center gap-3 cursor-pointer p-3 rounded bg-blue-50/50 border border-blue-100">
                <input type="checkbox" x-model="consent1"
                    class="h-5 w-5 rounded border-blue-300 text-blue-700 focus:ring-blue-600 transition">
                <span
                    class="text-[12px] required-checkbox font-bold text-blue-900 uppercase tracking-tighter group-hover:text-blue-700">
                    I have read and accept all terms in the Part A: Introduction & Overview
                </span>
            </label>

            <button @click="next()" :disabled="!(clientConfirm && consent1)"
                class="w-full text-[11px] font-bold uppercase tracking-[0.2em] py-4 rounded transition-all duration-300 shadow-md"
                :class="(clientConfirm && consent1) ?
                'bg-blue-900 text-white hover:bg-blue-800 cursor-pointer' :
                'bg-slate-200 text-slate-400 cursor-not-allowed opacity-60'">
                Accept and Continue →
            </button>


        </div>

    </div>
    <script>
        function agreementWizard() {
            return {
                step: 1,
                clientConfirm: false,
                consent1: false,

                next() {
                    if (this.step === 1 && !(this.clientConfirm && this.consent1)) return;
                    this.step++;
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                },

                back() {
                    if (this.step > 1) {
                        this.step--;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                }
            }
        }
    </script>
@endif
