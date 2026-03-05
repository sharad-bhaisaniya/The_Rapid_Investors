@extends('layouts.userdashboard')

@section('content')
    <div class="bg-[#f8fafc]" x-data="invoicePage()">

        <div class="max-w-7xl mx-auto bg-white rounded-[24px] border shadow-sm overflow-hidden">

            <!-- HEADER -->
            <div class="p-6 border-b flex items-center gap-3">
                <a href="{{ url('/settings') }}" class="p-2 rounded-full hover:bg-gray-100">←</a>
                <h1 class="text-lg font-bold">Payment History & Invoices</h1>
            </div>

            <!-- TABLE -->
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-3">Date</th>
                            <th class="pb-3">Plan</th>
                            <th class="pb-3">Amount</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 text-right">Invoice</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse ($subscriptions as $subscription)
                            @foreach ($subscription->invoices as $invoice)
                                @php
                                    $payload = json_encode(
                                        [
                                            'id' => $invoice->id,
                                            'number' => $invoice->invoice_number,
                                            'date' => $invoice->invoice_date->format('d M Y'),
                                            'plan' => $subscription->plan->name,

                                            // ✅ NEW: PLAN DURATION
                                            'duration' =>
                                                $subscription->duration->duration ??
                                                $subscription->duration->duration_days . ' Days',

                                            'amount' => number_format($invoice->amount, 2),
                                            'start' => $invoice->service_start_date->format('d M Y'),
                                            'end' => $invoice->service_end_date->format('d M Y'),
                                            'payment' => $invoice->payment_reference,
                                            'status' => isset($refunds[$invoice->id]) ? 'REFUNDED' : 'PAID',
                                            'refund_amount' => isset($refunds[$invoice->id])
                                                ? number_format($refunds[$invoice->id]->refund_amount, 2)
                                                : null,
                                        ],
                                        JSON_HEX_APOS | JSON_HEX_QUOT,
                                    );
                                @endphp


                                <tr class="hover:bg-gray-50">
                                    <td class="py-4">{{ $invoice->invoice_date->format('d M Y') }}</td>
                                    <td class="py-4 font-medium">{{ $subscription->plan->name }}</td>
                                    <!-- <td class="py-4 font-semibold">₹{{ number_format($invoice->amount, 2) }}</td> -->
                                    <td class="py-4">
                                            {{-- PAID AMOUNT --}}
                                            <div class="font-semibold">
                                                ₹{{ number_format($invoice->amount, 2) }}
                                            </div>

                                            {{-- REFUND AMOUNT --}}
                                            @if(isset($refunds[$invoice->id]))
                                                <div class="text-[10px] text-rose-600 font-bold mt-0.5">
                                                    Refunded: ₹{{ number_format($refunds[$invoice->id]->refund_amount, 2) }}
                                                </div>

                                                {{-- OPTIONAL: NET PAID --}}
                                                <!-- <div class="text-[10px] text-gray-500 font-semibold">
                                                    Net Paid:
                                                    ₹{{ number_format($invoice->amount - $refunds[$invoice->id]->refund_amount, 2) }}
                                                </div> -->
                                            @endif
                                    </td>
                                    <td class="py-4">
	                                        <!-- <span class="text-green-600 font-bold text-xs">PAID</span> -->
                                      @if(isset($refunds[$invoice->id]))
                                            <span class="text-rose-600 font-bold text-xs">REFUNDED</span>
                                        @else
                                            <span class="text-green-600 font-bold text-xs">PAID</span>
                                        @endif
                                    </td>
                                    
                                    <td class="py-4 text-right space-x-3">

                                        <!-- PREVIEW (SAFE) -->
                                        <button data-invoice='{{ $payload }}' @click="openInvoice($event)"
                                            class="text-blue-600 text-xs font-bold hover:underline">
                                            Preview
                                        </button>

                                        <!-- DOWNLOAD -->
                                        <!-- <a href="{{ route('invoice.download', $invoice->id) }}"
                                            class="text-gray-700 text-xs font-bold hover:underline">
                                            Download
                                        </a> -->
                                        @if(!isset($refunds[$invoice->id]))
                                            <a href="{{ route('invoice.download', $invoice->id) }}"
                                                class="text-gray-700 text-xs font-bold hover:underline">
                                                Download
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs italic">Download Unavailable
                                            </span>
                                        @endif
        
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400">
                                    No invoices found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- INVOICE PREVIEW MODAL -->
        <div x-show="showPreview" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white w-full max-w-lg rounded-2xl p-6 relative text-[11px]">

                <button @click="showPreview=false" class="absolute top-3 right-4 text-gray-400 hover:text-black">✕</button>

                <h2 class="text-lg font-black text-[#0939a4] mb-2">
                    Bharat Stock Market Research
                </h2>

                <hr class="my-3">

                <p><b>Invoice No:</b> <span x-text="invoice.number"></span></p>
                <p><b>Date:</b> <span x-text="invoice.date"></span></p>

                <hr class="my-3">

                <p class="font-bold mb-1">Bill To</p>
                <p class="text-gray-600 leading-relaxed">
                    <span class="font-semibold">{{ auth()->user()->name }}</span><br>

                    Email:
                    <span class="text-gray-800">
                        {{ auth()->user()->email }}
                    </span><br>

                    Phone:
                    <span class="text-gray-800">
                        {{ auth()->user()->phone ?? 'N/A' }}
                    </span><br>
                    {{-- 
                    <span class="text-[10px] text-gray-400">
                        User ID: {{ auth()->user()->id }}
                    </span> --}}
                </p>
                <p>
    <b>Status:</b>
    <span x-text="invoice.status"
          :class="invoice.status === 'REFUNDED' ? 'text-rose-600' : 'text-green-600'">
    </span>
