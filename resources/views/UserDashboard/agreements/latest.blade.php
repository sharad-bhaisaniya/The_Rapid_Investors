@extends('layouts.userdashboard')

@section('content')
<div
    x-data="{
        open: false,
        activePdf: null,
        activeTitle: '',
    }"
    class="max-w-6xl mx-auto py-8 px-4"
>

{{-- Header --}}
<div class="mb-8 flex items-start justify-between gap-6">

    {{-- Left: Title --}}
    <div class="flex items-start gap-4">
        <div>
              {{-- Back (previous URL) --}}
        <a
            href="{{ url()->previous() }}"
            title="Go back"
            class="inline-flex items-center justify-center w-10 h-10 rounded-lg
                   border border-slate-200 bg-slate-100 text-slate-600
                   hover:bg-slate-50 hover:text-sky-700 transition"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
        </a>

        </div>
        <div>
              <h1 class="text-2xl md:text-3xl font-extrabold text-blue-900 tracking-tight">
            
      
            My Agreements
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            View all your signed agreements. The most recent agreement appears first.
        </p>

        </div>
      
    </div>

 
</div>
    {{-- TABLE --}}
    <div class="bg-white rounded-xl shadow border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-sky-50 border-b">
                <tr class="text-left text-sky-900 font-bold">
                    <th class="px-4 py-3">Agreement No</th>
                    <th class="px-4 py-3">Invoice No</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Signed At</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($agreements as $agreement)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-sky-900">
                                <div class="flex items-center gap-2">
                                    <span>{{ $agreement->agreement_number }}</span>

                                    @if($loop->first)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                                    bg-emerald-100 text-emerald-700 uppercase">
                                            Latest
                                        </span>
                                    @endif
                                </div>
                            </td>

                        <td class="px-4 py-3">
                            {{ $agreement->invoice_number }}
                        </td>
                        <td class="px-4 py-3 font-mono text-sky-900">
                            ₹{{ number_format($agreement->invoice->amount, 2) }}
                            </td>

                        <td class="px-4 py-3">
                            {{ $agreement->signed_at?->format('d M Y, h:i A') }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-700 uppercase">
                                {{ $agreement->status }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">

                                {{-- View in Modal --}}
                <button
                    @click="
                        open = true;
                        activePdf = '{{ route('agreement.pdf', $agreement->id) }}';
                        activeTitle = '{{ $agreement->agreement_number }}';
                    "
                    class="bg-blue-800 hover:bg-blue-900 text-white px-4 py-2 rounded-lg font-semibold text-sm"
                >
                    View 
                </button>

                                {{-- DOWNLOAD --}}
                                <a
                                    href="{{ route('agreement.pdf', $agreement->id) }}"
                                    target="_blank"
                                    class="px-3 py-2 border rounded-md text-xs font-bold hover:bg-slate-100"
                                >
                                    ⬇ Download
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-500 font-semibold">
                            No agreements found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Back --}}
    <div class="pt-6">
        <a href="{{ route('user.dashboard') }}"
           class="text-sm font-semibold text-slate-600 hover:text-sky-700">
            ← Back to Dashboard
        </a>
    </div>

    {{-- ================= MODAL ================= --}}
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
      >
        <div class="bg-white w-[90%] h-[90%] rounded-xl shadow-lg overflow-hidden">

            {{-- Modal Header --}}
            <div class="flex justify-between items-center px-4 py-3 border-b">
                <h3 class="font-bold text-blue-900">
                    Agreement PDF — <span x-text="activeTitle"></span>
                </h3>
                <button
                    @click="open = false"
                    class="text-gray-500 hover:text-red-600 text-xl font-bold"
                >
                    ✕
                </button>
            </div>

            {{-- PDF --}}
            <iframe
                :src="activePdf"
                class="w-full h-full"
                frameborder="0"
            ></iframe>

        </div>
    </div>

</div>
@endsection