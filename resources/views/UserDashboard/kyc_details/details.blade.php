@extends('layouts.userdashboard')

@section('content')
<div class=" mx-auto px-2 sm:px-6  ">

    <div id="printableArea" class="bg-white rounded-3xl shadow-lg border border-slate-200 p-6 md:p-8">

        <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ url()->previous() }}"
           class="p-2 rounded-lg hover:bg-slate-100 text-slate-600 transition no-print"
           title="Back">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 19l-7-7 7-7" />
            </svg>
        </a>

        <h1 class="text-xl font-semibold text-slate-800">
            📄 KYC Verification · Aadhaar
        </h1>
    </div>

    <div class="flex items-center gap-3">
        <button onclick="window.print()" 
                class="no-print inline-flex items-center gap-2 px-4 py-2 bg-blue-800 text-white rounded-xl hover:bg-blue-900 transition shadow-sm text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.89l-2.1 2.1m0 0l-2.1-2.1m2.1 2.1V6.14M12.7 10.3l2.1-2.1m0 0l2.1 2.1m-2.1-2.1v10.5m-6 0h12" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
            </svg>
            Print Details
        </button>

        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium
                     bg-emerald-100 text-emerald-700">
            Verified
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8.25 8.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 111.414-1.414l2.543 2.543 7.543-7.543a1 1.414 0z"
                      clip-rule="evenodd"/>
            </svg>
        </span>
    </div>
</div>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-slate-50 rounded-2xl p-5">
                <h2 class="text-xs font-semibold tracking-wider text-slate-500 uppercase mb-4">
                    KYC Overview
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500 uppercase">Document Type</p>
                        <p class="font-semibold text-slate-800">Aadhaar</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 uppercase">Status</p>
                        <p class="font-semibold text-emerald-600">
                            {{ $kyc_status }} ✓
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-xs text-slate-500 uppercase">Last Refreshed</p>
                        <p class="font-medium text-slate-700">
                            {{ $last_refreshed }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 rounded-2xl p-5">
                <h2 class="text-xs font-semibold tracking-wider text-slate-500 uppercase mb-4">
                    Personal Information
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500 uppercase">Full Name</p>
                        <p class="font-semibold text-slate-800">{{ $full_name }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 uppercase">Father’s Name</p>
                        <p class="font-semibold text-slate-800">{{ $father_name }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 uppercase">Date of Birth</p>
                        <p class="font-medium text-slate-700">{{ $dob }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 uppercase">Gender</p>
                        <p class="font-medium text-slate-700">{{ $gender }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 rounded-2xl p-5">
                <h2 class="text-xs font-semibold tracking-wider text-slate-500 uppercase mb-4">
                    Aadhaar Information
                </h2>

                <div>
                    <p class="text-xs text-slate-500 uppercase">Aadhaar Number</p>
                    <p class="font-mono font-semibold text-slate-800 tracking-widest">
                        XXXX XXXX {{ substr($aadhaar_number, -4) }}
                    </p>
                </div>
            </div>
            @if(!empty($pan_number))
        <div class="bg-slate-50 rounded-2xl p-5">
            <h2 class="text-xs font-semibold tracking-wider text-slate-500 uppercase mb-4">
                PAN Information
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-500 uppercase">PAN Number</p>
                    <p class="font-mono font-semibold text-slate-800 tracking-widest">
                        {{ substr($pan_number, 0, 3) }}****{{ substr($pan_number, -1) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500 uppercase">Name on PAN</p>
                    <p class="font-semibold text-slate-800">
                        {{ $pan_name }}
                    </p>
                </div>
            </div>
        </div>
        @endif



            <div class="bg-slate-50 rounded-2xl p-5">
                <h2 class="text-xs font-semibold tracking-wider text-slate-500 uppercase mb-4">
                    Face Match
                </h2>

                <p class="font-semibold text-emerald-600">
                    Matched ✓
                </p>
            </div>

            <div class="md:col-span-2 bg-slate-50 rounded-2xl p-5">
                <h2 class="text-xs font-semibold tracking-wider text-slate-500 uppercase mb-4">
                    Address Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="font-semibold text-slate-700 mb-1">🏠 Current Address</p>
                        <p class="text-slate-600 text-sm">
                            {{ $current_address['address'] ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-700 mb-1">📬 Permanent Address</p>
                        <p class="text-slate-600 text-sm">
                            {{ $permanent_address['address'] ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 bg-slate-50 rounded-2xl p-5">
                <h2 class="text-xs font-semibold tracking-wider text-slate-500 uppercase mb-4">
                    Identity Media
                </h2>

                <div class="flex flex-wrap gap-6">

                    <div class="w-40">
                        <p class="text-xs text-center text-slate-500 uppercase mb-2">📸 Selfie</p>
                        <div class="h-32 w-full rounded-xl border bg-white flex items-center justify-center overflow-hidden">
                            @if($selfie)
                                <img src="{{ $selfie->getUrl() }}" class="object-cover w-full h-full">
                            @else
                                <span class="text-xs text-slate-400">No selfie</span>
                            @endif
                        </div>
                    </div>

                    <div class="w-40">
                        <p class="text-xs text-center text-slate-500 uppercase mb-2">✍️ Signature</p>
                        <div class="h-32 w-full rounded-xl border bg-white flex items-center justify-center overflow-hidden">
                            @if($signature)
                                <img src="{{ $signature->getUrl() }}" class="object-contain w-full h-full">
                            @else
                                <span class="text-xs text-slate-400">No signature</span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            <div class="md:col-span-2 bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                <p class="text-sm text-emerald-700 italic">
                    “This KYC has been digitally verified and is compliant with applicable regulatory requirements.”
                </p>
            </div>

        </div>
    </div>
</div>

<style>
    @media print {
        /* Hide everything except the content container */
        body * {
            visibility: hidden;
        }
        #printableArea, #printableArea * {
            visibility: visible;
        }
        #printableArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            box-shadow: none !important;
        }
        /* Hide buttons like 'Back' and 'Print' in the printed document */
        .no-print {
            display: none !important;
        }
        /* Adjust background colors for printers that disable them by default */
        .bg-slate-50 {
            background-color: #f8fafc !important;
            -webkit-print-color-adjust: exact;
        }
        .bg-emerald-50 {
            background-color: #ecfdf5 !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
@endsection