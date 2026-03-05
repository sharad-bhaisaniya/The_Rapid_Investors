@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
.font-inter { font-family: 'Inter', sans-serif; }
.table-container::-webkit-scrollbar { height: 6px; }
.table-container::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen font-inter">

{{-- Header --}}
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900">Refund Ledger</h2>
        <p class="text-slate-500 text-sm mt-1">
            Completed refunds with transaction proof
        </p>
    </div>

    <div class="bg-emerald-50 px-4 py-2 rounded-lg border border-emerald-100 text-center">
        <p class="text-[10px] uppercase font-bold text-emerald-400">Total Refunded</p>
        <p class="text-lg font-bold text-emerald-700">{{ $refunds->total() }}</p>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
<div class="table-container overflow-x-auto">

<table class="min-w-full text-sm">
<thead>
<tr class="bg-slate-50 border-b border-slate-200 text-slate-600">
    <th class="p-4 text-left text-[10px] font-bold uppercase">User</th>
    <th class="p-4 text-left text-[10px] font-bold uppercase">Subscription</th>
    <th class="p-4 text-left text-[10px] font-bold uppercase">Transaction</th>
    <th class="p-4 text-left text-[10px] font-bold uppercase">Amount</th>
    <th class="p-4 text-left text-[10px] font-bold uppercase">Reason</th>
    <th class="p-4 text-center text-[10px] font-bold uppercase">Proof</th>
    <th class="p-4 text-left text-[10px] font-bold uppercase">Refunded At</th>
    <th class="p-4 text-right text-[10px] font-bold uppercase">View</th>
</tr>
</thead>

<tbody class="divide-y divide-slate-100">
@forelse($refunds as $refund)

<tr class="hover:bg-slate-50 transition">
{{-- User --}}
<td class="p-4">
    <p class="font-semibold text-slate-800">{{ $refund->user->name }}</p>
    <p class="text-[11px] text-slate-400">UID #{{ $refund->user_id }}</p>
</td>

{{-- Subscription --}}
<td class="p-4">
    <p class="text-xs font-semibold">#{{ $refund->subscription->id }}</p>
    <p class="text-[11px] text-slate-400">
        {{ $refund->subscription->start_date->format('d M') }} –
        {{ $refund->subscription->end_date->format('d M Y') }}
    </p>
</td>

{{-- Transaction --}}
<td class="p-4">
    <p class="text-xs font-mono">{{ $refund->transaction_id }}</p>
    <p class="text-[10px] uppercase text-slate-400">
        {{ $refund->payment_gateway }}
    </p>
</td>

{{-- Amount --}}
<td class="p-4 font-bold text-slate-900">
    ₹{{ number_format($refund->refund_amount, 2) }}
</td>

{{-- Reason --}}
<td class="p-4 max-w-[220px]">
    <p class="text-xs text-slate-600 italic">
        "{{ \Illuminate\Support\Str::limit($refund->refund_reason, 60) }}"
    </p>
</td>

{{-- Proof --}}
<td class="p-4 text-center">
@if($refund->refund_proof_image)
    <a href="{{ asset('storage/'.$refund->refund_proof_image) }}" target="_blank">
        <img src="{{ asset('storage/'.$refund->refund_proof_image) }}"
             class="h-10 w-10 rounded-lg border object-cover hover:scale-105 transition">
    </a>
@else
    <span class="text-[11px] text-slate-400 italic">N/A</span>
@endif
</td>

{{-- Refunded At --}}
<td class="p-4 text-xs text-slate-600">
    {{ $refund->refunded_at->format('d M Y, h:i A') }}
</td>

{{-- View --}}
<td class="p-4 text-right">
    <a href="{{ route('admin.refund.show', $refund->id) }}"
       class="text-xs font-bold text-indigo-600 hover:underline">
        View
    </a>
</td>
</tr>

@empty
<tr>
<td colspan="8" class="p-10 text-center text-slate-400">
    No refunded records found
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($refunds->hasPages())
<div class="px-4 py-4 bg-slate-50 border-t">
    {{ $refunds->links('pagination.dots') }}
</div>
@endif

</div>
</div>
@endsection