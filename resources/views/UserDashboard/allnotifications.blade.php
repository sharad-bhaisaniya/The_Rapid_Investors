@extends('layouts.userdashboard')
@section('content')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom scrollbar styling -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        .skeleton-loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .toast-transition {
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .tab-transition {
            transition: all 0.2s ease-in-out;
        }

        .notification-transition {
            transition: all 0.25s ease;
        }
    </style>

    <div x-data="notificationCenter()" x-init="init()" class="min-h-screen">
        <!-- Toast notification for new alerts -->
        <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" class="fixed top-20 right-6 z-50 max-w-sm">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-4 flex items-center space-x-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-bell text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">New notifications</p>
                    <p class="text-sm text-gray-600">You have <span x-text="unreadCount" class="font-bold"></span>
                        unread notifications</p>
                </div>
                <button @click="showToast = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Main container -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 ">
            <!-- Header Section -->
            <header class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl font-semibold text-gray-900">Notifications</h1>
                        <p class="text-sm text-gray-500">System alerts & updates</p>

                    </div>
                    <div class="flex space-x-3">
                        <button @click="markAllAsRead()"
                            class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg font-medium transition-colors duration-200 flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            Mark all as read
                        </button>
                        {{-- <button @click="clearAll()"
                            class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg font-medium transition-colors duration-200 flex items-center">
                            <i class="fas fa-trash-alt mr-2"></i>
                            Clear all
                        </button> --}}
                    </div>
                </div>
            </header>

            <!-- Search bar -->
            {{-- <div class="mb-6">
                <div class="relative max-w-xl">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" x-model="searchQuery" @input="filterNotifications()"
                        class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Search notifications...">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <span x-text="filteredNotifications.length" class="text-sm text-gray-500"></span>
                    </div>
                </div>
            </div> --}}
            <!-- Search + Date Filters -->
            <div class="mb-6 flex flex-col md:flex-row gap-3 items-start md:items-center">

                <!-- Search -->
                <div class="relative w-full md:max-w-xl">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" x-model="searchQuery" @input="filterNotifications()"
                        class="block w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-xl bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search notifications...">
                </div>

                <!-- Quick Date Filters -->
                <div class="flex gap-2">

                    <button @click="setDateRange(7)" class="px-3 py-1.5 text-xs
        rounded-lg border  hover:bg-blue-50"
                                :class="datePreset === 7 ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'">
                                Last Week
                            </button>

                            <button @click="setDateRange(30)" class="px-3 py-1.5 text-xs
        rounded-lg border  hover:bg-blue-50"
                                :class="datePreset === 30 ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'">
                        Last Month
                    </button>

                </div>

                <!-- Custom Range -->
                <div class="flex gap-2 items-center">

                    <input type="date" x-model="fromDate" @change="filterNotifications()"
                        class="border rounded-lg px-3 py-1.5 
        text-sm">

                            <span class="text-gray-400">to</span>

                            <input type="date" x-model="toDate" @change="filterNotifications()"
                                class="border rounded-lg px-3 py-1.5 
        text-sm">

                        </div>

            </div>


            <!-- Filter Tabs Section (Compact Professional Style) -->
            <div x-on:scroll.window="isSticky = window.scrollY > 100" class="sticky top-0 z-30 mb-4 transition-all"
                :class="isSticky ?
                    'bg-white/90 backdrop-blur border-b border-gray-200 py-2 -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8' :
                    ''">

                <div class="flex overflow-x-auto custom-scrollbar">
                    <div class="inline-flex bg-gray-100 rounded-full p-1 gap-1">

                        <template x-for="tab in tabs" :key="tab.id">
                            <button @click="activeTab = tab.id; filterNotifications()"
                                class="tab-transition flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap"
                                :class="activeTab === tab.id ?
                                    'bg-white text-blue-600 shadow-sm ring-1 ring-gray-200' :
                                    'text-gray-600 hover:text-gray-900 hover:bg-gray-200'">

                                <span x-text="tab.name"></span>

                                <span x-show="getCountByTab(tab.id) > 0" class="px-1.5 py-0.5 rounded-full text-[10px]"
                                    :class="activeTab === tab.id ? 'bg-blue-100 text-blue-700' : 'bg-gray-300 text-gray-700'"
                                    x-text="getCountByTab(tab.id)">
                                </span>
                            </button>
                        </template>

                    </div>
                </div>
            </div>


            <!-- Main Content Area -->
            <main>
                <!-- Skeleton loading state -->
                <div x-show="isLoading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <template x-for="i in 6" :key="i">
                        <div class="bg-white rounded-xl border border-gray-200 p-5 skeleton-loading">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-gray-300"></div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="h-5 bg-gray-300 rounded w-3/4 mb-3"></div>
                                    <div class="h-4 bg-gray-300 rounded w-full mb-2"></div>
                                    <div class="h-4 bg-gray-300 rounded w-2/3"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Notifications Grid -->
                <div x-show="!isLoading && filteredNotifications.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <template x-for="notification in filteredNotifications" :key="notification.id">
                        <div @click="!notification.read && markAsRead(notification.id)"
                            class="notification-transition bg-white rounded-lg border px-3 py-3 cursor-pointer hover:bg-gray-50 relative overflow-hidden"
                            :class="{
                                'border-blue-200 bg-blue-50/50': !notification.read,
                                'border-gray-200': notification.read
                            }">
                            <!-- Unread indicator dot -->
                            <div x-show="!notification.read"
                                class="absolute top-4 right-4 w-2.5 h-2.5 rounded-full bg-blue-500"></div>

                            <div class="flex">
                                <!-- Icon badge -->
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white shadow-md"
                                        :class="notification.colorClass">
                                        <i :class="notification.icon"></i>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="ml-4 flex-1 min-w-0">
                                    <div class="flex justify-between">
                                        <h3 class="font-semibold text-sm text-gray-900 truncate"
                                            x-text="notification.title"></h3>
                                        <span
                                            class="text-[11px] text-gray-500
                                            x-text="notification.time"></span>
                                    </div>
                                    {{-- <p class="text-gray-600 mt-1 line-clamp-2" x-text="notification.description"></p> --}}
                                    <p class="text-xs text-gray-600 mt-1 leading-snug" x-html="notification.description">
                                    </p>


                                    <!-- Tags -->
                                    <div class="flex flex-wrap gap-1 mt-3">
                                        <span class="px-2 py-0.5 text-[10px]
                       font-medium rounded-full"
                                                    :class="notification.tagColorClass" x-text="notification.type"></span>
                                        <template x-if="notification.priority === 'high'">
                                            <span
                                                class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">High
                                                Priority</span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end mt-4 pt-4 border-t border-gray-100">
                                <button
                                    x-show="notification.category !== 'tip' && notification.category !== 'tip_followup'"
                                    @click.stop="deleteNotification(notification.id)"
                                    class="text-gray-400 hover:text-red-500 p-0.5 text-xs rounded-full hover:bg-red-50 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <button x-show="!notification.read" @click.stop="markAsRead(notification.id)"
                                    class="ml-2 text-gray-400 hover:text-blue-500 p-0.5 text-xs rounded-full hover:bg-blue-50 transition-colors">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty state -->
                <div x-show="!isLoading && filteredNotifications.length === 0" class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
                        <i class="fas fa-bell-slash text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No notifications found</h3>
                    <p class="text-gray-600 max-w-md mx-auto mb-6"
                        x-text="searchQuery ? 'Try adjusting your search to find what you\'re looking for.' : 'All caught up! You don\'t have any notifications at the moment.'">
                    </p>
                    <button x-show="searchQuery" @click="searchQuery = ''; filterNotifications()"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Clear search
                    </button>
                </div>
            </main>

            <!-- Footer info -->
            <footer class="mt-12 pt-8 border-t border-gray-200">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-600 text-sm">
                        <p>Showing <span x-text="filteredNotifications.length" class="font-semibold"></span> of <span
                                x-text="notifications.length" class="font-semibold"></span> notifications</p>
                        <p class="mt-1"><span x-text="unreadCount" class="font-semibold"></span> unread
                            notifications</p>
                    </div>
                    <div class="flex items-center space-x-4 mt-4 md:mt-0">
                        <button @click="loadMoreNotifications()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium">
                            Load more
                        </button>
                        <div class="text-gray-500 text-sm">
                            <i class="fas fa-sync-alt mr-1"></i>
                            Auto-refresh in <span class="font-semibold">30s</span>
                        </div>
                    </div>
                </div>
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
                isSticky: false,
                datePreset: null,
                fromDate: '',
                toDate: '',

                tabs: [{
                        id: 'all',
                        name: 'All'
                    },
                    {
                        id: 'unread',
                        name: 'Unread'
                    },

                    {
                        id: 'tip',
                        name: 'Trading Tips'
                    },
                    {
                        id: 'tip_followup',
                        name: 'Tip Follow-Ups'
                    },

                    {
                        id: 'subscription',
                        name: 'Subscriptions'
                    },

                    {
                        id: 'campaign',
                        name: 'Campaigns'
                    },
                    {
                        id: 'announcement',
                        name: 'Announcements'
                    },

                    {
                        id: 'chat',
                        name: 'Chats'
                    },
                    {
                        id: 'ticket',
                        name: 'Support Tickets'
                    },
                ],


                notifications: [],
                filteredNotifications: [],

                init() {
                    this.fetchNotifications();
                    this.fetchUnreadCount();

                    setInterval(() => {
                        this.fetchNotifications(true);
                    }, 30000);
                },
                setDateRange(days) {

                    this.datePreset = days;

                    const today = new Date();
                    const past = new Date();
                    past.setDate(today.getDate() - days);

                    this.fromDate = past.toISOString().split('T')[0];
                    this.toDate = today.toISOString().split('T')[0];

                    this.filterNotifications();
                },


                /* ---------------- FETCH FROM DB ---------------- */

                fetchNotifications(showToast = false) {
                    this.isLoading = true;

                    fetch('/user/notifications/')
                        .then(res => res.json())
                        .then(res => {

                            this.notifications = res.data.map(n => ({
                                id: n.id,
                                title: n.title,
                                description: this.formatMessage(n),
                                category: n.type,
                                type: this.formatType(n.type),
                                time: this.formatTime(n.created_at),
                                read: n.is_read == 1,
                                deleted: n.deleted_at !== null,
                                priority: n.priority ?? 'normal',
                                icon: this.getIcon(n.type),
                                colorClass: this.getColor(n.type),
                                tagColorClass: this.getTag(n.type),
                            }));

                            this.filterNotifications();
                            this.updateUnreadCount();
                            this.isLoading = false;

                            if (showToast) {
                                this.showToast = true;
                                setTimeout(() => this.showToast = false, 4000);
                            }
                        });
                },

                formatMessage(n) {

                    if (n.type !== 'tip_followup') {
                        return n.message;
                    }

                    try {
                        const d = JSON.parse(n.message);

                        return `
                        <div class="mt-1 flex flex-col items-center justify-center text-center bg-yellow-50 p-2 rounded-md">

                            <!-- Stock Name -->
                            <div class="text-sm font-semibold text-gray-800 mb-1">
                            ${d.update_title}
                            </div>

                            <!-- Levels -->
                            <div class="flex items-center justify-center gap-[5.4rem] text-xs">

                                <div class="flex flex-col items-center">
                                    <span class="text-gray-500">T1</span>
                                    <span class="font-medium text-gray-700">${d.t1.old}</span>
                                            →
                                    <span class="font-medium text-green-600">
                                        ${d.t1.new}
                                    </span>
                                </div>

                                <div class="flex flex-col items-center">
                                    <span class="text-gray-500">T2</span>
                                    <span class="font-medium text-gray-700">${d.t2.old}</span>
                                            →
                                    <span class="font-medium text-green-600">
                                        ${d.t2.new}
                                    </span>
                                </div>

                                <div class="flex flex-col items-center">
                                    <span class="text-gray-500">SL</span>
                                    <span class="font-medium text-gray-700">${d.sl.old}</span>
                                                →
                                    <span class="font-medium text-red-600">
                                        ${d.sl.new}
                                    </span>
                                </div>

                            </div>
                        </div>
                        `;

                    } catch (e) {
                        return n.message;
                    }
                },
   



                fetchUnreadCount() {
                    fetch('/user/notifications/unread-count/')
                        .then(res => res.json())
                        .then(res => this.unreadCount = res.count)
                        .then(res => console.log(res));
                },

                /* ---------------- FILTERING ---------------- */

                filterNotifications() {

                    let filtered = this.notifications;

                    /* Tab filtering */
                    if (this.activeTab !== 'all') {
                        if (this.activeTab === 'unread') {
                            filtered = filtered.filter(n => !n.read);
                        } else {
                            filtered = filtered.filter(n => n.category === this.activeTab);
                        }
                    }

                    /* Search */
                    if (this.searchQuery.trim() !== '') {
                        const q = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(n =>
                            n.title.toLowerCase().includes(q) ||
                            n.description.toLowerCase().includes(q)
                        );
                    }

                    /* Date filtering */
                    if (this.fromDate || this.toDate) {

                        filtered = filtered.filter(n => {

                            const d = new Date(n.time);

                            if (this.fromDate && d < new Date(this.fromDate)) return false;
                            if (this.toDate && d > new Date(this.toDate + ' 23:59:59')) return false;

                            return true;
                        });
                    }

                    this.filteredNotifications = filtered;
                },


                getCountByTab(tabId) {
                    if (tabId === 'all') return this.notifications.length;
                    if (tabId === 'unread') return this.notifications.filter(n => !n.read).length;

                    return this.notifications.filter(n => n.category === tabId).length;
                },

                /* ---------------- ACTIONS ---------------- */

                markAsRead(id) {
                    fetch('/user/notifications/read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            notification_id: id
                        })
                    }).then(() => {
                        const n = this.notifications.find(n => n.id === id);
                        if (n) n.read = true;
                        this.updateUnreadCount();
                        this.filterNotifications();
                    });
                },

                markAllAsRead() {
                    fetch('/user/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        }
                    }).then(() => {
                        this.notifications.forEach(n => n.read = true);
                        this.updateUnreadCount();
                        this.filterNotifications();
                    });
                },

                deleteNotification(id) {
                    fetch('/user/notifications/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            notification_id: id
                        })
                    }).then(() => {

                        // ✅ REMOVE ONLY THAT ITEM
                        this.notifications = this.notifications.filter(n => n.id !== id);

                        this.updateUnreadCount();
                        this.filterNotifications();
                    });
                },




                clearAll() {
                    if (!confirm("Clear all notifications?")) return;

                    Promise.all(
                        this.notifications.map(n => this.deleteNotification(n.id))
                    ).then(() => {
                        this.notifications = [];
                        this.filteredNotifications = [];
                        this.unreadCount = 0;
                    });
                },

                updateUnreadCount() {
                    this.unreadCount = this.notifications.filter(n => !n.read).length;
                },

                /* ---------------- UI HELPERS ---------------- */

                formatTime(date) {
                    return new Date(date).toLocaleString();
                },

                formatType(type) {
                    return type.charAt(0).toUpperCase() + type.slice(1);
                },

                getIcon(type) {
                    return {
                        tip: 'fas fa-chart-line',
                        tip_followup: 'fas fa-bell',
                        subscription: 'fas fa-crown',
                        campaign: 'fas fa-bullhorn',
                        announcement: 'fas fa-volume-high',
                        chat: 'fas fa-comments',
                        ticket: 'fas fa-life-ring'
                    } [type] || 'fas fa-bell';
                },


                getColor(type) {
                    return {
                        tip: 'bg-green-500',
                        tip_followup: 'bg-emerald-500',
                        subscription: 'bg-yellow-500',
                        campaign: 'bg-blue-500',
                        announcement: 'bg-indigo-500',
                        chat: 'bg-purple-500',
                        ticket: 'bg-orange-500'
                    } [type] || 'bg-gray-500';
                },


                getTag(type) {
                    return {
                        tip: 'bg-green-100 text-green-800',
                        tip_followup: 'bg-emerald-100 text-emerald-800',
                        subscription: 'bg-yellow-100 text-yellow-800',
                        campaign: 'bg-blue-100 text-blue-800',
                        announcement: 'bg-indigo-100 text-indigo-800',
                        chat: 'bg-purple-100 text-purple-800',
                        ticket: 'bg-orange-100 text-orange-800'
                    } [type] || 'bg-gray-100 text-gray-800';
                },

            }
        }
    </script>
@endsection
