<div id="agreementModal" class="fixed inset-0 z-[100] hidden bg-black/50 backdrop-blur-sm flex items-center justify-center">

    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden">

        {{-- HEADER --}}
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-black text-sky-950">
                Client Agreement & Terms
            </h2>
            <button onclick="closeAgreement()" class="text-slate-400 hover:text-slate-700 text-xl">
                ✕
            </button>
        </div>

        {{-- SCROLLABLE CONTENT --}}
        <div class="p-6 max-h-[60vh] overflow-y-auto text-sm text-slate-700 space-y-6 leading-relaxed">

            <h3 class="font-black text-sky-900">CLIENT AGREEMENT & TERMS AND CONDITIONS</h3>

            <p>
                This Agreement is entered into between <strong>Bharat Stock Market Research</strong>,
                a SEBI registered Research Analyst (Registration No:
                <strong>INH000023728</strong>), and the Client/User.
            </p>

            <h4 class="font-bold text-slate-900">1. Scope & Nature of Services</h4>
            <ul class="list-disc ml-5 space-y-1">
                <li>Research-only services. No execution of trades.</li>
                <li>No assured or guaranteed returns.</li>
                <li>All recommendations are subject to market risk.</li>
                <li>Past performance is not indicative of future results.</li>
            </ul>

            <h4 class="font-bold text-slate-900">2. Regulatory Compliance</h4>
            <p>
                Services are provided strictly in accordance with SEBI (Research Analysts)
                Regulations, 2014 and applicable circulars.
            </p>

            <h4 class="font-bold text-slate-900">3. Eligibility & KYC</h4>
            <ul class="list-disc ml-5 space-y-1">
                <li>Only individuals above 18 years may subscribe.</li>
                <li>Client must provide accurate and complete KYC details.</li>
                <li>Services may be suspended or terminated if KYC is false or incomplete.</li>
            </ul>

            <h4 class="font-bold text-slate-900">4. Fees & Payment</h4>
            <ul class="list-disc ml-5 space-y-1">
                <li>Fees are charged in advance as per SEBI guidelines.</li>
                <li>Maximum fee ₹1,51,000 per annum per family (excluding taxes).</li>
            </ul>

            <h4 class="font-bold text-slate-900">5. Refund Policy</h4>
            <p>
                Subscriptions are generally non-refundable except where mandated
                under SEBI regulations.
            </p>

            <h4 class="font-bold text-slate-900">6. Risk Disclosure</h4>
            <p>
                Investments in securities markets are subject to market risks,
                including loss of capital. No guarantees are provided.
            </p>

            <h4 class="font-bold text-slate-900">7. Governing Law</h4>
            <p>
                This Agreement shall be governed by the laws of India and subject
                to jurisdiction of courts in Uttar Pradesh.
            </p>

            <hr>

            <p class="text-xs text-slate-500">
                Plan: <strong>{{ $selectedPlan->name }}</strong><br>
                Duration: <strong>{{ $selectedDuration->duration }}</strong><br>
                Amount Payable:
                <strong>₹{{ number_format($selectedDuration->price, 2) }}</strong>
            </p>

        </div>

        {{-- FOOTER / CONSENT --}}
        <div class="px-6 py-4 border-t bg-slate-50 flex items-center justify-between">

            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                <input type="checkbox" id="agreeCheck" class="w-4 h-4">
                I have read and agree to the terms
            </label>

            <button id="agreeContinueBtn"
                    disabled
                    class="px-6 py-3 bg-sky-900 text-white rounded-xl font-black
                           disabled:opacity-40 disabled:cursor-not-allowed">
                Proceed to Payment
            </button>

        </div>

    </div>
</div>

<script>
    const agreementModal = document.getElementById('agreementModal');
    const agreeCheck = document.getElementById('agreeCheck');
    const agreeContinueBtn = document.getElementById('agreeContinueBtn');

    function openAgreement() {
        agreementModal.classList.remove('hidden');
    }

    function closeAgreement() {
        agreementModal.classList.add('hidden');
        agreeCheck.checked = false;
        agreeContinueBtn.disabled = true;
    }

    agreeCheck.addEventListener('change', () => {
        agreeContinueBtn.disabled = !agreeCheck.checked;
    });

    // Optional: Handle "Proceed to Payment" click
    agreeContinueBtn.addEventListener('click', () => {
        // You can trigger the payment process here, e.g., submit a form or redirect
        alert('Proceeding to payment...');
        closeAgreement();
    });
</script>