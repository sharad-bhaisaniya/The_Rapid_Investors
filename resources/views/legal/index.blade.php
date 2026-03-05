@extends('layouts.user')

@section('content')
    @php
        use App\Models\PolicyMaster;

        $allPolicies = PolicyMaster::where('is_enabled', 1)
            ->with(['activeContent'])
            ->orderBy('id')
            ->get();

        // Default selection: Search for Privacy Policy slug, fallback to first
        $defaultPolicy = $allPolicies->where('slug', 'privacy-policy')->first() ?? $allPolicies->first();
        
        // Custom SVG icons for a professional Fintech look
        $icons = [
            'privacy-policy' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'terms-of-service' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'risk-disclosure' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'aml-policy' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            'refund-policy' => 'M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z',
        ];
    @endphp

    <div id="legal-center-root" class="min-h-screen bg-[#FAFAFB] font-sans antialiased selection:bg-indigo-100 selection:text-indigo-900">
        
        <!-- Premium Centered Badge Header (Hidden on Print) -->
        <header class="bg-white border-b border-slate-100 pt-28 pb-12 print:hidden">
            <div class="max-w-7xl mx-auto px-6 text-center">
                <div class="flex justify-center mb-5">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-indigo-100 bg-white text-indigo-600 shadow-sm ring-2 ring-indigo-50/50">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-indigo-600"></span>
                        </span>
                        <span class="text-[9px] font-bold uppercase tracking-[0.2em]">Regulatory & Compliance</span>
                    </div>
                </div>
                
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">Legal Center</h1>
                <p class="max-w-xl mx-auto text-slate-500 text-base leading-relaxed mb-6">
                    Review our official governing documents, regulatory disclosures, and institutional policies designed to protect your assets and data.
                </p>
                
                <div class="flex justify-center">
                    <a href="mailto:support@bharatstockmarketresearch.com" class="px-6 py-2.5 bg-blue-900 text-white text-[13px] font-bold rounded-xl hover:bg-blue-800 transition-all shadow-lg shadow-blue-900/10">
                        Contact Compliance
                    </a>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-6 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                <!-- Sticky Navigation Sidebar (Hidden on Print) -->
                <aside class="lg:col-span-3 print:hidden">
                    <div class="sticky top-10 space-y-1">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 px-3">Official Registry</p>
                        <nav class="flex flex-col gap-1">
                            @foreach ($allPolicies as $policy)
                                <button type="button" 
                                    data-target="{{ $policy->slug }}"
                                    class="policy-tab group flex items-center gap-3 w-full px-4 py-3 rounded-xl text-[13px] font-bold transition-all duration-200
                                    {{ $policy->id === $defaultPolicy->id ? 'bg-white text-indigo-700 active-tab shadow shadow-slate-200/50 ring-1 ring-slate-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                    
                                    <svg class="w-3.5 h-3.5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$policy->slug] ?? 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' }}"></path>
                                    </svg>
                                    
                                    <span class="truncate">{{ $policy->name }}</span>
                                    
                                    <div class="ml-auto w-1 h-1 rounded-full {{ $policy->id === $defaultPolicy->id ? 'bg-indigo-600' : 'bg-transparent' }} active-indicator"></div>
                                </button>
                            @endforeach
                        </nav>
                        
                        <div class="mt-10 pt-6 border-t border-slate-100 px-3 space-y-3">
                            <button onclick="handlePrint('single')" class="flex items-center gap-2.5 text-[10px] font-bold text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition-colors w-full group">
                                <div class="p-2 bg-slate-50 rounded-lg group-hover:bg-indigo-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </div>
                                Export Selection
                            </button>
                            <button onclick="handlePrint('all')" class="flex items-center gap-2.5 text-[10px] font-bold text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition-colors w-full group">
                                <div class="p-2 bg-slate-50 rounded-lg group-hover:bg-indigo-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                Full Policy Audit
                            </button>
                        </div>
                    </div>
                </aside>

                <!-- Policy Content Area -->
                <div class="lg:col-span-9 print:col-span-12">
                    <div class="bg-white rounded-[2rem] p-8 md:p-16 border border-slate-100 shadow-sm min-h-[800px] print:border-none print:shadow-none print:p-0">
                        @foreach ($allPolicies as $policy)
                            <div id="{{ $policy->slug }}" 
                                 class="policy-content transition-all duration-300 
                                 {{ $policy->id === $defaultPolicy->id ? 'block opacity-100 translate-y-0 active-policy' : 'hidden opacity-0 translate-y-4' }}
                                 print:page-break-after-always">
                                
                                <!-- Document Print Heading -->
                                <div class="mb-10 print:mb-8 print:border-b-2 print:border-black print:pb-6">
                                    <div class="flex items-center gap-2 text-indigo-600 font-bold text-[9px] tracking-widest uppercase mb-3 print:hidden">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        Compliance Verified
                                    </div>
                                    <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 tracking-tight mb-5 leading-[1.2] print:text-2xl print:mb-4">
                                        <span class="hidden print:block print:text-[9pt] print:uppercase print:font-bold print:text-slate-500 print:mb-2 print:tracking-[0.2em]">Official Policy Statement</span>
                                        {{ $policy->name }}
                                    </h2>
                                    <div class="flex items-center gap-4 text-[13px] font-semibold text-slate-400 print:text-slate-700 print:text-[10pt]">
                                        <span>Revised: <strong class="text-slate-600 print:text-black">{{ $policy->updated_at->format('M d, Y') }}</strong></span>
                                        <div class="w-1 h-1 rounded-full bg-slate-200 print:bg-slate-400"></div>
                                        <span>Ref: BSM-{{ strtoupper(substr($policy->slug, 0, 4)) }}</span>
                                    </div>
                                </div>

                                <article class="policy-editor-content prose prose-slate max-w-none print:prose-sm print:max-w-full">
                                    {!! optional($policy->activeContent)->content ?? '<div class="text-center py-20 text-slate-300 font-bold italic">Document content loading...</div>' !!}
                                </article>

                                <!-- Professional Print Footer -->
                                <div class="mt-20 pt-10 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6 print:mt-12 print:pt-6">
                                    <div class="text-slate-400 text-[10px] font-medium italic print:text-slate-700 print:not-italic print:text-[9pt]">
                                        © {{ date('Y') }} Bharat Stock Market Research (SEBI Reg: INH000023728).
                                    </div>
                                    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="flex items-center gap-2.5 text-[9px] font-bold text-indigo-600 uppercase tracking-widest hover:translate-y-[-1px] transition-transform print:hidden">
                                        Return to Start <span class="text-base">↑</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </main>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; font-size: 15px; }

        .policy-editor-content {
            font-size: 1rem;
            line-height: 1.8;
            color: #334155;
        }

        .policy-editor-content h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 3rem;
            margin-bottom: 1.25rem;
            letter-spacing: -0.02em;
        }

        .policy-editor-content h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
        }

        .policy-editor-content table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 2rem 0;
            border: 1px solid #f1f5f9;
            border-radius: 1rem;
            overflow: hidden;
        }

        .policy-editor-content th {
            background-color: #f8fafc;
            padding: 1rem;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            text-align: left;
        }

        .policy-editor-content td {
            padding: 1rem;
            border-bottom: 1px solid #f8fafc;
            font-size: 0.9375rem;
        }

        @media print {
            .main-header, 
            header.w-full.fixed.top-0,
            .mobile-menu,
            #mobile-menu,
            footer,
            #footer,
            [aria-label="Main header"],
            .print\:hidden { 
                display: none !important; 
                height: 0 !important;
                visibility: hidden !important;
                position: fixed !important;
                top: -9999px !important;
            }
            
            #legal-center-root { background: white !important; padding: 0 !important; margin: 0 !important; }
            
            .print-single .policy-content:not(.active-policy) { display: none !important; }

            .print-all .policy-content {
                display: block !important;
                opacity: 1 !important;
                transform: none !important;
                page-break-after: always !important;
                padding-top: 40pt !important;
            }
            
            .print-all .policy-content:first-child { padding-top: 0 !important; }

            .lg\:col-span-9 { width: 100% !important; border: none !important; padding: 0 !important; margin: 0 !important; }
            .policy-editor-content { font-size: 10pt !important; line-height: 1.5 !important; color: black !important; }
            
            h2, h3, p, strong, td, th { color: black !important; }
            * { color: black !important; background: transparent !important; box-shadow: none !important; border-color: #ddd !important; }
        }
    </style>

    <script>
        function handlePrint(mode) {
            const root = document.getElementById('legal-center-root');
            if (mode === 'single') {
                root.classList.add('print-single');
                root.classList.remove('print-all');
            } else {
                root.classList.add('print-all');
                root.classList.remove('print-single');
            }
            setTimeout(() => { window.print(); }, 150);
        }

        function switchPolicy(slug) {
            const tabs = document.querySelectorAll('.policy-tab');
            const contents = document.querySelectorAll('.policy-content');

            tabs.forEach(t => {
                t.classList.remove('bg-white', 'text-indigo-700', 'active-tab', 'shadow', 'ring-1', 'ring-slate-200');
                t.classList.add('text-slate-500', 'hover:bg-slate-50');
                const ind = t.querySelector('.active-indicator');
                if(ind) ind.classList.replace('bg-indigo-600', 'bg-transparent');
            });

            contents.forEach(c => {
                c.classList.add('hidden', 'opacity-0', 'translate-y-4');
                c.classList.remove('block', 'opacity-100', 'translate-y-0', 'active-policy');
            });

            const activeTab = document.querySelector(`[data-target="${slug}"]`);
            if(activeTab) {
                activeTab.classList.add('bg-white', 'text-indigo-700', 'active-tab', 'shadow', 'ring-1', 'ring-slate-200');
                activeTab.classList.remove('text-slate-500', 'hover:bg-slate-50');
                const ind = activeTab.querySelector('.active-indicator');
                if(ind) ind.classList.replace('bg-transparent', 'bg-indigo-600');
                activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }

            const targetContent = document.getElementById(slug);
            if(targetContent) {
                targetContent.classList.remove('hidden');
                setTimeout(() => {
                    targetContent.classList.add('block', 'opacity-100', 'translate-y-0', 'active-policy');
                    targetContent.classList.remove('opacity-0', 'translate-y-4');
                }, 20);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        document.querySelectorAll('.policy-tab').forEach(tab => {
            tab.addEventListener('click', () => switchPolicy(tab.dataset.target));
        });

        window.onload = () => { switchPolicy('{{ $defaultPolicy->slug }}'); };
    </script>
@endsection