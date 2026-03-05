@extends('layouts.app')
@section('content')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        
        .notification-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .notification-card:hover {
            transform: translateY(-2px);
        }
        
        .skeleton-loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>

    <div x-data="notificationCenter()" x-init="init()" class="min-h-screen bg-gray-50 pb-12">
        
        <div x-show="unreadCount > 0 && showToast" x-transition class="fixed bottom-6 right-6 z-50">
            <div class="bg-white border border-blue-100 shadow-lg rounded-lg p-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-800">New Updates</p>
                    <p class="text-[10px] text-gray-500" x-text="'You have ' + unreadCount + ' unread alerts'"></p>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 pt-4">
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Notification Center</h1>
                    <p class="text-sm text-gray-500">Manage your system activity, support tickets, and trading alerts.</p>
                </div>
                <div class="flex gap-2">
                    <button @click="markAllAsRead()" class="text-xs font-semibold px-4 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 transition">
                        <i class="fas fa-check-double mr-2 text-blue-500"></i> Mark All Read
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-6">
                <div class="lg:col-span-4 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" @input="filterNotifications()"
                           placeholder="Search alerts..." 
                           class="w-full pl-9 pr-4 py-2 text-xs bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition">
                </div>
                
                <div class="lg:col-span-8 flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <template x-for="tab in tabs" :key="tab.id">
                        <button @click="activeTab = tab.id; filterNotifications()"
                                :class="activeTab === tab.id ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-100'"
                                class="flex items-center gap-2 px-4 py-2 rounded-full text-[11px] font-medium whitespace-nowrap transition-all">
                            <span x-text="tab.name"></span>
                            <span x-show="getCountByTab(tab.id) > 0" 
                                  :class="activeTab === tab.id ? 'bg-blue-400 text-white' : 'bg-gray-100 text-gray-500'"
                                  class="px-1.5 py-0.5 rounded-md text-[9px]" x-text="getCountByTab(tab.id)"></span>
                        </button>
                    </template>
                </div>
            </div>

            <main>
                <div x-show="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="i in 6" :key="i">
                        <div class="bg-white rounded-xl p-4 h-24 skeleton-loading border border-gray-100"></div>
                    </template>
                </div>

                <div x-show="!isLoading && filteredNotifications.length > 0" 
                     class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="notification in filteredNotifications" :key="notification.id">
                        <div @click="!notification.read && markAsRead(notification.id)"
                             class="notification-card bg-white rounded-xl border p-3 cursor-pointer relative flex flex-col justify-between"
                             :class="notification.read ? 'border-gray-100 shadow-sm' : 'border-blue-100 shadow-md ring-1 ring-blue-50'">
                            
                            <div x-show="!notification.read" class="absolute top-3 right-3 flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </div>

                            <div class="flex items-start gap-3">
                                <div :class="notification.colorClass" class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-white text-xs shadow-sm">
                                    <i :class="notification.icon"></i>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] text-gray-400 font-medium mb-0.5" x-text="notification.time"></span>
                                        <h3 class="text-sm font-bold text-gray-900 truncate pr-4" x-text="notification.title"></h3>
                                    </div>
                                    <div class="text-[11px] text-gray-600 mt-1 leading-relaxed" x-html="notification.description"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-50">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase" 
                                      :class="notification.tagColorClass" x-text="notification.type"></span>
                                
                                <div class="flex gap-2">
                                    <button @click.stop="deleteNotification(notification.id)" class="text-gray-300 hover:text-red-500 transition p-1">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="!isLoading && filteredNotifications.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                        <i class="fas fa-wind text-gray-200 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">Everything is clear</p>
                    <p class="text-xs text-gray-500 mt-1">No notifications match your current filters.</p>
                </div>
            </main>

            <footer x-show="notifications.length > 0" class="mt-10 flex flex-col items-center border-t border-gray-200 pt-8">
                <p class="text-[10px] text-gray-400 mt-4 uppercase tracking-widest">
                    Showing <span x-text="filteredNotifications.length"></span> of <span x-text="notifications.length"></span> alerts
                </p>
            </footer>
        </div>
    </div>

    <script>
        function notificationCenter() {
            return {
                isLoading: true,
                showToast: false,
                activeTab: 'all',
                searchQuery: '',
                unreadCount: 0,
                tabs: [
                    { id: 'all', name: 'All' },
                    { id: 'unread', name: 'Unread' },
                    { id: 'tip', name: 'Trading Tips' },
                    { id: 'tip_followup', name: 'Follow-ups' },
                    { id: 'ticket', name: 'Support' },
                    { id: 'announcement', name: 'System' },
                    { id: 'chat', name: 'Messages' }
                ],
                notifications: [],
                filteredNotifications: [],

                init() {
                    this.fetchNotifications();
                    // Poll for new notifications every 60 seconds
                    setInterval(() => this.fetchNotifications(true), 60000);
                },

                fetchNotifications(isSilent = false) {
                    if(!isSilent) this.isLoading = true;
                    fetch('/user/notifications/')
                        .then(res => res.json())
                        .then(res => {
                            this.notifications = res.data.map(n => ({
                                id: n.id,
                                title: n.title,
                                description: this.formatMessage(n),
                                category: n.type, // RAW type for filtering
                                type: n.type.replace('_', ' '), // Formatted display text
                                time: this.formatTime(n.created_at),
                                read: parseInt(n.is_read) === 1,
                                icon: this.getIcon(n.type),
                                colorClass: this.getColor(n.type),
                                tagColorClass: this.getTag(n.type)
                            }));
                            this.filterNotifications();
                            this.updateUnreadCount();
                            this.isLoading = false;
                            
                            if(isSilent) this.showToast = true;
                        });
                },

               formatTime(date) {
    const d = new Date(date);

    const fullDate = d.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });

    const time = d.toLocaleTimeString('en-IN', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });

    return `${fullDate} • ${time}`;
},

                filterNotifications() {
                    let filtered = this.notifications;
                    if (this.activeTab !== 'all') {
                        filtered = (this.activeTab === 'unread') 
                            ? filtered.filter(n => !n.read) 
                            : filtered.filter(n => n.category === this.activeTab);
                    }
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(n => 
                            n.title.toLowerCase().includes(q) || 
                            n.description.toLowerCase().includes(q)
                        );
                    }
                    this.filteredNotifications = filtered;
                },

                getCountByTab(tabId) {
                    if (tabId === 'all') return this.notifications.length;
                    if (tabId === 'unread') return this.notifications.filter(n => !n.read).length;
                    return this.notifications.filter(n => n.category === tabId).length;
                },

                updateUnreadCount() {
                    this.unreadCount = this.notifications.filter(n => !n.read).length;
                },

                getIcon(type) {
                    const icons = { 
                        tip: 'fa-chart-line', 
                        tip_followup: 'fa-clock-rotate-left',
                        ticket: 'fa-ticket-alt',
                        chat: 'fa-comment-dots', 
                        announcement: 'fa-bullhorn' 
                    };
                    return 'fas ' + (icons[type] || 'fa-bell');
                },

                getColor(type) {
                    const colors = { 
                        tip: 'bg-emerald-500', 
                        tip_followup: 'bg-blue-500', 
                        ticket: 'bg-orange-500',
                        chat: 'bg-indigo-500', 
                        announcement: 'bg-rose-500' 
                    };
                    return colors[type] || 'bg-slate-500';
                },

                getTag(type) {
                    const tags = { 
                        tip: 'bg-emerald-50 text-emerald-700', 
                        tip_followup: 'bg-blue-50 text-blue-700',
                        ticket: 'bg-orange-50 text-orange-700',
                        chat: 'bg-indigo-50 text-indigo-700',
                        announcement: 'bg-rose-50 text-rose-700'
                    };
                    return tags[type] || 'bg-gray-50 text-gray-600';
                },
                
                        
            formatMessage(n) {
                if (n.type === 'tip_followup') {
                    try {
                        const data = JSON.parse(n.message);

                        let html = `
                            <div class="text-sm text-blue-600 font-semibold mb-1">
                                ${data.update_title || 'Price Update'}
                            </div>

                            <!-- Horizontal scroll container -->
                            <div class="flex items-center gap-2 text-xs mt-1
                                        overflow-x-auto overflow-y-hidden whitespace-nowrap
                                        scrollbar-thin-x">
                        `;

                        const createItem = (label, values) => {
                            if (!values || !values.old) return '';

                            const isSL = label === 'SL';

                            return `
                                <div class="flex-shrink-0 flex items-center gap-1
                                            px-2 py-1 rounded-md bg-gray-50 border">
                                    <span class="text-[10px] font-bold text-gray-400">
                                        ${label}
                                    </span>
                                    <span class="line-through text-gray-400 text-[8px]">
                                        ${values.old}
                                    </span>
                                    <span class="text-gray-300 text-[8px]">→</span>
                                    <span class="font-semibold text-[8px] ${isSL ? 'text-red-600' : 'text-emerald-600'}">
                                        ${values.new}
                                    </span>
                                </div>
                            `;
                        };

                        html += createItem('T1', data.t1);
                        html += createItem('T2', data.t2);
                        html += createItem('SL', data.sl);

                        html += `</div>`;

                        return html;
                    } catch (e) {
                        return n.message;
                    }
                }

                return n.message;
            },

              markAllAsRead() {
    // Optimistic UI update: Mark everything as read locally first
    this.notifications.forEach(n => n.read = true);
    this.updateUnreadCount();
    this.filterNotifications();

    fetch('/user/notifications/read-all', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.status) {
            // Rollback if server fails (optional)
            this.fetchNotifications(true);
        }
    })
    .catch(() => this.fetchNotifications(true));
},

markAsRead(id) {
    // Find the specific notification and mark as read locally
    const n = this.notifications.find(item => item.id === id);
    if (n) n.read = true;
    
    this.updateUnreadCount();
    this.filterNotifications();

    fetch('/user/notifications/read', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
        },
        body: JSON.stringify({ notification_id: id })
    });
},
                deleteNotification(id) {
                    fetch('/user/notifications/delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ notification_id: id })
                    }).then(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                        this.filterNotifications();
                        this.updateUnreadCount();
                    });
                }
            }
        }
    </script>

    <style>
                    /* Ultra-thin horizontal scrollbar (1px) */
            .scrollbar-thin-x::-webkit-scrollbar {
                height: 1px;
            }

            .scrollbar-thin-x::-webkit-scrollbar-track {
                background: transparent;
            }

            .scrollbar-thin-x::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.25);
                border-radius: 10px;
            }

            /* Firefox */
            .scrollbar-thin-x {
                scrollbar-width: thin;
                scrollbar-color: rgba(0, 0, 0, 0.25) transparent;
            }
    </style>
@endsection