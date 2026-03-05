    <!-- Header -->
    <style>
    #notificationBell {
        color: #0591b2;
    }
</style>
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">
        <div>
            <h1 class="text-lg font-semibold text-slate-800">{{ $pageTitle ?? 'Dashboard' }}</h1>
            <p class="text-xs text-[#0591b2]">Quick overview of your application</p>
        </div>

        <div class="flex items-center space-x-4">
            <!-- <a href="/admin/tips"
                class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-slate-600
                    border border-slate-300 rounded-lg
                    hover:bg-slate-100 hover:text-slate-900
                    transition duration-200">
                Tips
            </a>

            <a href="/listUser"
                class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-slate-600
                    border border-slate-300 rounded-lg
                    hover:bg-slate-100 hover:text-slate-900
                    transition duration-200">
                Customers
            </a> -->


            <div class="relative w-64" id="global-search-wrapper">
                <div class="relative">
                    <input type="text" id="admin-search-input" placeholder="Search pages..."
                        class="w-full pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span class="absolute inset-y-0 left-2 flex items-center text-slate-400 text-xs">🔍</span>
                </div>

                <div id="search-results"
                    class="hidden absolute top-full left-0 w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-xl z-50 max-h-60 overflow-y-auto">
                </div>
            </div>

            <!-- NOTIFICATION -->
            <div class="relative">
                <button id="notificationBell"
                    class="relative inline-flex items-center justify-center h-9 w-9 rounded-full bg-slate-100 text-slate-600">
                    <span class=" flex-shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                         stroke="#0591b2" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                    </svg>
                </span>
                    <span id="notificationCount"
                        class="hidden absolute -top-1 -right-1 h-4 w-4 bg-[#0591b2] text-[10px] text-white rounded-full flex items-center justify-center">
                    </span>
                </button>

                <div id="notificationDropdown"
                    class="hidden absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-lg shadow-lg z-50">

                    <div class="px-4 py-2 border-b font-semibold text-sm">Notifications</div>

                    <div id="notificationList" class="max-h-72 overflow-y-auto"></div>

                        <a href="/admin/notifications/" 
                class="block border-t border-slate-100 py-2.5 text-center text-[11px] font-bold text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 uppercase tracking-tight">
                    View All Notifications
                </a>
                </div>
            </div>


            <!-- USER -->

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="flex items-center focus:outline-none hover:opacity-80 transition-opacity">
                    <div
                        class="h-10 w-10 rounded-full border-2 border-slate-200 overflow-hidden flex items-center justify-center bg-slate-200 shadow-sm">
                        @if (Auth::user()->getFirstMediaUrl('profile_images'))
                            <img src="{{ Auth::user()->getFirstMediaUrl('profile_images') }}" alt="{{ Auth::user()->name }}"
                                class="h-full w-full object-cover">
                        @else
                            <span class="text-sm font-bold text-slate-600">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                </button>

                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 z-[100] overflow-hidden">

                    <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                        <p class="text-sm font-bold text-[#0591b2] truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ Auth::user()->email }}</p>
                    </div>

                    <div class="p-2 grid grid-cols-3 gap-1 border-b border-slate-100 bg-white">
                        <a href="{{ route('users.list') }}" title="Users"
                            class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-blue-50 text-[#0591b2] hover:text-blue-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </a>

                        <a href="#" title="Messages"
                            class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-green-50 text-[#0591b2] hover:text-green-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" id="dropdown-logout" class="m-0">
                            @csrf
                            <button type="submit" title="Logout"
                                class="w-full flex flex-col items-center justify-center p-2 rounded-lg hover:bg-red-50 text-[#0591b2] hover:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <a href="#"
                        class="block px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 hover:text-[#0591b2] transition-colors">View Profile
                        Settings</a>
                </div>
            </div>
        </div>
    </header>





    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ================= CONFIG ================= */
        const API_URL  = '/announcements/fetch';          // SAME backend
        const READ_URL = '/announcements/read/';          // SAME backend

        /* ================= ELEMENTS ================= */
        const bell     = document.getElementById('notificationBell');
        const dropdown = document.getElementById('notificationDropdown');
        const badge    = document.getElementById('notificationCount');
        const list     = document.getElementById('notificationList');

        let unreadCount = 0;

        /* ================= TOGGLE ================= */
        bell.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
            fetchNotifications();
        });

        document.addEventListener('click', function () {
            dropdown.classList.add('hidden');
        });



        /* ================= BADGE ================= */
        function updateBadge() {
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        /* ================= RENDER ================= */      
        function renderNotification(n) {
                const iconMap = {
                    'follow up tips': '🔄',
                    'tips': '💡',
                    'announcement': '📢',
                    'chat': '💬',
                    'ticket pop up': '🎫',
                    'message campaign': '✉️',
                    'default': '🔔'
                };

                const typeKey = n.type ? n.type.toLowerCase() : 'default';
                const icon = iconMap[typeKey] || iconMap.default;
                
                let displayMessage = n.message;
                try {
                    if (typeof n.message === 'string' && n.message.startsWith('{')) {
                        const parsed = JSON.parse(n.message);
                        displayMessage = parsed.update_title || parsed.message || n.message;
                    }
                } catch (e) { displayMessage = n.message; }

                // Use n.time_ago from your backend or format it here
                const timeText = n.time_ago || ''; 

            const html = `
                <div class="px-4 py-2.5 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors"
                    onclick="handleAdminRead(${n.tracking_id}, '${n.url}')">
                    
                    <div class="flex items-start gap-3">
                        <span class="text-sm mt-0.5 flex-shrink-0">${icon}</span>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center gap-2">
                                <h4 class="text-[12px] font-bold text-slate-800 truncate leading-none">
                                    ${n.title}
                                </h4>
                                <span class="text-[9px] font-medium text-slate-400 whitespace-nowrap">
                                    ${timeText}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 truncate mt-1">
                                ${displayMessage}
                            </p>
                        </div>

                        <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></div>
                    </div>
                </div>
            `;

            // CHANGED: Use 'beforeend' because your PHP '->latest()' 
            // puts the newest record at index 0 of the array.
            list.insertAdjacentHTML('beforeend', html);
        }

        
        /* ================= UPDATED FETCH TO HANDLE LABEL ================= */
        function fetchNotifications() {
            fetch(API_URL)
                .then(r => r.json())
                .then(data => {
                    list.innerHTML = '';
                    unreadCount = data.count || 0;
                    
                    updateBadge();
                    const countLabel = document.getElementById('unreadCountLabel');
                    if(countLabel) countLabel.textContent = `${unreadCount} New`;

                    if (!data.notifications || !data.notifications.length) {
                        list.innerHTML = `
                            <div class="flex flex-col items-center justify-center py-12 px-4">
                                <span class="text-4xl mb-2">🍃</span>
                                <p class="text-xs text-slate-400 font-medium">All caught up!</p>
                            </div>`;
                        return;
                    }

                    data.notifications.forEach(n => renderNotification(n));
                });
        }

        /* ================= CLICK / MARK READ ================= */
        window.handleAdminRead = function (id, url) {

            fetch(READ_URL + id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            window.location.href = url;
        };

        /* ================= REALTIME ================= */
        const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }
        });

        /* 🔒 PRIVATE ADMIN CHANNEL */
        pusher.subscribe('user.{{ auth()->id() }}')
            .bind('master.notification', function (data) {
                fetchNotifications();
            });

        /* 🌍 GLOBAL CHANNEL */
        pusher.subscribe('public-notifications')
            .bind('master.notification', function () {
                fetchNotifications();
            });

        /* ================= INITIAL ================= */
        fetchNotifications();

    });
    </script>
