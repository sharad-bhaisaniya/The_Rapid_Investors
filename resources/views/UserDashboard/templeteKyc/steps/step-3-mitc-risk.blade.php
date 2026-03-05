{{-- <div x-show="step === 3" class="bg-white rounded-lg border border-blue-100 p-6 space-y-6 overflow-hidden">

    <div class="border-b border-blue-50 pb-4">
        <h2 class="text-base font-bold text-blue-900 tracking-tight uppercase">Part C: Most Important Terms & Conditions
            (MITC)</h2>
        <p class="text-[11px] text-slate-500 italic">Standardized Risk Disclosures as per SEBI & Industry Standards Forum
            [cite: 144, 152]</p>
    </div>

    <div class="border border-blue-50 rounded bg-slate-50/30 p-5 space-y-3 text-[12px] text-slate-700 leading-relaxed">

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">Execution & Trade Warning</h4>
            <p>The Research Analyst (RA) <strong>cannot execute or carry out any trade</strong> (purchase/sell
                transaction) on behalf of the client[cite: 145, 153]. Clients are strictly advised not to permit the RA
                to execute any trade on their behalf[cite: 146, 154].</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">Market Risk & Returns</h4>
            <p>Investments in securities are subject to market risks, including volatility and potential <strong>loss of
                    principal</strong>[cite: 112, 265]. Any past performance is no indicator of future results, and no
                returns are assured or guaranteed[cite: 112, 178, 286].</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">Registration & Performance</h4>
            <p>SEBI registration, enlistment with RAASB, and NISM certification do not guarantee the performance of the
                RA or assure any specific returns to the client.</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">Fee Compliance</h4>
            <p>The current fee limit is <strong>INR 1,51,000 per annum per family of client</strong> for all research
                services[cite: 103, 157]. Advance fees shall not exceed one year[cite: 161].</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">Client Responsibility</h4>
            <p>Any reliance placed on the research report shall be as per the client's own judgement and
                assessment[cite: 84, 182]. There is no recourse to claim losses incurred on investments made based on RA
                recommendations.</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">Security Disclosure</h4>
            <p>The RA shall <strong>never ask for login credentials or OTPs</strong> for your Trading, Demat, or Bank
                accounts. Never share this information with anyone[cite: 192, 193].</p>
        </div>

    </div>

    <div class="bg-blue-50/50 p-4 rounded border border-blue-100">
        <label class="group flex items-start gap-3 cursor-pointer">
            <div class="relative flex items-center h-5">
                <input type="checkbox" x-model="consent3"
                    class="h-4 w-4 rounded border-blue-300 text-blue-700 focus:ring-blue-600 transition cursor-pointer">
            </div>
            <div class="text-[11px] leading-tight">
                <span class="text-blue-900 font-semibold group-hover:text-blue-700 transition">
                    I acknowledge that I have read and understood the Risk Factors and Most Important Terms & Conditions
                    (MITC) as presented in Part C of this agreement[cite: 152, 324].
                </span>
            </div>
        </label>
    </div>

    <div class="flex justify-between items-center pt-2">
        <button @click="back()"
            class="text-[11px] font-bold text-slate-400 hover:text-blue-800 transition uppercase tracking-widest">
            ← Previous
        </button>

        <button @click="next()" :disabled="!consent3"
            class="text-[11px] font-bold uppercase tracking-[0.2em] px-10 py-3 rounded transition-all duration-300 shadow-sm"
            :class="consent3
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

            // STEP 3
            consent3: false,

            next() {

                // 🔒 STEP 1 BLOCK
                if (this.step === 1 && !(this.clientConfirm && this.consent1)) {
                    return;
                }

                // 🔒 STEP 2 BLOCK
                if (this.step === 2 && !this.consent2) {
                    return;
                }

                // 🔒 STEP 3 BLOCK
                if (this.step === 3 && !this.consent3) {
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
    <title>Most Important Terms & Conditions (MITC)</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }
        h2, h3, h4 {
            margin-bottom: 6px;
        }
        ul {
            margin-left: 18px;
        }
        hr {
            margin: 15px 0;
        }
        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">
    Part C: Most Important Terms & Conditions (MITC)
</h2>

<p style="text-align:center; font-size:11px;">
    Standardized Risk Disclosures as per SEBI & Industry Standards Forum
</p>

<hr>

<div class="box">
    <h4>Execution & Trade Warning</h4>
    <p>
        The Research Analyst (RA) <strong>cannot execute or carry out any trade</strong>
        on behalf of the client. Clients are strictly advised not to permit the RA
        to execute any trade on their behalf.
    </p>
</div>

<div class="box">
    <h4>Market Risk & Returns</h4>
    <p>
        Investments in securities are subject to market risks, including volatility
        and potential <strong>loss of principal</strong>. Past performance is not
        indicative of future results and no returns are assured or guaranteed.
    </p>
</div>

<div class="box">
    <h4>Registration & Performance</h4>
    <p>
        SEBI registration, enlistment with RAASB, and NISM certification do not
        guarantee performance or assure any specific returns.
    </p>
</div>

<div class="box">
    <h4>Fee Compliance</h4>
    <p>
        The current fee limit is <strong>INR 1,51,000 per annum per family of client</strong>.
        Advance fees shall not exceed one year.
    </p>
</div>

<div class="box">
    <h4>Client Responsibility</h4>
    <p>
        Any reliance placed on the research report shall be as per the client’s own
        judgement. There is no recourse to claim losses incurred based on RA
        recommendations.
    </p>
</div>

<div class="box">
    <h4>Security Disclosure</h4>
    <p>
        The RA shall <strong>never ask for login credentials or OTPs</strong> for
        trading, demat, or bank accounts. Never share this information with anyone.
    </p>
</div>

<p style="margin-top:15px;">
    <strong>Client Acknowledgement:</strong> ☑  
    I acknowledge that I have read and understood the Risk Factors and Most Important
    Terms & Conditions (MITC) as presented in Part C of this agreement.
</p>

</body>
</html>

{{-- ===================================================== --}}
{{-- ===================== UI MODE ======================= --}}
{{-- ===================================================== --}}
@else

<div x-show="step === 3" class="bg-white rounded-lg border border-blue-100 p-6 space-y-6 overflow-hidden">

    <div class="border-b border-blue-50 pb-4">
        <h2 class="text-base font-bold text-blue-900 tracking-tight uppercase">
            Part C: Most Important Terms & Conditions (MITC)
        </h2>
        <p class="text-[11px] text-slate-500 italic">
            Standardized Risk Disclosures as per SEBI & Industry Standards Forum
        </p>
    </div>

    <div class="border border-blue-50 rounded bg-slate-50/30 p-5 space-y-3 text-[12px] text-slate-700 leading-relaxed">

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">
                Execution & Trade Warning
            </h4>
            <p>
                The Research Analyst (RA) <strong>cannot execute or carry out any trade</strong>
                on behalf of the client.
            </p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">
                Market Risk & Returns
            </h4>
            <p>
                Investments are subject to market risks including loss of principal.
                Past performance is not indicative of future results.
            </p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">
                Registration & Performance
            </h4>
            <p>
                SEBI registration and certifications do not guarantee performance
                or assure any returns.
            </p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">
                Fee Compliance
            </h4>
            <p>
                Fee limit is INR 1,51,000 per annum per family of client.
            </p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">
                Client Responsibility
            </h4>
            <p>
                Client relies on research at their own discretion with no recourse
                for losses.
            </p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow-sm">
            <h4 class="font-bold text-blue-900 uppercase text-[10px] mb-1">
                Security Disclosure
            </h4>
            <p>
                RA will never ask for login credentials or OTPs.
            </p>
        </div>

    </div>

    <div class="bg-blue-50/50 p-4 rounded border border-blue-100">
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" x-model="consent3"
                   class="h-4 w-4 rounded border-blue-300 text-blue-700">
            <span class="text-[11px] font-semibold text-blue-900">
                I acknowledge that I have read and understood the Risk Factors and MITC.
            </span>
        </label>
    </div>

    <div class="flex justify-between pt-2">
        <button @click="back()"
                class="text-[11px] font-bold text-slate-400 hover:text-blue-800 uppercase">
            ← Previous
        </button>

        <button @click="next()" :disabled="!consent3"
                class="text-[11px] font-bold uppercase px-10 py-3 rounded transition"
                :class="consent3
                    ? 'bg-blue-900 text-white'
                    : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
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

            // STEP 3
            consent3: false,

            next() {

                // 🔒 STEP 1 BLOCK
                if (this.step === 1 && !(this.clientConfirm && this.consent1)) {
                    return;
                }

                // 🔒 STEP 2 BLOCK
                if (this.step === 2 && !this.consent2) {
                    return;
                }

                // 🔒 STEP 3 BLOCK
                if (this.step === 3 && !this.consent3) {
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
