@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white p-6 font-sans">


    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Performance Analytics</h1>
            <p class="text-sm text-slate-500">Real-time accuracy tracking and trade growth metrics</p>
        </div>

        <button onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
            <i class="fa-solid fa-file-arrow-down text-slate-500"></i>
            Export PDF
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-4 mb-6">

        {{-- Accuracy --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <i class="fa-solid fa-chart-line text-sm"></i>
                </div>

                <span class="rounded-full px-2 py-0.5 text-[11px] font-medium
                    {{ $growthRate >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                    {{ $growthRate >= 0 ? '+' : '' }}{{ $growthRate }}%
                </span>
            </div>

            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Current Accuracy
            </p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                {{ $accuracy }}%
            </h2>
        </div>

        {{-- Targets --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                <i class="fa-solid fa-bullseye text-sm"></i>
            </div>

            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Targets Hit
            </p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                {{ $t1Hits + $t2Hits }}
            </h2>
        </div>

        {{-- SL --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600">
                <i class="fa-solid fa-shield-halved text-sm"></i>
            </div>

            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Stop Loss Hit
            </p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                {{ $slHits }}
            </h2>
        </div>

        {{-- Total Trades --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                <i class="fa-solid fa-layer-group text-sm"></i>
            </div>

            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Total Calls
            </p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                {{ $totalTrades }}
            </h2>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">

            <div class="min-w-[160px]">
                <label class="ml-1 text-[11px] font-medium uppercase text-slate-400">Segment</label>
                <select name="tip_type"
                    class="mt-1 w-full rounded-lg bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 focus:outline-none">
                    <option value="">All Segments</option>
                    <option value="equity" {{ request('tip_type') == 'equity' ? 'selected' : '' }}>Equity</option>
                    <option value="future" {{ request('tip_type') == 'future' ? 'selected' : '' }}>Future</option>
                    <option value="option" {{ request('tip_type') == 'option' ? 'selected' : '' }}>Option</option>
                </select>
            </div>

            <div class="flex min-w-[260px] gap-3">
                <div class="w-1/2">
                    <label class="ml-1 text-[11px] font-medium uppercase text-slate-400">From</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                        class="mt-1 w-full rounded-lg bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 focus:outline-none">
                </div>

                <div class="w-1/2">
                    <label class="ml-1 text-[11px] font-medium uppercase text-slate-400">To</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                        class="mt-1 w-full rounded-lg bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 focus:outline-none">
                </div>
            </div>

            <button type="submit"
                class="rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
                Update
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">

        <div class="border-b border-slate-100 px-6 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
                Trade History
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-[11px] font-medium uppercase text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Symbol</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Entry</th>
                        <th class="px-6 py-3">CMP</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">P/L</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach($tipsList as $tip)
                    @php
                        $diff = ($tip->call_type == 'Buy')
                            ? ($tip->cmp_price - $tip->entry_price)
                            : ($tip->entry_price - $tip->cmp_price);
                    @endphp

                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 text-slate-500">
                            {{ $tip->created_at->format('d M Y') }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900">{{ $tip->stock_name }}</div>
                            <div class="text-xs uppercase text-slate-400">{{ $tip->exchange }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-medium
                                {{ $tip->tip_type == 'equity'
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'bg-purple-50 text-purple-600' }}">
                                {{ ucfirst($tip->tip_type) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 font-medium text-slate-700">
                            ₹{{ $tip->entry_price }}
                        </td>

                        <td class="px-6 py-4 font-medium text-slate-700">
                            ₹{{ $tip->cmp_price }}
                        </td>

                        <td class="px-6 py-4">
                            @if(str_contains($tip->status, 'Achieved'))
                                <span class="text-emerald-600 font-medium text-sm">Target Hit</span>
                            @elseif($tip->status === 'SL-Hit')
                                <span class="text-red-600 font-medium text-sm">SL Hit</span>
                            @else
                                <span class="text-slate-400 text-sm">{{ $tip->status }}</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right font-semibold
                            {{ $diff >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
            {{ $tipsList->links() }}
        </div>
    </div>

</div>
@endsection
