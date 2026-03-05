 {{-- Message Campaigns show here  --}}

           

            @auth
                    @php
                        $userId = auth()->id();
                        $activeCampaigns = \App\Models\MasterNotification::where('type', 'campaign')
                            ->where(function ($q) use ($userId) {
                                $q->where('is_global', true)->orWhere('user_id', $userId);
                            })
                            ->whereDoesntHave('reads', function ($q) use ($userId) {
                                $q->where('user_id', $userId);
                            })
                            ->orderByDesc('id')
                            ->limit(10)
                            ->get()
                            ->map(function ($c) {
                                return [
                                    'id' => $c->id,
                                    'title' => $c->title,
                                    'message' => $c->message,
                                    // Pulling the full description/detail here
                                    'description' => $c->data['detail'] ?? $c->description ?? $c->content ?? '',
                                    'image' => $c->data['image'] ?? $c->image ?? null,
                                ];
                            })
                            ->toArray();
                    @endphp

                    <script>
                        window.ALL_CAMPAIGNS = @json($activeCampaigns);
                    </script>

                    <div id="campaign-bell-container" class="fixed top-5 right-5 z-[60] hidden">
                        <button onclick="openCampaignDetails()"
                            class="relative flex items-center justify-center w-12 h-12 bg-yellow-400 rounded-full shadow-lg hover:bg-yellow-500 transition-all animate-vibrate">
                            <i class="fa-solid fa-bell text-white text-xl"></i>
                            <span class="absolute top-0 right-0 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                        </button>
                    </div>

                    <div id="campaign-modal-overlay" class="fixed right-6 z-[100] hidden flex items-center justify-center  p-2">
                        
                        <div id="campaign-toast"
                            class="relative w-full max-w-[450px] overflow-hidden rounded-[2rem] bg-white shadow-2xl transition-all duration-500 transform scale-95 border border-white/20">

                            <div id="campaign-image-wrapper" class="relative h-48 w-full overflow-hidden bg-slate-900 hidden">
                                <img id="campaign-image" class="h-full w-full object-cover opacity-90" src="" alt="">
                                <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
                                
                                <button onclick="closeCampaignToast()"
                                    class="absolute top-4 right-4 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/20 text-white backdrop-blur hover:bg-red-500">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div id="campaign-content-wrapper" class="relative px-6 ">
                                
                                <button id="close-btn-no-img" onclick="closeCampaignToast()" class="absolute top-5 right-5 text-slate-300 hover:text-red-500 hidden">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>

                                <div id="campaign-icon" class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-400 shadow-lg ring-4 ring-white mb-6">
                                    <i class="fa-solid fa-bolt-lightning text-white text-xl"></i>
                                </div>

                                <div class="space-y-3">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-blue-600">Announcement</span>
                                    <h4 id="campaign-title" class="text-2xl font-black leading-tight text-slate-900"></h4>
                                    
                                    <p id="campaign-message" class="text-sm font-bold text-slate-700 italic border-l-4 border-yellow-400 pl-3 mb-4"></p>

                                    <div class="max-h-[160px] overflow-y-auto pr-2 custom-scrollbar">
                                        <p id="campaign-description" class="text-[14px] leading-relaxed text-slate-500 whitespace-pre-line"></p>
                                    </div>
                                </div>

                                <button onclick="closeCampaignToast()"
                                    class="group mt-8 flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-900 py-4 text-[11px] font-bold uppercase tracking-widest text-white shadow-xl hover:bg-blue-600 transition-all active:scale-95">
                                    <span>Got it, Thanks!</span>
                                    <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                                </button>

                                <button onclick="closeAllCampaigns()" class="mt-4 w-full text-[10px] font-bold uppercase text-slate-400 hover:text-red-500">
                                    Dismiss all
                                </button>
                            </div>
                        </div>
                    </div>

                    <style>
                        @keyframes vibrate { 0%, 100% { transform: rotate(0deg); } 20% { transform: rotate(15deg); } 40% { transform: rotate(-15deg); } 60% { transform: rotate(10deg); } 80% { transform: rotate(-10deg); } }
                        .animate-vibrate { animation: vibrate 0.6s both infinite; animation-delay: 1.5s; }
                        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
                        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
                    </style>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const bell = document.getElementById('campaign-bell-container');
                            const overlay = document.getElementById('campaign-modal-overlay');
                            const toast = document.getElementById('campaign-toast');
                            const title = document.getElementById('campaign-title');
                            const message = document.getElementById('campaign-message');
                            const description = document.getElementById('campaign-description');
                            const image = document.getElementById('campaign-image');
                            const imageWrap = document.getElementById('campaign-image-wrapper');
                            const icon = document.getElementById('campaign-icon');
                            const closeNoImg = document.getElementById('close-btn-no-img');

                            let pendingCampaigns = [];
                            let activeCampaign = null;

                            if (Array.isArray(window.ALL_CAMPAIGNS) && window.ALL_CAMPAIGNS.length) {
                                pendingCampaigns = [...window.ALL_CAMPAIGNS];
                                showNextCampaign();
                            }

                            // Realtime listening
                            if (typeof Echo !== 'undefined') {
                                Echo.channel('public-notifications').listen('.master.notification', (data) => {
                                    if (data.type !== 'campaign') return;
                                    pendingCampaigns.push({
                                        id: data.id,
                                        title: data.title,
                                        message: data.message,
                                        description: data.data?.detail || data.description || data.content || '',
                                        image: data.data?.image || data.image || null
                                    });
                                    if (!activeCampaign) showNextCampaign();
                                });
                            }

                            function showNextCampaign() {
                                if (!pendingCampaigns.length) return;
                                activeCampaign = pendingCampaigns[0];

                                title.innerText = activeCampaign.title || 'Announcement';
                                message.innerText = activeCampaign.message || '';
                                description.innerText = activeCampaign.description || '';
                               
                                if (activeCampaign.image) {

                                // 🔄 RESET (jo else me remove kiya tha)
                                image.removeAttribute('hidden');
                                image.classList.remove('hidden');

                                imageWrap.classList.remove(
                                    'bg-gradient-to-br',
                                    'from-blue-300',
                                    'via-blue-300',
                                    'to-blue-400'
                                );

                                // 🖼️ SET IMAGE
                                image.setAttribute('src', activeCampaign.image);

                                imageWrap.classList.remove('hidden');

                                icon.classList.add('absolute', '-top-8', 'left-6');
                                closeNoImg.classList.add('hidden');
                            } else {          
                                    image.src = '';
                                    image.removeAttribute('src');         
                                    image.classList.add('hidden');         

                                    imageWrap.classList.remove('hidden');  
                                    imageWrap.classList.add(
                                        'bg-gradient-to-br',
                                        'from-blue-300',
                                        'via-blue-300',
                                        'to-blue-400'
                                    );

                                    icon.classList.remove('absolute', '-top-8', 'left-6');
                                }
                             
                                bell.classList.remove('hidden');
                            }

                            window.openCampaignDetails = function() {
                                overlay.classList.remove('hidden');
                                setTimeout(() => toast.classList.add('scale-100'), 10);
                            };

                            window.closeCampaignToast = function() {
                                if (!activeCampaign) return;
                                logCampaign(activeCampaign.id);
                                pendingCampaigns.shift();
                                activeCampaign = null;
                                overlay.classList.add('hidden');
                                toast.classList.remove('scale-100');
                                if (pendingCampaigns.length) {
                                    setTimeout(showNextCampaign, 400);
                                    setTimeout(openCampaignDetails, 500);
                                } else {
                                    bell.classList.add('hidden');
                                }
                            };

                            window.closeAllCampaigns = function() {
                                pendingCampaigns.forEach(c => logCampaign(c.id));
                                pendingCampaigns = [];
                                activeCampaign = null;
                                overlay.classList.add('hidden');
                                bell.classList.add('hidden');
                            };

                            function logCampaign(id) {
                                fetch('/campaign/mark-as-seen', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify({ campaign_id: id })
                                }).catch(() => {});
                            }
                        });
                    </script>
            @endauth

            {{-- END message campaign --}}
