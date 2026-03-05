<div id="userSidePanel" class="fixed inset-0 overflow-hidden z-[100] hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 overflow-hidden">
        <div onclick="closeUserPanel()" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>

        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div class="pointer-events-auto w-screen max-w-md transform transition duration-500 ease-in-out sm:duration-700">
                <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-2xl">
                    <div class="bg-slate-900 px-6 py-8">
                        <div class="flex items-start justify-between">
                            <h2 class="text-xl font-black text-white uppercase tracking-tight" id="panel-title">Customer Profile</h2>
                            <button onclick="closeUserPanel()" class="text-slate-400 hover:text-white transition">
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div id="panel-avatar" class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-white text-2xl font-black"></div>
                            <div>
                                <p id="panel-name" class="text-lg font-bold text-white"></p>
                                <span id="panel-status-badge"></span>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex-1 px-6 py-8">
                        <div class="space-y-6">
                            {{-- Identity Section --}}
                            <section>
                                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Contact Information</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Email Address</p>
                                        <p id="panel-email" class="text-sm font-black text-slate-900"></p>
                                    </div>
                                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Phone Number</p>
                                        <p id="panel-phone" class="text-sm font-black text-slate-900"></p>
                                    </div>
                                </div>
                            </section>

                            {{-- System Info --}}
                            <section>
                                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Account Details</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="border-l-4 border-blue-500 pl-4 py-1">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Internal ID</p>
                                        <p id="panel-id" class="text-sm font-black text-slate-900"></p>
                                    </div>
                                    <div class="border-l-4 border-emerald-500 pl-4 py-1">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Joined Date</p>
                                        <p id="panel-joined" class="text-sm font-black text-slate-900"></p>
                                    </div>
                                </div>
                            </section>

                            {{-- Deleted Info (Hidden by default) --}}
                            <section id="deleted-section" class="hidden">
                                <h3 class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-4">Archive Details</h3>
                                <div class="bg-rose-50 p-4 rounded-xl border border-rose-100">
                                    <p class="text-[10px] font-bold text-rose-400 uppercase mb-1">Reason for Deactivation</p>
                                    <p id="panel-reason" class="text-sm font-bold text-rose-900 italic"></p>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>