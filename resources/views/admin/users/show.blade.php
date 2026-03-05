@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    {{-- Breadcrumbs & Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <nav class="flex text-xs font-bold text-gray-400 uppercase tracking-widest mb-2" aria-label="Breadcrumb">
                <a href="{{ route('users.index') }}" class="hover:text-blue-600">Customers</a>
                <span class="mx-2 text-gray-300">/</span>
                <span class="text-slate-900">User Profile</span>
            </nav>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $user->name }}</h2>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-tighter">Internal ID: {{ $user->bsmr_id }}</p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('users.list') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition">
                Back to List
            </a>
            
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- LEFT COLUMN: IDENTITY & STATUS --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Profile Card --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="h-24 bg-[#0b3186]"></div>
                <div class="px-6 pb-6 text-center">
                    <div class="relative -top-12 mb-[-3rem]">
                        @php $profileImg = $user->getFirstMediaUrl('profile_images'); @endphp
                        @if($profileImg)
                            <img src="{{ $profileImg }}" class="w-24 h-24 rounded-2xl border-4 border-white mx-auto object-cover shadow-md">
                        @else
                            <div class="w-24 h-24 rounded-2xl border-4 border-white bg-blue-50 text-blue-600 mx-auto flex items-center justify-center text-2xl font-black shadow-md">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                    <h3 class="mt-16 text-xl font-black text-slate-900">{{ $user->name }}</h3>
                    <p class="text-sm font-bold text-gray-400">{{ $user->email }}</p>
                    
                    <div class="mt-4 flex justify-center gap-2">
                        
                            <span class="px-3 py-1 bg-[#daad2075] text-green-600 rounded-full text-[9px] font-black uppercase tracking-widest">{{ $activeSubscription->plan->name ?? 'Service Plan' }}</span>
                      

                        @if($kyc && $kyc->isApproved())
                            <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-[9px] font-black uppercase tracking-widest italic">KYC Verified</span>
                        @endif
                    </div>
                </div>
                
                <div class="border-t border-gray-50 p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black text-gray-400 uppercase">Phone</span>
                        <span class="text-sm font-bold text-slate-700 font-mono">{{ $user->phone ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black text-gray-400 uppercase">Joined On</span>
                        <span class="text-sm font-bold text-slate-700">{{ $user->created_at->format('d M, Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Current Subscription Plan --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Current Subscription</h3>
                @if($activeSubscription)
                    <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100">
                        <p class="text-[10px] font-black text-blue-500 uppercase">{{ $activeSubscription->plan->name ?? 'Service Plan' }}</p>
                        <p class="text-lg font-black text-slate-900 mt-1">{{ $activeSubscription->currency }} {{ number_format($activeSubscription->amount, 2) }}</p>
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Starts</span>
                                <span class="font-bold">{{ $activeSubscription->start_date->format('d M, Y') }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Expires</span>
                                <span class="font-bold text-rose-600">{{ $activeSubscription->end_date->format('d M, Y') }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4 border-2 border-dashed border-gray-100 rounded-2xl">
                        <p class="text-xs font-bold text-gray-400 uppercase">No Active Plan</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN: KYC & HISTORY --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- KYC Details Section --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest">KYC Verification Data</h3>
                    @if($kyc)
                        <span class="px-2 py-1 bg-{{ $kyc->status_with_color['color'] }}-100 text-{{ $kyc->status_with_color['color'] }}-600 rounded text-[9px] font-black uppercase">
                            {{ $kyc->status_with_color['text'] }}
                        </span>
                    @endif
                </div>
                
                <div class="p-6">
                    @if($kyc)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Personal Info from KYC --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase block">Full Name (Aadhaar)</label>
                                    <p class="text-sm font-bold text-slate-900">{{ $kyc->full_name }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase block">Date of Birth</label>
                                    <p class="text-sm font-bold text-slate-900">{{ $kyc->date_of_birth ?? 'Not Provided' }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase block">Gender</label>
                                    <p class="text-sm font-bold text-slate-900 capitalize">{{ $kyc->gender ?? 'Not Provided' }}</p>
                                </div>
                            </div>
                            
                            {{-- IDs & Address --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase block">Aadhaar Number</label>
                                    <p class="text-sm font-bold text-slate-900 font-mono">{{ $kyc->aadhaar_number ?? 'Masked' }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase block">Permanent Address</label>
                                    <p class="text-xs font-bold text-slate-600 leading-relaxed">{{ $kyc->address ?? 'Not Available' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Technical KYC Info --}}
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase mb-4 tracking-widest">Verification Audit</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase">Document ID</p>
                                    <p class="text-[10px] font-bold text-slate-800 truncate">{{ $kyc->digio_document_id }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase">Completed At</p>
                                    <p class="text-[10px] font-bold text-slate-800">{{ $kyc->kyc_completed_at ? $kyc->kyc_completed_at->format('d/m/Y H:i') : '—' }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase">Expires At</p>
                                    <p class="text-[10px] font-bold text-slate-800">{{ $kyc->kyc_expires_at ? $kyc->kyc_expires_at->format('d/m/Y') : 'Lifetime' }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase">Reference ID</p>
                                    <p class="text-[10px] font-bold text-slate-800 truncate">{{ $kyc->reference_id }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-address-card text-gray-300 text-xl"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-400 uppercase">No KYC Verification Record Found</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Invoices --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest">Billing History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50/50 text-gray-400">
                            <tr>
                                <th class="p-4 text-left font-black uppercase tracking-widest">Invoice #</th>
                                <th class="p-4 text-left font-black uppercase tracking-widest">Date</th>
                                <th class="p-4 text-left font-black uppercase tracking-widest">Amount</th>
                                <th class="p-4 text-left font-black uppercase tracking-widest">Method</th>
                                <th class="p-4 text-right font-black uppercase tracking-widest">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($user->invoices->sortByDesc('invoice_date') as $invoice)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-bold text-slate-900">{{ $invoice->invoice_number }}</td>
                                <td class="p-4 text-gray-500">{{ $invoice->invoice_date->format('d M, Y') }}</td>
                                <td class="p-4 font-black text-slate-900">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 bg-gray-100 rounded text-[9px] font-bold uppercase">{{ $invoice->payment_gateway }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <span class="text-emerald-600 font-black uppercase tracking-tighter">Paid</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center text-gray-400 font-bold uppercase italic tracking-widest">No payment history available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection