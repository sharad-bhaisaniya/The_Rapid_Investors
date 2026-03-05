


@extends('layouts.userdashboard')

@section('title', 'Confirm Subscription')

@section('content')

    <div class="max-w-7xl mx-auto px-4 py-6 pb-40 relative font-sans text-slate-900">

        {{-- Header Section --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl md:text-4xl font-black tracking-tight text-sky-950">
                Choose Your Trading Edge
            </h1>
            <p class="text-sky-600 mt-2 text-base">
                Premium stock market insights & actionable trade setups
            </p>
        </div>

        {{-- Pricing Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach ($plans as $plan)
            @php $durations = $plan->durations->values(); @endphp
            <div id="plan_card_{{ $plan->id }}" 
                class="plan-card bg-sky-50 border border-sky-100 rounded-[2rem] p-6 transition-all duration-300 flex flex-col hover:shadow-lg {{ $selectedPlan->id == $plan->id ? 'ring-2 ring-sky-500 shadow-xl' : 'shadow-sm' }}">
                
                <h3 class="text-xl font-black text-sky-950 mb-1">{{ $plan->name }}</h3>
                
                <div class="mb-4">
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-sky-900">
                            ₹{{ number_format($selectedPlan->id === $plan->id ? $selectedDuration->price : $durations[0]->price, 2) }}
                        </span>
                        <span class="text-[10px] font-bold text-sky-600 uppercase tracking-wider">(Inc. GST)</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5 mb-6">
                    @foreach ($durations as $index => $duration)
                    <a href="{{ route('subscription.confirm', ['plan' => $plan->id, 'duration' => $index]) }}"
                            class="px-4 py-1.5 rounded-full text-xs font-bold transition-all duration-200 
                            {{ $selectedPlan->id == $plan->id && $selectedDuration->id == $duration->id 
                                ? 'bg-sky-800 text-white shadow-sm scale-105' 
                                : 'bg-white text-sky-800 border border-sky-200 hover:border-sky-400' }}">
                        {{ $duration->duration }}
                    </a>
                    @endforeach
                </div>

                <div class="text-[11px] font-black text-sky-900 uppercase tracking-widest mb-3">Included Features</div>
                <ul class="space-y-3 mb-2 flex-1">
                    @php $currentFeatures = $selectedPlan->id === $plan->id ? $selectedDuration->features : $durations[0]->features; @endphp
                    @foreach ($currentFeatures as $feature)
                    <li class="flex items-center justify-between group">
                        <span class="text-[13px] font-medium text-sky-900/80 group-hover:text-sky-950">{{ $feature->text }}</span>
                        <span class="flex-shrink-0 ml-3">
                            @if($feature->svg_icon === '✖')
                                <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                            @else
                                <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                            @endif
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        {{-- FIXED ORDER SUMMARY (EXPANDED) --}}
        <div id="orderSummary" class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t-2 border-sky-100 shadow-[0_-20px_50px_rgba(14,165,233,0.1)] rounded-t-[2.5rem] px-6 py-8 md:px-12 transition-transform duration-500 ease-in-out">
            
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-black text-sky-950">Order Summary</h3>
                            <button onclick="toggleSummary(false)" class="text-[10px] font-bold bg-sky-50 hover:bg-sky-100 text-sky-600 px-4 py-2 rounded-full transition">
                                Minimize Summary ↓
                            </button>
                        </div>

                        <div class="bg-sky-50/50 rounded-2xl p-5 border border-sky-100/50 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 font-medium">Selected Plan</span>
                                <span class="font-bold text-sky-900">{{ $selectedPlan->name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 font-medium">Duration</span>
                                <span class="font-bold text-sky-900">{{ $selectedDuration->duration }}</span>
                            </div>
                            <div class="pt-4 border-t border-sky-100 flex justify-between items-end">
                                <div>
                                    <span class="text-[10px] font-bold text-sky-400 uppercase block tracking-widest">Total Payable</span>
                                    <span class="text-3xl font-black text-sky-900">₹<span id="display_price">{{ number_format($selectedDuration->price, 2) }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Promotional Code</label>
                            <div class="flex gap-2">
                                <input type="text" id="coupon_code" placeholder="ENTER CODE" class="flex-1 border-2 border-sky-100 bg-sky-50/30 rounded-xl px-4 py-3 text-sm font-bold uppercase focus:border-sky-500 focus:outline-none transition-all">
                                <button id="applyCouponBtn" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-black transition-all active:scale-95 shadow-lg shadow-emerald-100">
                                    Apply
                                </button>
                            </div>
                            <div id="coupon_msg" class="hidden text-[11px] mt-2 font-bold px-1"></div>
                        </div>

                        <button id="payBtn" data-plan="{{ $selectedPlan->id }}" data-duration="{{ $selectedDuration->id }}" 
                                class="w-full py-4 bg-sky-900 hover:bg-sky-950 text-white rounded-2xl font-black text-base transition-all active:scale-95 shadow-xl shadow-sky-900/20 flex items-center justify-center gap-3">
                            <span>Complete Subscription</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- COLLAPSED FLOATING BAR --}}
        <div id="collapsedBar" class="hidden fixed bottom-8 left-1/2 -translate-x-1/2 w-[90%] max-w-lg z-50 transition-all duration-300">
            <div class="bg-white/90 backdrop-blur-xl border border-sky-100 shadow-2xl p-4 flex items-center justify-between rounded-[2.5rem]">
                <div class="pl-4">
                    <div class="text-[10px] font-black text-sky-400 uppercase tracking-widest">{{ $selectedPlan->name }}</div>
                    <div class="text-xl font-black text-sky-900">₹{{ number_format($selectedDuration->price, 2) }}</div>
                </div>
                <div class="flex gap-3">
                    <button onclick="toggleSummary(true)" class="p-3 bg-sky-50 hover:bg-sky-100 text-sky-600 rounded-full transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
                    </button>
                    <button onclick="document.getElementById('payBtn').click()" class="px-8 py-3 bg-sky-900 text-white rounded-[1.5rem] font-black text-sm transition active:scale-95 shadow-lg shadow-sky-900/20">
                        Pay Now
                    </button>
                </div>
            </div>
        </div>

    </div>

    @include('subscription.partials.agreement-modal')

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <!-- <script>
        /* Toggle Logic */
        function toggleSummary(show) {
            const summary = document.getElementById('orderSummary');
            const collapsed = document.getElementById('collapsedBar');
            if (show) {
                summary.style.transform = 'translateY(0)';
                collapsed.classList.add('hidden');
            } else {
                summary.style.transform = 'translateY(100%)';
                collapsed.classList.remove('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const payBtn = document.getElementById('payBtn');
            const applyBtn = document.getElementById('applyCouponBtn');
            const couponInput = document.getElementById('coupon_code');
            const couponMsg = document.getElementById('coupon_msg');
            const displayPrice = document.getElementById('display_price');

            // Auto-collapse on small screens
            if (window.innerWidth < 768) toggleSummary(false);

            /* Razorpay Payment Logic */
            payBtn.addEventListener('click', function() {
                const planId = this.dataset.plan;
                const durationId = this.dataset.duration;
                const couponCode = couponInput.value.trim();

                payBtn.disabled = true;
                payBtn.innerText = 'Initializing...';

                fetch("{{ route('subscription.razorpay.initiate') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: JSON.stringify({ plan_id: planId, duration_id: durationId, coupon_code: couponCode })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        couponMsg.innerText = data.message || 'Error';
                        couponMsg.className = "text-[11px] mt-2 text-red-500 block font-bold";
                        payBtn.disabled = false;
                        payBtn.innerText = 'Complete Subscription';
                        return;
                    }

                    const options = {
                        key: data.key,
                        amount: data.amount,
                        currency: "INR",
                        name: "{{ config('app.name') }}",
                        description: data.description,
                        order_id: data.order_id,
                        handler: function(response) {
                            fetch("{{ route('subscription.razorpay.verify') }}", {
                                method: "POST",
                                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                                body: JSON.stringify({ ...response, plan_id: planId, duration_id: durationId, coupon_code: couponCode })
                            })
                            .then(res => res.json())
                            .then(result => {
                                if (result.success) window.location.href = "/dashboard?subscribed=success";
                                else alert(result.message);
                            });
                        },
                        modal: { ondismiss: () => { payBtn.disabled = false; payBtn.innerText = 'Complete Subscription'; } },
                        prefill: data.user,
                        theme: { color: "#0ea5e9" }
                    };
                    new Razorpay(options).open();
                });
            });

            /* Coupon Logic */
            applyBtn.addEventListener('click', function() {
                const code = couponInput.value.trim();
                if (!code) return;
                applyBtn.disabled = true;

                fetch("{{ route('subscription.coupon.apply') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: JSON.stringify({ coupon_code: code, duration_id: payBtn.dataset.duration })
                })
                .then(res => res.json())
                .then(data => {
                    applyBtn.disabled = false;
                    couponMsg.classList.remove('hidden');
                    if (data.success) {
                        couponMsg.innerText = data.message;
                        couponMsg.className = "text-[11px] mt-2 text-emerald-600 block font-bold";
                        displayPrice.innerText = data.new_total;
                    } else {
                        couponMsg.innerText = data.message;
                        couponMsg.className = "text-[11px] mt-2 text-red-500 block font-bold";
                    }
                });
            });
        });
    </script> -->

<script>
    /* ================= TOGGLE LOGIC (UNCHANGED) ================= */
    function toggleSummary(show) {
        const summary = document.getElementById('orderSummary');
        const collapsed = document.getElementById('collapsedBar');
        if (show) {
            summary.style.transform = 'translateY(0)';
            collapsed.classList.add('hidden');
        } else {
            summary.style.transform = 'translateY(100%)';
            collapsed.classList.remove('hidden');
        }
    }

    /* ================= AGREEMENT STATE ================= */
    let agreementAccepted = false;

    function openAgreement() {
        document.getElementById('agreementModal').classList.remove('hidden');
    }

    function closeAgreement() {
        document.getElementById('agreementModal').classList.add('hidden');
    }

    /* ================= MAIN LOGIC ================= */
    document.addEventListener('DOMContentLoaded', function () {

        const payBtn = document.getElementById('payBtn');
        const applyBtn = document.getElementById('applyCouponBtn');
        const couponInput = document.getElementById('coupon_code');
        const couponMsg = document.getElementById('coupon_msg');
        const displayPrice = document.getElementById('display_price');

        const agreeCheck = document.getElementById('agreeCheck');
        const agreeContinueBtn = document.getElementById('agreeContinueBtn');

        /* Auto-collapse on small screens */
        if (window.innerWidth < 768) toggleSummary(false);

        /* ================= AGREEMENT UI ================= */
        if (agreeCheck && agreeContinueBtn) {
            agreeContinueBtn.disabled = true;

            agreeCheck.addEventListener('change', function () {
                agreeContinueBtn.disabled = !this.checked;
            });

            agreeContinueBtn.addEventListener('click', function () {
                agreementAccepted = true;
                closeAgreement();

                // Now payment is allowed
                payBtn.click();
            });
        }

        /* ================= PAY BUTTON (SINGLE SOURCE OF TRUTH) ================= */
        payBtn.addEventListener('click', function (e) {

            /* 🔒 HARD BLOCK BEFORE AGREEMENT */
            if (!agreementAccepted) {
                e.preventDefault();
                openAgreement();
                return; // ⛔ Razorpay will NOT run
            }

            /* ================= RAZORPAY PAYMENT ================= */
            const planId = this.dataset.plan;
            const durationId = this.dataset.duration;
            const couponCode = couponInput.value.trim();

            payBtn.disabled = true;
            payBtn.innerText = 'Initializing...';

            fetch("{{ route('subscription.razorpay.initiate') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    plan_id: planId,
                    duration_id: durationId,
                    coupon_code: couponCode
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    couponMsg.innerText = data.message || 'Error';
                    couponMsg.className = "text-[11px] mt-2 text-red-500 block font-bold";
                    payBtn.disabled = false;
                    payBtn.innerText = 'Complete Subscription';
                    return;
                }

                const options = {
                    key: data.key,
                    amount: data.amount,
                    currency: "INR",
                    name: "{{ config('app.name') }}",
                    description: data.description,
                    order_id: data.order_id,
                    handler: function (response) {
                        fetch("{{ route('subscription.razorpay.verify') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                ...response,
                                plan_id: planId,
                                duration_id: durationId,
                                coupon_code: couponCode
                            })
                        })
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) {
                                window.location.href = "/agreement/latest";
                            } else {
                                alert(result.message);
                            }
                        });
                    },
                    modal: {
                        ondismiss: () => {
                            payBtn.disabled = false;
                            payBtn.innerText = 'Complete Subscription';
                        }
                    },
                    prefill: data.user,
                    theme: { color: "#0ea5e9" }
                };

                new Razorpay(options).open();
            });
        });

        /* ================= COUPON LOGIC (UNCHANGED) ================= */
        applyBtn.addEventListener('click', function () {
            const code = couponInput.value.trim();
            if (!code) return;

            applyBtn.disabled = true;

            fetch("{{ route('subscription.coupon.apply') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    coupon_code: code,
                    duration_id: payBtn.dataset.duration
                })
            })
            .then(res => res.json())
            .then(data => {
                applyBtn.disabled = false;
                couponMsg.classList.remove('hidden');

                if (data.success) {
                    couponMsg.innerText = data.message;
                    couponMsg.className = "text-[11px] mt-2 text-emerald-600 block font-bold";
                    displayPrice.innerText = data.new_total;
                } else {
                    couponMsg.innerText = data.message;
                    couponMsg.className = "text-[11px] mt-2 text-red-500 block font-bold";
                }
            });
        });

    });
</script>
@endsection

