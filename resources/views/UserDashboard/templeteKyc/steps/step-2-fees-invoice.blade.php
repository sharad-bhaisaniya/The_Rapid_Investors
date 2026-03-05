{{-- <div x-show="step === 2" class="bg-white rounded-lg border border-blue-100 p-6 space-y-6 overflow-hidden">

    <div class="border-b border-blue-50 pb-4">
        <h2 class="text-base font-bold text-blue-900 tracking-tight uppercase">Part A: Fees, Payment & Refund Policy</h2>
        <p class="text-[11px] text-slate-500 italic">Governed by SEBI Circular No.
            SEBI/HO/MIRSD/MIRSD-PoD-1/P/CIR/2025/004</p>
    </div>

    <div class="border border-blue-50 rounded bg-slate-50/30 p-5 space-y-6 text-[13px] text-slate-700 leading-relaxed">

        <section>
            <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">1. Maximum Fee Disclosure</h3>
            <p class="mb-2">
                As per Regulation 15A of the RA Regulations, the RA may charge fees up to
                <span class="font-bold text-blue-900">INR 1,51,000 (Rupees One Lakh Fifty-One Thousand)</span>
                per annum per "family of client" for individual and HUF clients.
            </p>
            <p class="text-slate-500 text-xs italic">
                *This amount excludes statutory taxes and charges. Fees may be revised every three years in line with
                the Cost Inflation Index.
            </p>
        </section>

        <section>
            <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">2. Billing & Mode of Payment
            </h3>
            <p class="mb-2">All payments shall be made only through secure and trackable banking channels:</p>
            <ul class="list-disc ml-5 space-y-1 text-slate-600">
                <li>NEFT / RTGS / IMPS Bank Transfers</li>
                <li>Digital modes (UPI, Net Banking)</li>
                <li>Cheque or Demand Draft</li>
                <li><span class="font-bold text-red-600 underline">Cash payments are strictly prohibited.</span></li>
            </ul>
        </section>

        <section>
            <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">3. Termination & Pro-Rata
                Refund</h3>
            <p>
                In the event of premature termination of Services, the RA shall refund the fees for the unexpired
                portion of the subscription period on a <strong>pro-rata basis</strong>. No "breakage" fee or penalty
                shall be imposed.
            </p>
            <p class="mt-2 italic">
                If the RA's SEBI registration is suspended for more than 60 days, unutilized fees will be refunded from
                the effective date of suspension.
            </p>
        </section>

        <section class="bg-white border border-blue-100 rounded-md p-4 shadow-sm mt-4">
            <h3
                class="text-[10px] uppercase tracking-widest font-bold text-blue-800/60 mb-3 border-b border-blue-50 pb-1">
                Client Subscription Summary</h3>
            <div class="grid grid-cols-2 gap-y-4 gap-x-4">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold">Client Name</p>
                    <p class="text-xs font-bold text-blue-900">{{ auth()->user()->name ?? 'Client Name' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold">Service Period</p>
                    <p class="text-xs font-bold text-blue-900">
                        {{ $subscription->duration->duration ?? 'Annual Subscription' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold">Amount Paid</p>
                    <p class="text-xs font-bold text-blue-900">₹ {{ number_format($subscription->amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold">Invoice ID</p>
                    <p class="text-xs font-bold text-blue-900">{{ $invoice->invoice_number ?? 'INV-XXXX' }}</p>
                </div>
            </div>
        </section>
    </div>

    <div class="bg-blue-50/50 p-4 rounded border border-blue-100">
        <label class="group flex items-start gap-3 cursor-pointer">
            <div class="relative flex items-center h-5">
                <input type="checkbox" x-model="consent2"
                    class="h-4 w-4 rounded border-blue-300 text-blue-700 focus:ring-blue-600 transition cursor-pointer">
            </div>
            <div class="text-[11px] leading-tight">
                <span class="text-blue-900 font-semibold group-hover:text-blue-700 transition">
                    I confirm that I have reviewed the fee structure, payment terms, and the pro-rata refund policy as
                    mandated by SEBI.
                </span>
            </div>
        </label>
    </div>

    <div class="flex justify-between items-center pt-2">
        <button @click="back()"
            class="text-[11px] font-bold text-slate-400 hover:text-blue-800 transition uppercase tracking-widest">
            ← Previous
        </button>
        <button @click="next()" :disabled="!consent2"
            class="text-[11px] font-bold uppercase tracking-[0.2em] px-10 py-3 rounded transition-all duration-300 shadow-sm"
            :class="consent2
                ?
                'bg-blue-900 text-white hover:bg-blue-800 cursor-pointer' :
                'bg-slate-200 text-slate-400 cursor-not-allowed opacity-60'">
            Accept & Continue
        </button>

    </div>

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

            next() {

                // 🔒 STEP 1 BLOCK
                if (this.step === 1 && !(this.clientConfirm && this.consent1)) {
                    return;
                }

                // 🔒 STEP 2 BLOCK
                if (this.step === 2 && !this.consent2) {
                    return;
                }

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
            }
        }
    }
</script> --}}


@php
    $isPdf = isset($pdfMode) && $pdfMode === true;
@endphp

{{-- ===================================================== --}}
{{-- ===================== PDF MODE ====================== --}}
{{-- ===================================================== --}}
@if ($isPdf)
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <title>Fees, Payment & Refund Policy</title>
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
                margin-top: 10px;
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
            Part A: Fees, Payment & Refund Policy
        </h2>

        <p style="text-align:center; font-size:11px;">
            Governed by SEBI Circular No. SEBI/HO/MIRSD/MIRSD-PoD-1/P/CIR/2025/004
        </p>

        <hr>

        <h4>1. Maximum Fee Disclosure</h4>
        <p>
            As per Regulation 15A of the Research Analyst Regulations, the RA may charge fees up to
            <strong>INR 1,51,000 (Rupees One Lakh Fifty-One Thousand)</strong>
            per annum per family of client for individual and HUF clients.
        </p>
        <p style="font-size:11px;">
            This amount excludes statutory taxes and charges. Fees may be revised every three years
            in line with the Cost Inflation Index.
        </p>

        <h4>2. Billing & Mode of Payment</h4>
        <p>All payments shall be made only through secure and trackable banking channels:</p>
        <ul>
            <li>NEFT / RTGS / IMPS Bank Transfers</li>
            <li>Digital modes (UPI, Net Banking)</li>
            <li>Cheque or Demand Draft</li>
            <li><strong>Cash payments are strictly prohibited</strong></li>
        </ul>

        <h4>3. Termination & Pro-Rata Refund</h4>
        <p>
            In the event of premature termination of services, the RA shall refund fees for the
            unexpired portion of the subscription period on a <strong>pro-rata basis</strong>.
            No breakage fee or penalty shall be imposed.
        </p>
        <p>
            If the RA’s SEBI registration is suspended for more than 60 days, unutilized fees shall
            be refunded from the effective date of suspension.
        </p>

        <h4>Client Subscription Summary</h4>

        <table>
            <tr>
                <td width="30%"><strong>Client Name</strong></td>
                <td>{{ auth()->user()->name ?? 'Client Name' }}</td>
            </tr>
            <tr>
                <td><strong>Service Period</strong></td>
                <td>{{ $subscription->duration->duration ?? 'Annual Subscription' }}</td>
            </tr>
            <tr>
                <td><strong>Amount Paid</strong></td>
                <td>₹ {{ number_format($subscription->amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Invoice ID</strong></td>
                <td>{{ $invoice->invoice_number ?? 'INV-XXXX' }}</td>
            </tr>
        </table>

        <p style="margin-top:15px;">
            <strong>Client Confirmation:</strong> ☑
            I confirm that I have reviewed the fee structure, payment terms,
            and the pro-rata refund policy as mandated by SEBI.
        </p>

    </body>

    </html>

    {{-- ===================================================== --}}
    {{-- ===================== UI MODE ======================= --}}
    {{-- ===================================================== --}}
@else
    <div x-show="step === 2" class="bg-white rounded-lg border border-blue-100 p-6 space-y-6 overflow-hidden">

        <div class="border-b border-blue-50 pb-4">
            <h2 class="text-base font-bold text-blue-900 tracking-tight uppercase">
                Part A: Fees, Payment & Refund Policy
            </h2>
            <p class="text-[11px] text-slate-500 italic">
                Governed by SEBI Circular No. SEBI/HO/MIRSD/MIRSD-PoD-1/P/CIR/2025/004
            </p>
        </div>

        <div
            class="border border-blue-50 rounded bg-slate-50/30 p-5 space-y-6 text-[13px] text-slate-700 leading-relaxed">

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">
                    1. Maximum Fee Disclosure
                </h3>
                <p class="mb-2">
                    As per Regulation 15A of the RA Regulations, the RA may charge fees up to
                    <span class="font-bold text-blue-900">
                        INR 1,51,000 (Rupees One Lakh Fifty-One Thousand)
                    </span>
                    per annum per family of client.
                </p>
                <p class="text-slate-500 text-xs italic">
                    This amount excludes statutory taxes and charges.
                </p>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">
                    2. Billing & Mode of Payment
                </h3>
                <ul class="list-disc ml-5 space-y-1 text-slate-600">
                    <li>NEFT / RTGS / IMPS Bank Transfers</li>
                    <li>Digital modes (UPI, Net Banking)</li>
                    <li>Cheque or Demand Draft</li>
                    <li class="font-bold text-red-600">Cash payments are strictly prohibited</li>
                </ul>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[11px] mb-2 tracking-wider">
                    3. Termination & Pro-Rata Refund
                </h3>
                <p>
                    In the event of premature termination of services, fees shall be refunded
                    on a pro-rata basis without penalty.
                </p>
            </section>

            <section class="bg-white border border-blue-100 rounded-md p-4 shadow-sm">
                <h3 class="text-[10px] uppercase font-bold text-blue-800/60 mb-3">
                    Client Subscription Summary
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-semibold">Client Name</p>
                        <p class="text-xs font-bold text-blue-900">{{ auth()->user()->name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-semibold">Service Period</p>
                        <p class="text-xs font-bold text-blue-900">
                            {{ $subscription->duration->duration ?? 'Annual Subscription' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-semibold">Amount Paid</p>
                        <p class="text-xs font-bold text-blue-900">
                            ₹ {{ number_format($subscription->amount ?? 0, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-semibold">Invoice ID</p>
                        <p class="text-xs font-bold text-blue-900">
                            {{ $invoice->invoice_number ?? 'INV-XXXX' }}
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <div class="bg-blue-50/50 p-4 rounded border border-blue-100">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" x-model="consent2" class="h-4 w-4 rounded border-blue-300 text-blue-700">
                <span class="text-[11px] font-semibold text-blue-900">
                    I confirm that I have reviewed the fee structure and refund policy.
                </span>
            </label>
        </div>

        <div class="flex justify-between pt-2">
            <button @click="back()" class="text-[11px] font-bold text-slate-400 hover:text-blue-800 uppercase">
                ← Previous
            </button>

            <button @click="next()" :disabled="!consent2"
                class="text-[11px] font-bold uppercase px-10 py-3 rounded transition"
                :class="consent2
                    ?
                    'bg-blue-900 text-white' :
                    'bg-slate-200 text-slate-400 cursor-not-allowed'">
                Accept & Continue
            </button>
        </div>

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

                next() {

                    // 🔒 STEP 1 BLOCK
                    if (this.step === 1 && !(this.clientConfirm && this.consent1)) {
                        return;
                    }

                    // 🔒 STEP 2 BLOCK
                    if (this.step === 2 && !this.consent2) {
                        return;
                    }

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
                }
            }
        }
    </script>
@endif