</p>


                <div class="border rounded-xl mt-4 mb-3">
                    <div class="p-2 flex justify-between">
                        <div>
                            <b x-text="invoice.plan"></b>
                            <span class="text-[10px] text-gray-500 block">
                                Duration: <span x-text="invoice.duration"></span>
                            </span>
                            <span class="text-gray-500 block">
                                Service Period:
                                <span x-text="invoice.start"></span> –
                                <span x-text="invoice.end"></span>
                            </span>
                        </div>
                        <template x-if="invoice.refund_amount">
                    <p class="text-[10px] text-rose-600 font-bold mt-1">
                        Refunded: ₹<span x-text="invoice.refund_amount"></span>
                    </p>
                </template>

                        <div class="font-bold text-right">
                            ₹<span x-text="invoice.amount"></span>
                        </div>
                    </div>
                </div>


                <p class="text-[10px] text-gray-500">
                    Payment Ref: <span x-text="invoice.payment"></span><br>
                    Mode: Razorpay
                </p>

                <div class="mt-4 flex justify-end gap-3">
                    <button @click="showPreview=false" class="px-4 py-2 text-xs font-bold bg-gray-100 rounded-lg">
                        Close
                    </button>
                    
                 <!-- DOWNLOAD (ONLY IF NOT REFUNDED) -->
                <template x-if="invoice.status !== 'REFUNDED'">
                    <a :href="'/invoice/download/' + invoice.id"
                    class="px-4 py-2 text-xs font-bold bg-[#0939a4] text-white rounded-lg
                            hover:bg-[#072f82] transition">
                        Download PDF
                    </a>
                </template>

                <!-- DISABLED WHEN REFUNDED -->
                <template x-if="invoice.status === 'REFUNDED'">
                    <span
                        class="px-4 py-2 text-xs font-bold bg-gray-200 text-gray-400 rounded-lg
                            cursor-not-allowed">
                        Download Disabled
                    </span>
                </template>

                </div>
            </div>
        </div>
    </div>

    <script>
        function invoicePage() {
            return {
                showPreview: false,
                invoice: {},
                openInvoice(e) {
                    this.invoice = JSON.parse(e.target.dataset.invoice);
                    this.showPreview = true;
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
