@extends('layouts.app')

@section('content')

<div class="p-6 md:p-10 bg-slate-50 min-h-screen font-inter">

<div class="max-w-4xl mx-auto bg-white rounded-2xl shadow border p-8">

<h2 class="text-2xl font-extrabold mb-6 text-slate-900">
    Refund Details
</h2>

{{-- User --}}
<div class="grid md:grid-cols-2 gap-6 text-sm">
<div>
    <p class="text-slate-400 text-xs uppercase">User</p>
    <p class="font-semibold">{{ $refund->user->name }}</p>
</div>

<div>
    <p class="text-slate-400 text-xs uppercase">Subscription ID</p>
    <p class="font-semibold">#{{ $refund->subscription->id }}</p>
</div>

<div>
    <p class="text-slate-400 text-xs uppercase">Invoice</p>
    <p class="font-semibold">{{ $refund->invoice->invoice_number }}</p>
</div>

<div>
    <p class="text-slate-400 text-xs uppercase">Refunded By</p>
    <p class="font-semibold">{{ $refund->refundedBy->name ?? 'System' }}</p>
</div>
</div>

<hr class="my-6">

{{-- Transaction --}}
<div class="grid md:grid-cols-2 gap-6 text-sm">
<div>
    <p class="text-slate-400 text-xs uppercase">Transaction ID</p>
    <p class="font-mono">{{ $refund->transaction_id }}</p>
</div>

<div>
    <p class="text-slate-400 text-xs uppercase">Gateway</p>
    <p class="font-semibold uppercase">{{ $refund->payment_gateway }}</p>
</div>

<div>
    <p class="text-slate-400 text-xs uppercase">Amount</p>
    <p class="font-bold text-lg">₹{{ number_format($refund->refund_amount,2) }}</p>
</div>

<div>
    <p class="text-slate-400 text-xs uppercase">Refunded At</p>
    <p>{{ $refund->refunded_at->format('d M Y, h:i A') }}</p>
</div>
</div>

<hr class="my-6">

{{-- Notes --}}
<div class="space-y-4">
<div>
    <p class="text-slate-400 text-xs uppercase">Refund Reason (Customer)</p>
    <p class="text-sm text-slate-700">{{ $refund->refund_reason }}</p>
</div>

@if($refund->admin_note)
<div>
    <p class="text-slate-400 text-xs uppercase">Admin Note</p>
    <p class="text-sm italic text-slate-600">{{ $refund->admin_note }}</p>
</div>
@endif
</div>

{{-- Proof --}}
@if($refund->refund_proof_image)
<div class="mt-8">
    <p class="text-slate-400 text-xs uppercase mb-2">Refund Proof</p>
    <img src="{{ asset('storage/'.$refund->refund_proof_image) }}"
         class="max-w-sm rounded-xl border shadow">
</div>
@endif

<div class="mt-10">
    <a href="{{ route('admin.refund.index') }}"
       class="inline-block px-6 py-2 bg-slate-100 rounded-lg font-bold text-slate-600 hover:bg-slate-200">
        ← Back to Refund Ledger
    </a>
</div>

</div>
</div>
@endsection