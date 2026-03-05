{{-- @extends('layouts.userdashboard')

@section('content')
    <div x-data="agreementWizard()" class="min-h-screen bg-slate-50 flex flex-col">

        <div class="w-full sticky top-0 z-50">
            @include('UserDashboard.templeteKyc.partials.progress-bar')
        </div>

        <div class="flex-grow w-full px-2 md:px-0 py-4">
            <div
                class="max-w-4xl mx-auto bg-white shadow-[0_0_15px_rgba(0,0,0,0.05)] border border-slate-200 min-h-[80vh] rounded-sm transition-all duration-300">

                <div class="p-4 md:p-10">

                    @include('UserDashboard.templeteKyc.steps.step-1-overview', [
                        'user' => $user,
                        'kyc' => $kyc,
                    ])

                    @include('UserDashboard.templeteKyc.steps.step-2-fees-invoice', [
                        'subscription' => $subscription,
                        'invoice' => $invoice,
                    ])

                    @include('UserDashboard.templeteKyc.steps.step-3-mitc-risk')

                    @include('UserDashboard.templeteKyc.steps.step-4-legal-confidentiality')

                    @include('UserDashboard.templeteKyc.steps.step-5-digital-signature', [
                        'user' => $user,
                    ])

                    @include('UserDashboard.templeteKyc.steps.step-success')

                </div>
            </div>
        </div>



    </div>
@endsection

<script>
    function agreementWizard() {
        return {
            step: 1,
            next() {
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


@extends('layouts.userdashboard')

@section('content')
    <div x-data="agreementWizard()" class="min-h-screen bg-slate-50 flex flex-col">

        <!-- Progress Bar with print:hidden -->
        <div class="w-full sticky top-0 z-50 print:hidden">
            @include('UserDashboard.templeteKyc.partials.progress-bar')
        </div>

        <div class="flex-grow w-full px-2 md:px-0 py-4">
            <div
                class="max-w-4xl mx-auto bg-white shadow-[0_0_15px_rgba(0,0,0,0.05)] border border-slate-200 min-h-[80vh] rounded-sm transition-all duration-300">

                <div class="p-4 md:p-10 print:p-2" id="agreement-content">

                    <!-- All your steps here (unchanged) -->
                    @include('UserDashboard.templeteKyc.steps.step-1-overview', [
                        'user' => $user,
                        'kyc' => $kyc,
                    ])

                    @include('UserDashboard.templeteKyc.steps.step-2-fees-invoice', [
                        'subscription' => $subscription,
                        'invoice' => $invoice,
                    ])

                    @include('UserDashboard.templeteKyc.steps.step-3-mitc-risk')

                    @include('UserDashboard.templeteKyc.steps.step-4-legal-confidentiality')

                    @include('UserDashboard.templeteKyc.steps.step-5-digital-signature', [
                        'user' => $user,
                    ])

                    @include('UserDashboard.templeteKyc.steps.step-success')

                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden;
            }

            #agreement-content,
            #agreement-content * {
                visibility: visible;
            }

            #agreement-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none !important;
                border: none !important;
            }

            /* Hide progress bar and navigation buttons in print */
            .print-hide,
            .no-print {
                display: none !important;
            }

            /* Ensure each step is visible */
            [x-show] {
                display: block !important;
                opacity: 1 !important;
                position: relative !important;
                page-break-inside: avoid;
            }

            /* Add page breaks between steps */
            .step-page {
                page-break-after: always;
            }

            /* Last step shouldn't have page break */
            .step-page:last-child {
                page-break-after: auto;
            }
        }
    </style>
@endpush


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

        /* -----------------------------
         * SUBMIT AGREEMENT (STEP-5)
         * ----------------------------- */
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
            .then(res => res.json())
            .then(data => {
                this.loading = false;

                if (data.status !== 'ok') {
                    alert(data.message || 'Agreement generation failed');
                    return;
                }

                // BACKEND RESPONSE
                this.agreementNumber = data.agreement_number;
                this.signedAt = data.signed_at;
                this.signatureId = data.signature_id ?? null;

                if (data.status === 'ok') {
        // Backend se aaye huye URL par redirect karein
        window.location.href = data.redirect_url;
    } else {
        alert(data.message || 'Agreement generation failed');
    }
                // SUCCESS STEP
                this.step = 6;
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



