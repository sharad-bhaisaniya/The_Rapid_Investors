

<div x-show="step === 6" class="bg-white rounded-xl shadow p-8 text-center space-y-6 step-page">

 {{-- step-success.blade.php --}}

<div class="bg-white rounded-xl shadow p-8 text-center space-y-6">

    <h2 class="text-3xl font-bold text-green-600">
        Agreement Signed Successfully
    </h2>

    <div class="bg-slate-50 border rounded-lg p-5 text-sm space-y-2 max-w-md mx-auto text-left">
        <p><strong>Signed At:</strong> <span x-text="signedAt"></span></p>
        <p><strong>Agreement Version:</strong> <span x-text="agreementVersion"></span></p>
        <p><strong>Agreement No:</strong> <span x-text="agreementNumber"></span></p>
    </div>

    <button
        @click="downloadAgreementPdf()"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
        Download Full Agreement (PDF)
    </button>

</div>

<button
    @click="submitAgreement()"
    :disabled="loading || !signed"
    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold disabled:opacity-50">
    <span x-show="!loading">Sign & Save Agreement</span>
    <span x-show="loading">Saving…</span>
</button>

        <a href="{{ route('user.dashboard') }}" class="border px-6 py-3 rounded-lg font-semibold hover:bg-gray-50">
            Go to Dashboard
        </a>

    </div>

    <p class="text-xs text-gray-500 pt-4">
        This agreement is digitally executed and legally valid under the Information Technology Act, 2000.
    </p>

</div>



<script>
function agreementWizard() {
    return {
        /* -----------------------------
         * STATE
         * ----------------------------- */
        step: 1,
        loading: false,

        // STEP CHECKS
        clientConfirm: false,
        consent1: false,
        consent2: false,
        consent3: false,
        consent4: false,
        signed: false,

        // SUCCESS DATA
        signatureId: null,
        signedAt: null,
        agreementNumber: null,
        agreementVersion: 'v1.0',

        /* -----------------------------
         * NAVIGATION
         * ----------------------------- */
        next() {
            if (this.step === 1 && !(this.clientConfirm && this.consent1)) return;
            if (this.step === 2 && !this.consent2) return;
            if (this.step === 3 && !this.consent3) return;
            if (this.step === 4 && !this.consent4) return;

            if (this.step < 5) {
                this.step++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        back() {
            if (this.step > 1) {
                this.step--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        // /* -----------------------------
        //  * SUBMIT AGREEMENT (STEP-5)
        //  * ----------------------------- */
        // submitAgreement() {
        //     if (!this.signed || this.loading) return;

        //     this.loading = true;

        //     fetch("{{ route('agreement.generate') }}", {
        //         method: "POST",
        //         headers: {
        //             "Content-Type": "application/json",
        //             "X-CSRF-TOKEN": "{{ csrf_token() }}"
        //         }
        //     })
        //     .then(res => res.json())
        //     .then(data => {
        //         this.loading = false;

        //         if (data.status !== 'ok') {
        //             alert(data.message || 'Agreement generation failed');
        //             return;
        //         }

        //         // BACKEND RESPONSE
        //         this.agreementNumber = data.agreement_number;
        //         this.signedAt = data.signed_at;
        //         this.signatureId = data.signature_id ?? null;

        //         // SUCCESS STEP
        //         this.step = 6;
        //     })
        //     .catch(() => {
        //         this.loading = false;
        //         alert('Server error while generating agreement');
        //     });
        // },

        submitAgreement() {
    if (!this.signed || this.loading) return;

    this.loading = true;

    fetch("{{ route('agreement.generate') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(async res => {
        this.loading = false;

        // Agar server error / redirect HTML aya
        if (!res.ok) {
            window.location.href = "{{ route('agreement.latest') }}";
            return;
        }

        const data = await res.json();

        if (data.status !== 'ok') {
            alert(data.message || 'Agreement generation failed');
            window.location.href = "{{ route('agreement.latest') }}";
            return;
        }

        // ✅ SUCCESS → REDIRECT
        window.location.href = data.redirect_url || "{{ route('agreement.latest') }}";
    })
    .catch(() => {
        this.loading = false;
        window.location.href = "{{ route('agreement.latest') }}";
    });
},


        /* -----------------------------
         * DOWNLOAD AGREEMENT PDF
         * ----------------------------- */
        downloadAgreementPdf() {
            if (!this.agreementNumber) return;

            window.location.href =
                "{{ url('/agreement') }}/" + this.agreementNumber + "/pdf";
        }
    }
}
</script>