

<style>
    /* Sidebar scrollbar styling */
    #main-sidebar nav::-webkit-scrollbar {
        width: 6px;
    }
    
    #main-sidebar nav::-webkit-scrollbar-track {
        background: transparent;
    }
    
    #main-sidebar nav::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.35);
        border-radius: 10px;
    }
    
    #main-sidebar nav {
        scrollbar-width: thin; /* Firefox */
        scrollbar-color: rgba(255, 255, 255, 0.35) transparent;
    }
</style>




 <aside
        id="main-sidebar"
        class="w-64 bg-[#0591b2] text-slate-100 flex flex-col
               h-screen overflow-hidden
               transition-all duration-300 ease-in-out">
    <div class="h-16 flex items-center justify-between px-4 border-b border-[#30beff]">
        <!--<span class="text-lg font-semibold admin-name">Metawish Admin</span>-->
        <div class="flex items-center gap-2 admin-name">
        <img
            src="{{ asset('public/assets/images/rapid-investors-logo.png') }}"
            alt="The Rapid Investors"
            class="h-16 w-auto object-contain"
        >
       
    </div>
        <button id="sidebar-toggle" class="p-1 rounded-lg hover:bg-[#30beff] transition-colors duration-200 text-sm"
            title="Toggle Sidebar">
            ←
        </button>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <a href="{{ route('dashboard') }}"
            class="flex items-center px-3 py-2 rounded-md text-white text-sm font-medium group">
            <span class="inline-block h-2 w-2 rounded-full bg-emerald-400 mr-2 flex-shrink-0"></span>
            <span class="sidebar-text">Dashboard</span>
        </a>
    

                {{-- Tickets Link --}}
        @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view tickets'))
        <a href="{{ url('admin/tickets') }}"
           class="relative flex items-center px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group">
        
            <!--<span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">-->
            <!--    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
            <!--        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"-->
            <!--            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />-->
            <!--    </svg>-->
            <!--</span>-->
             <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 5h14v4a2 2 0 010 6v4H5v-4a2 2 0 010-6V5z"/>
                    </svg>
            </span>
        
            <span class="sidebar-text">Tickets</span>
        
            {{-- 🔴 Ticket Counter --}}
            <span id="ticketCount"
                class="absolute right-2 top-2 min-w-[18px] h-[18px] px-1
                       hidden items-center justify-center
                       text-[10px] font-bold text-white
                       bg-green-500 rounded-full">
            </span>
        </a>
        @endif



        {{-- Tips Link (Single Link) --}}
        @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view tips'))
            <a href="{{ route('admin.tips.index') }}"
                class="flex items-center px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group">
                <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </span>
                <span class="sidebar-text">Tips</span>
            </a>
        @endif

        {{-- Announcements Dropdown --}}
        @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view announcements'))
            <div class="dropdown-container" data-dropdown-id="announcements" x-data="{ open: {{ request()->routeIs('admin.announcements.*') ? 'true' : 'false' }} }">
                <button @click="open = !open; toggleDropdown('announcements-menu')"
                    class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group dropdown-btn">
                    <div class="flex items-center">
                        <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                </path>
                            </svg>
                        </span>
                        <span class="sidebar-text">Announcements</span>
                    </div>
                    <span class="arrow-icon transition-transform duration-300" :class="{ 'rotate-180': open }">▼</span>
                </button>

                <div id="announcements-menu"
                    class="dropdown-content {{ request()->routeIs('admin.announcements.*') ? '' : 'hidden' }} pl-8 mt-1 space-y-1">
                    
                    {{-- All Announcements link --}}
                    <a href="{{ route('admin.announcements.index') }}"
                        class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text {{ request()->routeIs('admin.announcements.index') ? 'bg-[#30beff] text-white' : '' }}">
                        All Announcements
                    </a>

                
                </div>
            </div>
        @endif



        {{-- Logic: Show Dropdown if Super Admin OR has any 'view' permission for News or Blogs --}}
        @php
            $canViewNewsBlogs = auth()->user()->hasRole('super-admin') || 
                                auth()->user()->can('view news') || 
                                auth()->user()->can('view blogs');
        @endphp

        @if($canViewNewsBlogs)
            <div class="dropdown-container" data-dropdown-id="news-blogs" x-data="{ open: false }">
                <button @click="open = !open; toggleDropdown('news-blogs-menu')"
                    class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group dropdown-btn">
                    <div class="flex items-center">
                        <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2zM14 4v4h4" />
                            </svg>
                        </span>
                        <span class="sidebar-text">News & Blogs</span>
                    </div>
                    <span class="arrow-icon transition-transform duration-300" :class="{ 'rotate-180': open }">▼</span>
                </button>

                <div id="news-blogs-menu" class="dropdown-content hidden pl-8 mt-1 space-y-1">
       

                    {{-- News Updates Link --}}
                    @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view news'))
                        <a href="{{ route('admin.news.index') }}"
                            class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">
                            News Updates
                        </a>
                    @endif

                </div>
            </div>
        @endif


        {{-- Logic: Show Site Settings if Super Admin OR has any 'view' permission related to these modules --}}
            @php
                $canViewSiteSettings = auth()->user()->hasRole('super-admin') || 
                                    auth()->user()->can('view header menus') || 
                                    auth()->user()->can('view contact details') || 
                                    auth()->user()->can('view footer');
            @endphp


  
        {{-- Logic: Show Dropdown if Super Admin OR has any 'view' permission related to Banners/Ads --}}
        @php
            $canViewBannersAds = auth()->user()->hasRole('super-admin') || 
                                auth()->user()->can('view hero banners') || 
                                auth()->user()->can('view offer banners') || 
                                auth()->user()->can('view popups') || 
                                auth()->user()->can('view message campaigns');
        @endphp

        @if($canViewBannersAds)
            <div class="dropdown-container" data-dropdown-id="banners-ads" x-data="{ open: false }">
                <button @click="open = !open; toggleDropdown('banners-ads-menu')"
                    class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group dropdown-btn">
                    <div class="flex items-center">
                        <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </span>
                        <span class="sidebar-text">Banners & Ads</span>
                    </div>
                    <span class="arrow-icon transition-transform duration-300" :class="{ 'rotate-180': open }">▼</span>
                </button>

                <div id="banners-ads-menu" class="dropdown-content hidden pl-8 mt-1 space-y-1">
                    
                    {{-- Hero Banners Link --}}
                    <!--@if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view hero banners'))-->
                    <!--    <a href="{{ route('admin.hero-banners.index') }}"-->
                    <!--        class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">-->
                    <!--        Hero Banners-->
                    <!--    </a>-->
                    <!--@endif-->

                    {{-- Offer Banners Link --}}
                    <!--@if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view offer banners'))-->
                    <!--    <a href="{{ route('admin.offer-banners.index') }}"-->
                    <!--        class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">-->
                    <!--        Offer Banners-->
                    <!--    </a>-->
                    <!--@endif-->

                    {{-- Pop-ups Link --}}
                    @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view popups'))
                        <a href="{{ route('admin.popups.index') }}"
                            class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">
                            Pop-up Notifications
                        </a>
                    @endif

                    {{-- Message Campaigns Link --}}
                    @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view message campaigns'))
                        <a href="{{ route('admin.message-campaigns.index') }}"
                            class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">
                            Message Campaigns
                        </a>
                    @endif

                </div>
            </div>
        @endif




        {{-- Customers Section --}}
        @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view users'))
            <a href="{{ route('users.list') }}"
                class="flex items-center px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group">
                <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
                <span class="sidebar-text">Customers</span>
            </a>
        @endif

  

        {{-- Policies Section --}}
        @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view policies'))
            <a href="{{ url('admin/policies') }}"
                class="flex items-center px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group">
                <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
                <span class="sidebar-text">Policies</span>
            </a>
        @endif
        {{-- Policies Section --}}
        @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view policies'))
            <a href="{{ url('admin/policyacceptance') }}"
                class="flex items-center px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group">
                <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
                <span class="sidebar-text">Policy Acceptance</span>
            </a>
        @endif

        <div class="dropdown-container" data-dropdown-id="services" x-data="{ open: false }">
            <button @click="open = !open; toggleDropdown('services-menu')"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group dropdown-btn">
                <div class="flex items-center">
                    <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="sidebar-text">Services</span>
                </div>
                <span class="arrow-icon transition-transform duration-300" :class="{ 'rotate-180': open }">▼</span>
            </button>

            <div id="services-menu" class="dropdown-content hidden pl-8 mt-1 space-y-1">
                <a href="{{ route('admin.service-plans.index') }}"
                    class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">
                    Service Plans
                </a>
                <a href="{{ url('admin/demo-subscriptions') }}"
                    class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">
                    Demo Subcription
                </a>
                <a href="{{ url('admin/coupons') }}"
                    class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">
                    Coupons
                </a>
            </div>
        </div>


        {{-- Check if Super Admin or if they have permission to view the module --}}
     
 

        {{-- Logic: Show Dropdown if Super Admin OR has any 'view' permission related to About --}}
        @php
            $canViewAbout = auth()->user()->hasRole('super-admin') || 
                            auth()->user()->can('view about mission values') || 
                            auth()->user()->can('view about core values') || 
                            auth()->user()->can('view about why platform');
        @endphp

     


        {{-- Logic: Show Management if Super Admin OR has any 'view' permission for children --}}
    @php
        $canViewManagement = auth()->user()->hasRole('super-admin') || 
                            auth()->user()->can('view employees') || 
                            auth()->user()->can('view roles') || 
                            auth()->user()->can('view reviews') || 
                            auth()->user()->can('view tickets');
    @endphp

    @if($canViewManagement)
        <div class="dropdown-container" data-dropdown-id="management" x-data="{ open: false }">
            <button @click="open = !open; toggleDropdown('management-menu')"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-[#30beff] text-sm text-white group dropdown-btn">
                <div class="flex items-center">
                    <span class="w-4 h-4 mr-2 flex-shrink-0 text-white group-hover:text-white">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </span>
                    <span class="sidebar-text">Management</span>
                </div>
                <span class="arrow-icon transition-transform duration-300" :class="{ 'rotate-180': open }">▼</span>
            </button>

            <div id="management-menu" class="dropdown-content hidden pl-8 mt-1 space-y-1">
                
                {{-- Employees Link --}}
                @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view employees'))
                    <a href="{{ url('admin/employees') }}"
                        class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">Employees</a>
                @endif

                {{-- Roles Link --}}
                @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view roles'))
                    <a href="{{ route('roles.index') }}"
                        class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">Roles</a>
                @endif

                {{-- Permissions Link (Usually tied to view roles or a dedicated permission) --}}
                @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view roles'))
                    <a href="{{ route('permissions.index') }}"
                        class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">Permissions</a>
                @endif

                {{-- Reviews Link --}}
                <!--@if(auth()->user()->hasRole('super-admin') || auth()->user()->can('view reviews'))-->
                <!--    <a href="{{ route('admin.reviews.index') }}"-->
                <!--        class="block px-3 py-2 text-sm text-slate-300 rounded-md hover:bg-[#30beff] sidebar-text">Reviews</a>-->
                <!--@endif-->

              

            </div>
        </div>
    @endif
    </nav>
</aside>    

<style>
    .sidebar-text {
        transition: opacity 0.3s ease;
        white-space: nowrap;
        overflow: hidden;
    }

    .dropdown-content {
        transition: all 0.3s ease;
        max-height: 0;
        overflow: hidden;
    }

    .dropdown-content:not(.hidden) {
        max-height: 500px;
    }

    .arrow-icon {
        transition: transform 0.3s ease;
        font-size: 10px;
    }

    aside.w-16 nav a,
    aside.w-16 .dropdown-container button {
        justify-content: center;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    aside.w-16 nav a span:first-child,
    aside.w-16 .dropdown-container button span:first-child {
        margin-right: 0;
    }

    aside,
    .sidebar-text,
    .dropdown-content {
        transition: all 0.3s ease-in-out;
    }

    .dropdown-btn:hover {
        background-color: #30beff;
    }
</style> 


<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const ticketBadge = document.getElementById('ticketCount');
        let ticketUnread = 0;

        /* ===== UPDATE BADGE ===== */
        function updateTicketBadge() {
            if (!ticketBadge) return;

            if (ticketUnread > 0) {
                ticketBadge.textContent = ticketUnread > 99 ? '99+' : ticketUnread;
                ticketBadge.classList.remove('hidden');
                ticketBadge.classList.add('flex');
            } else {
                ticketBadge.classList.add('hidden');
            }
        }

        /* ===== FETCH OLD (DB) TICKET NOTIFICATIONS ===== */
        function fetchOldTicketNotifications() {
            fetch('/announcements/fetch')
                .then(res => res.json())
                .then(data => {
                    if (!data.notifications) return;

                    ticketUnread = data.notifications.filter(n =>
                        n.type && n.type.toLowerCase() === 'ticket'
                    ).length;

                    updateTicketBadge();
                });
        }

        /* ===== PUSHER INIT ===== */
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

        /* ===== SUBSCRIBE PRIVATE USER CHANNEL ===== */
        const channel = pusher.subscribe('private-user.{{ auth()->id() }}');

        /* ===== REALTIME TICKET LISTENER ===== */
        channel.bind('master.notification', function (data) {
            console.log('Ticket notification received:', data);

            if (data.type && data.type.toLowerCase() === 'ticket') {
                ticketUnread++;
                updateTicketBadge();
            }
        });

        /* ===== INITIAL LOAD (OLD + NEW TOGETHER) ===== */
        fetchOldTicketNotifications();

    });
</script>

<style>
    /* ===== ACTIVE SIDEBAR LINK ===== */
    .sidebar-active {
        background-color: #30beff !important;
        color: #ffffff !important;
        font-weight: 500;
    }

    .sidebar-active .sidebar-text {
        color: #ffffff !important;
    }

    /* Active dropdown parent */
    .dropdown-active > .dropdown-btn {
        background-color: #30beff !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const currentUrl = window.location.href;

    /* ===== ACTIVE LINK ===== */
    document.querySelectorAll('#main-sidebar a').forEach(link => {
        if (link.href && currentUrl.startsWith(link.href)) {
            link.classList.add('sidebar-active');

            /* ===== IF INSIDE DROPDOWN ===== */
            const dropdown = link.closest('.dropdown-container');
            if (dropdown) {
                dropdown.classList.add('dropdown-active');

                const menu = dropdown.querySelector('.dropdown-content');
                if (menu) {
                    menu.classList.remove('hidden');
                }

                const arrow = dropdown.querySelector('.arrow-icon');
                if (arrow) {
                    arrow.classList.add('rotate-180');
                }
            }
        }
    });

});
</script>