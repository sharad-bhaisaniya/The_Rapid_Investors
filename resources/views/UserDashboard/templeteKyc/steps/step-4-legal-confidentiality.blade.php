{{-- <div x-show="step === 4" class="bg-white rounded-lg border border-blue-100 p-6 space-y-6 overflow-hidden">

    <div class="border-b border-blue-50 pb-4">
        <h2 class="text-base font-bold text-blue-900 tracking-tight uppercase">Part D & G: Legal Framework & Liability
        </h2>
        <p class="text-[11px] text-slate-500 italic">Governing Laws, Confidentiality, and Indemnification Clauses</p>
    </div>

    <div class="border border-blue-50 rounded bg-slate-50/30 p-5 space-y-6 text-[12px] text-slate-700 leading-relaxed">

        <section>
            <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-2 tracking-wider border-b border-blue-50 pb-1">1.
                Representations & Warranties</h3>
            <ul class="list-disc ml-4 space-y-2 text-slate-600">
                <li>The RA is duly registered under SEBI (Research Analysts) Regulations, 2014, with Registration No.
                    INH000023728.</li>
                <li>The RA meets all qualification and certification requirements mandated by SEBI and NISM[cite: 100,
                    202, 203].</li>
                <li>The Client represents they are legally entitled to enter this Agreement and all KYC details provided
                    are true and correct[cite: 206].</li>
                <li>The Client acknowledges the inherent market risks and volatility in securities investments[cite:
                    207].</li>
            </ul>
        </section>

        <section>
            <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-2 tracking-wider border-b border-blue-50 pb-1">2.
                Confidentiality & Data Protection</h3>
            <ul class="list-disc ml-4 space-y-2 text-slate-600">
                <li>We respect your privacy and will not disclose personal data except as required by law or regulatory
                    obligations[cite: 209, 210].</li>
                <li>Aggregated or anonymized data may be shared for research or compliance purposes without revealing
                    individual identities[cite: 211].</li>
                <li>Complete security of data over the internet cannot be guaranteed; transfers may be unencrypted over
                    multiple networks.</li>
            </ul>
        </section>

        <section>
            <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-2 tracking-wider border-b border-blue-50 pb-1">
                3. Liability & Force Majeure</h3>
            <ul class="list-disc ml-4 space-y-2 text-slate-600">
                <li>The RA shall not be liable for direct, indirect, or consequential losses, including lost profits,
                    due to reliance on research[cite: 217].</li>
                <li>Neither party is liable for failure or delay caused by events beyond reasonable control, such as
                    acts of God, war, or internet outages[cite: 219].</li>
                <li>If a Force Majeure Event exceeds 30 days, either party may terminate the Agreement without
                    liability.</li>
            </ul>
        </section>

        <section>
            <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-2 tracking-wider border-b border-blue-50 pb-1">
                4. Indemnification</h3>
            <p class="mb-2">The Client agrees to indemnify and hold harmless the RA from claims, damages, or
                liabilities arising out of[cite: 225]:</p>
            <ul class="list-disc ml-4 space-y-1 text-slate-600">
                <li>Breach of these Terms & Conditions or violation of law[cite: 229].</li>
                <li>Unauthorized or improper use of the Client account[cite: 230].</li>
                <li>Third-party claims related to Client actions or inactions[cite: 231].</li>
            </ul>
        </section>

        <section class="bg-white p-3 border border-blue-100 rounded">
            <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-1">5. Governing Law & Jurisdiction</h3>
            <p class="text-slate-600">
                These Terms shall be governed by the laws of India. Any disputes arising shall be subject to the
                exclusive jurisdiction of the <strong>courts and tribunals in Uttar Pradesh</strong>.
            </p>
        </section>

    </div>

    <div class="bg-blue-50/50 p-4 rounded border border-blue-100">
        <label class="group flex items-start gap-3 cursor-pointer">
            <div class="relative flex items-center h-5">
                <input type="checkbox" x-model="consent4"
                    class="h-4 w-4 rounded border-blue-300 text-blue-700 focus:ring-blue-600 transition cursor-pointer">
            </div>
            <div class="text-[11px] leading-tight">
                <span class="text-blue-900 font-semibold group-hover:text-blue-700 transition">
                    I agree to the Representations & Warranties, Confidentiality, Limitation of Liability, and the
                    Jurisdiction of Uttar Pradesh as outlined in this Agreement.
                </span>
            </div>
        </label>
    </div>

    <div class="flex justify-between items-center pt-2">
        <button @click="back()"
            class="text-[11px] font-bold text-slate-400 hover:text-blue-800 transition uppercase tracking-widest">
            ← Previous
        </button>

        <button @click="next()" :disabled="!consent4"
            class="text-[11px] font-bold uppercase tracking-[0.2em] px-10 py-3 rounded transition-all duration-300 shadow-sm"
            :class="consent4
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

            // STEP 4
            consent4: false,

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
        <title>Legal Framework & Liability</title>
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

            ul {
                margin-left: 18px;
            }

            .section {
                margin-bottom: 14px;
            }

            hr {
                margin: 16px 0;
            }
        </style>
    </head>

    <body>

        <h2 style="text-align:center;">
            Part D & G: Legal Framework & Liability
        </h2>

        <p style="text-align:center; font-size:11px;">
            Governing Laws, Confidentiality, and Indemnification Clauses
        </p>

        <hr>

        <div class="section">
            <h3>1. Representations & Warranties</h3>
            <ul>
                <li>The Research Analyst (RA) is duly registered under SEBI (Research Analysts) Regulations, 2014 (Reg.
                    No. INH000023728).</li>
                <li>The RA fulfills all qualification and certification requirements prescribed by SEBI and NISM.</li>
                <li>The Client represents that all KYC details provided are true, complete, and accurate.</li>
                <li>The Client acknowledges inherent market risks and volatility in securities investments.</li>
            </ul>
        </div>

        <div class="section">
            <h3>2. Confidentiality & Data Protection</h3>
            <ul>
                <li>Client data shall not be disclosed except as required under law or regulatory obligations.</li>
                <li>Aggregated or anonymized data may be used for research or compliance purposes.</li>
                <li>Absolute security of data transmission over the internet cannot be guaranteed.</li>
            </ul>
        </div>

        <div class="section">
            <h3>3. Liability & Force Majeure</h3>
            <ul>
                <li>The RA shall not be liable for direct or indirect losses arising from reliance on research.</li>
                <li>Neither party shall be liable for events beyond reasonable control (Force Majeure).</li>
                <li>If a Force Majeure event continues beyond 30 days, either party may terminate the Agreement.</li>
            </ul>
        </div>

        <div class="section">
            <h3>4. Indemnification</h3>
            <p>
                The Client agrees to indemnify and hold harmless the RA against claims arising from:
            </p>
            <ul>
                <li>Breach of these terms or applicable laws</li>
                <li>Unauthorized or improper use of the Client account</li>
                <li>Third-party claims due to Client actions or omissions</li>
            </ul>
        </div>

        <div class="section">
            <h3>5. Governing Law & Jurisdiction</h3>
            <p>
                This Agreement shall be governed by the laws of India. Any disputes shall be subject to the
                exclusive jurisdiction of the courts and tribunals in <strong>Uttar Pradesh</strong>.
            </p>
        </div>

        <p style="margin-top:18px;">
            <strong>Client Consent:</strong> ☑
            I agree to the Representations & Warranties, Confidentiality, Limitation of Liability,
            and Jurisdiction clauses contained herein.
        </p>

    </body>

    </html>

    {{-- ===================================================== --}}
    {{-- ===================== UI MODE ======================= --}}
    {{-- ===================================================== --}}
@else
    <div x-show="step === 4" class="bg-white rounded-lg border border-blue-100 p-6 space-y-6 overflow-hidden">

        <div class="border-b border-blue-50 pb-4">
            <h2 class="text-base font-bold text-blue-900 tracking-tight uppercase">
                Part D & G: Legal Framework & Liability
            </h2>
            <p class="text-[11px] text-slate-500 italic">
                Governing Laws, Confidentiality, and Indemnification Clauses
            </p>
        </div>

        <div
            class="border border-blue-50 rounded bg-slate-50/30 p-5 space-y-6 text-[12px] text-slate-700 leading-relaxed">

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-2 border-b border-blue-50 pb-1">
                    1. Representations & Warranties
                </h3>
                <ul class="list-disc ml-4 space-y-2 text-slate-600">
                    <li>RA is registered with SEBI (INH000023728).</li>
                    <li>RA meets SEBI & NISM qualification requirements.</li>
                    <li>Client confirms accuracy of all KYC details.</li>
                    <li>Client acknowledges market risks and volatility.</li>
                </ul>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-2 border-b border-blue-50 pb-1">
                    2. Confidentiality & Data Protection
                </h3>
                <ul class="list-disc ml-4 space-y-2 text-slate-600">
                    <li>Personal data disclosed only as per law.</li>
                    <li>Anonymized data may be used for compliance.</li>
                    <li>Internet transmission risks acknowledged.</li>
                </ul>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-2 border-b border-blue-50 pb-1">
                    3. Liability & Force Majeure
                </h3>
                <ul class="list-disc ml-4 space-y-2 text-slate-600">
                    <li>No liability for losses from research reliance.</li>
                    <li>Force majeure events exempt liability.</li>
                    <li>Termination allowed after 30 days of force majeure.</li>
                </ul>
            </section>

            <section>
                <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-2 border-b border-blue-50 pb-1">
                    4. Indemnification
                </h3>
                <ul class="list-disc ml-4 space-y-2 text-slate-600">
                    <li>Breach of agreement or law</li>
                    <li>Unauthorized account usage</li>
                    <li>Third-party claims due to client actions</li>
                </ul>
            </section>

            <section class="bg-white p-3 border border-blue-100 rounded">
                <h3 class="font-bold text-blue-900 uppercase text-[10px] mb-1">
                    5. Governing Law & Jurisdiction
                </h3>
                <p class="text-slate-600">
                    Governed by Indian laws with exclusive jurisdiction in Uttar Pradesh.
                </p>
            </section>

        </div>

        <div class="bg-blue-50/50 p-4 rounded border border-blue-100">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" x-model="consent4" class="h-4 w-4 rounded border-blue-300 text-blue-700">
                <span class="text-[11px] font-semibold text-blue-900">
                    I agree to the Legal Framework & Liability terms of this Agreement.
                </span>
            </label>
        </div>

        <div class="flex justify-between pt-2">
            <button @click="back()" class="text-[11px] font-bold text-slate-400 hover:text-blue-800 uppercase">
                ← Previous
            </button>

            <button @click="next()" :disabled="!consent4"
                class="text-[11px] font-bold uppercase px-10 py-3 rounded transition"
                :class="consent4
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

                // STEP 3
                consent3: false,

                // STEP 4
                consent4: false,

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
                }
            }
        }
    </script>
@endif
