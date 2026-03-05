@extends('layouts.app')

@section('title', 'All Support Tickets')

@section('content')
    {{-- 
        Prepare Data for Alpine JS 
        We map the PHP collection to a JSON-friendly array to enable client-side sorting, live updates, and filtering.
    --}}
    @php
        $ticketsData = $tickets->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'user_name' => optional($ticket->user)->name ?? 'User #' . $ticket->user_id,
                'user_id' => $ticket->user_id, 
                'subject' => $ticket->subject,
                'issue' => $ticket->issue,
                'description' => $ticket->description,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'admin_note' => $ticket->admin_note,
                'created_at_formatted' => $ticket->created_at->format('d M Y, h:i A'),
                'created_timestamp' => $ticket->created_at->timestamp,
                'image_url' => $ticket->getFirstMediaUrl('tickets'),
                // Added for Date Filtering
                'month' => $ticket->created_at->format('m'),
                'year' => $ticket->created_at->format('Y'),
            ];
        });
    @endphp

    <div class="p-4 bg-gray-50 min-h-screen" x-data="ticketManager({{ json_encode($ticketsData) }})">
        <div class="max-w-7xl mx-auto">
            
            {{-- Header & Statistics --}}
            <div class="mb-8 space-y-6">
                <div class="flex justify-between items-end">
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">🎫 Support Dashboard</h2>
                        <p class="text-gray-500 text-sm mt-1">Manage and track all customer support requests in real-time.</p>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Tickets</span>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-2xl font-extrabold text-gray-900" x-text="tickets.length"></span>
                            <div class="p-2 bg-gray-50 rounded-lg text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100 flex flex-col justify-between hover:shadow-md transition relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-16 h-16 bg-blue-50 rounded-bl-full -mr-4 -mt-4 z-0"></div>
                        <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest relative z-10">Open</span>
                        <div class="flex items-center justify-between mt-2 relative z-10">
                            <span class="text-2xl font-extrabold text-blue-700" x-text="counts.open"></span>
                            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-amber-100 flex flex-col justify-between hover:shadow-md transition">
                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">In Progress</span>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-2xl font-extrabold text-amber-700" x-text="counts.in_progress"></span>
                            <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100 flex flex-col justify-between hover:shadow-md transition">
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Resolved</span>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-2xl font-extrabold text-emerald-700" x-text="counts.resolved"></span>
                            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Real-time Filter Bar --}}
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    
                    {{-- Search Field --}}
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Search Details</label>
                        <div class="relative">
                            <input type="text" x-model="filters.search" placeholder="Search ID, Name, or Subject..." 
                                   class="w-full bg-gray-50 border-gray-100 rounded-xl text-sm p-2.5 pl-9 focus:ring-2 focus:ring-blue-500 transition outline-none">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    {{-- Status Dropdown --}}
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Status</label>
                        <select x-model="filters.status" class="w-full bg-gray-50 border-gray-100 rounded-xl text-sm p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">All Statuses</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>

                    {{-- Priority Dropdown --}}
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Priority</label>
                        <select x-model="filters.priority" class="w-full bg-gray-50 border-gray-100 rounded-xl text-sm p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">All Priorities</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>

                    {{-- Month Filter --}}
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Month</label>
                        <select x-model="filters.month" class="w-full bg-gray-50 border-gray-100 rounded-xl text-sm p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Any Month</option>
                            <option value="01">January</option><option value="02">February</option><option value="03">March</option>
                            <option value="04">April</option><option value="05">May</option><option value="06">June</option>
                            <option value="07">July</option><option value="08">August</option><option value="09">September</option>
                            <option value="10">October</option><option value="11">November</option><option value="12">December</option>
                        </select>
                    </div>

                    {{-- Year Filter --}}
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Year</label>
                        <select x-model="filters.year" class="w-full bg-gray-50 border-gray-100 rounded-xl text-sm p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Any Year</option>
                            <template x-for="y in availableYears" :key="y">
                                <option :value="y" x-text="y"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-gray-50">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                        Showing <span class="text-blue-600" x-text="filteredTickets.length"></span> of <span x-text="tickets.length"></span> Total Tickets
                    </p>
                    <button @click="resetFilters()" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Clear Filters
                    </button>
                </div>
            </div>

            {{-- Tickets Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <template x-for="col in [
                                {key: 'id', label: 'ID'}, 
                                {key: 'user_name', label: 'Customer Name'}, 
                                {key: 'subject', label: 'Subject'}, 
                                {key: 'priority', label: 'Priority'}, 
                                {key: 'status', label: 'Status'}
                            ]">
                                <th @click="sortBy(col.key)" 
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition select-none group">
                                    <div class="flex items-center gap-1">
                                        <span x-text="col.label"></span>
                                        <div class="flex flex-col text-gray-300">
                                            <svg class="w-2 h-2" :class="sortCol === col.key && sortAsc ? 'text-blue-600' : ''" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0l8 10H4z"/></svg>
                                            <svg class="w-2 h-2" :class="sortCol === col.key && !sortAsc ? 'text-blue-600' : ''" fill="currentColor" viewBox="0 0 24 24"><path d="M12 24l-8-10h16z"/></svg>
                                        </div>
                                    </div>
                                </th>
                            </template>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="ticket in filteredTickets" :key="ticket.id">
                            <tr :class="ticket.status === 'Resolved' ? 'bg-slate-50 opacity-90' : 'bg-white hover:bg-blue-50/30'" 
                                class="transition-colors group">
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold"
                                    :class="ticket.status === 'Resolved' ? 'text-gray-300' : 'text-gray-400'">
                                    #<span x-text="ticket.id"></span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs"
                                             x-text="ticket.user_name.charAt(0).toUpperCase()">
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold" :class="ticket.status === 'Resolved' ? 'text-gray-500' : 'text-gray-900'"
                                                 x-text="ticket.user_name"></div>
                                            <div class="text-[10px] uppercase tracking-tighter" :class="ticket.status === 'Resolved' ? 'text-gray-300' : 'text-gray-400'"
                                                 x-text="ticket.created_at_formatted"></div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold" :class="ticket.status === 'Resolved' ? 'text-gray-500 decoration-slate-300' : 'text-gray-800'"
                                         x-text="ticket.subject"></div>
                                    <div class="text-xs truncate max-w-xs" :class="ticket.status === 'Resolved' ? 'text-gray-300' : 'text-gray-500'"
                                         x-text="ticket.issue"></div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest"
                                          :class="{
                                                'bg-red-100 text-red-700': ticket.priority === 'High' && ticket.status !== 'Resolved',
                                                'bg-gray-100 text-gray-600': ticket.priority !== 'High' && ticket.status !== 'Resolved',
                                                'bg-slate-100 text-slate-400': ticket.status === 'Resolved'
                                          }"
                                          x-text="ticket.priority">
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold"
                                          :class="{
                                              'bg-blue-100 text-blue-700': ticket.status === 'Open',
                                              'bg-amber-100 text-amber-700': ticket.status === 'In Progress',
                                              'bg-emerald-100 text-emerald-700': ticket.status === 'Resolved'
                                          }"
                                          x-text="ticket.status">
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button @click="openDrawer(ticket)"
                                        class="font-bold text-sm flex items-center gap-1 group transition"
                                        :class="ticket.status === 'Resolved' ? 'text-slate-400 hover:text-slate-600' : 'text-blue-600 hover:text-blue-900'">
                                        View
                                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        
                        {{-- Empty State Row --}}
                        <template x-if="filteredTickets.length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center space-y-4">
                                    <div class="flex justify-center">
                                        <div class="p-6 bg-gray-50 rounded-full">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                    </div>
                                    <p class="text-gray-500 font-bold">No tickets match your current filters.</p>
                                    <button @click="resetFilters()" class="text-blue-600 font-bold underline">Clear all filters</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Side Drawer --}}
        <div x-show="drawerOpen" class="fixed inset-0 z-50 overflow-hidden" x-cloak>
            <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="drawerOpen = false"></div>
            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div x-show="drawerOpen" x-transition:enter="transform transition ease-in-out duration-500"
                    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-500"
                    x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                    class="w-screen max-w-md">

                    <div class="h-full flex flex-col bg-white shadow-2xl">
                        {{-- Drawer Header --}}
                        <div class="px-6 py-6 bg-gray-50 border-b flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Ticket #<span x-text="selectedTicket.id"></span></h2>
                                <p class="text-xs text-gray-500" x-text="selectedTicket.created_at_formatted"></p>
                            </div>
                            <button @click="drawerOpen = false" class="p-2 rounded-full hover:bg-gray-200 text-gray-400 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        {{-- Drawer Content --}}
                        <div class="flex-1 overflow-y-auto p-6 space-y-6">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase"
                                    :class="{
                                        'bg-blue-100 text-blue-700': selectedTicket.status === 'Open',
                                        'bg-amber-100 text-amber-700': selectedTicket.status === 'In Progress',
                                        'bg-emerald-100 text-emerald-700': selectedTicket.status === 'Resolved'
                                    }"
                                    x-text="selectedTicket.status"></span>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-gray-100 text-gray-700"
                                    x-text="selectedTicket.priority"></span>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-gray-800 mb-2" x-text="selectedTicket.subject"></h3>
                                <div class="flex items-center gap-2 mt-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <p class="text-sm font-bold text-blue-600" x-text="selectedTicket.user_name"></p>
                                </div>
                            </div>

                            <template x-if="selectedTicket.image_url">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Attachment</label>
                                    <div class="relative group">
                                        <img :src="selectedTicket.image_url" class="w-full rounded-2xl border shadow-sm object-cover max-h-64 cursor-pointer hover:ring-4 hover:ring-blue-100 transition-all" @click="window.open(selectedTicket.image_url, '_blank')">
                                        <div class="absolute bottom-2 right-2 bg-black/50 text-white text-[10px] px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition">Click to view</div>
                                    </div>
                                </div>
                            </template>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Issue Description</label>
                                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                    <p class="text-sm text-gray-700 leading-relaxed" x-text="selectedTicket.description"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Drawer Footer --}}
                        <div class="px-6 py-6 bg-gray-50 border-t">
                            <template x-if="selectedTicket.status !== 'Resolved'">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Resolution Note</label>
                                    <textarea x-model="adminNote" rows="3" class="w-full p-4 border-gray-200 rounded-2xl mb-4 focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="Write resolution details..."></textarea>
                                    <button @click="resolveTicket()" :disabled="loading" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-emerald-200 transition-all transform active:scale-[0.98] flex justify-center items-center gap-2">
                                        <template x-if="!loading">
                                            <span class="flex items-center gap-2">Mark as Resolved</span>
                                        </template>
                                        <template x-if="loading">
                                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </template>
                                    </button>
                                </div>
                            </template>

                            <template x-if="selectedTicket.status === 'Resolved'">
                                <div>
                                    <label class="text-[10px] font-black text-emerald-600 uppercase tracking-widest block mb-2">Resolution Details</label>
                                    <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100 mb-4">
                                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Admin Note:</p>
                                        <p class="text-sm text-gray-800 leading-relaxed" x-text="adminNote || 'No specific note was added.'"></p>
                                    </div>
                                    <div class="w-full bg-gray-100 text-gray-500 font-bold py-4 rounded-2xl flex justify-center items-center gap-2 cursor-not-allowed select-none">
                                        Ticket is Closed
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>

    <script>
        function ticketManager(initialTickets) {
            return {
                tickets: initialTickets,
                drawerOpen: false,
                selectedTicket: {},
                adminNote: '',
                loading: false,
                sortCol: 'id',
                sortAsc: false,
                
                // Filters Object
                filters: {
                    search: '',
                    status: '',
                    priority: '',
                    month: '',
                    year: ''
                },

                // Derived Years from data
                get availableYears() {
                    const years = this.tickets.map(t => t.year);
                    return [...new Set(years)].sort((a, b) => b - a);
                },

                // Computed Logic for Stats
                get counts() {
                    return {
                        open: this.tickets.filter(t => t.status === 'Open').length,
                        in_progress: this.tickets.filter(t => t.status === 'In Progress').length,
                        resolved: this.tickets.filter(t => t.status === 'Resolved').length
                    };
                },

                // THE SMOOTH FILTER LOGIC
                get filteredTickets() {
                    let filtered = this.tickets.filter(ticket => {
                        // 1. Search Filter (Subject, User, Issue, or ID)
                        const search = this.filters.search.toLowerCase();
                        const searchMatch = !search || 
                            ticket.id.toString().includes(search) ||
                            ticket.user_name.toLowerCase().includes(search) ||
                            ticket.subject.toLowerCase().includes(search) ||
                            ticket.issue.toLowerCase().includes(search);

                        // 2. Status Filter
                        const statusMatch = !this.filters.status || ticket.status === this.filters.status;

                        // 3. Priority Filter
                        const priorityMatch = !this.filters.priority || ticket.priority === this.filters.priority;

                        // 4. Month Filter
                        const monthMatch = !this.filters.month || ticket.month === this.filters.month;

                        // 5. Year Filter
                        const yearMatch = !this.filters.year || ticket.year === this.filters.year;

                        return searchMatch && statusMatch && priorityMatch && monthMatch && yearMatch;
                    });

                    // Apply Sorting to Filtered Results
                    return filtered.sort((a, b) => {
                        let mod = this.sortAsc ? 1 : -1;
                        let valA = a[this.sortCol];
                        let valB = b[this.sortCol];

                        if (typeof valA === 'string') valA = valA.toLowerCase();
                        if (typeof valB === 'string') valB = valB.toLowerCase();

                        if (valA < valB) return -1 * mod;
                        if (valA > valB) return 1 * mod;
                        return 0;
                    });
                },

                init() {
                    // Pusher Setup
                    window.pusher = window.pusher || new Pusher("{{ env('PUSHER_APP_KEY') }}", {
                        cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
                        forceTLS: true
                    });

                    const channel = window.pusher.subscribe('tickets');

                    channel.bind('ticket.updated', (data) => {
                        if (data.ticket) {
                            const index = this.tickets.findIndex(t => t.id === data.ticket.id);
                            if (index !== -1) {
                                this.tickets[index].status = data.ticket.status;
                                this.tickets[index].admin_note = data.ticket.admin_note;
                                if (this.selectedTicket.id === data.ticket.id) {
                                    this.selectedTicket.status = data.ticket.status;
                                    this.selectedTicket.admin_note = data.ticket.admin_note;
                                    this.adminNote = data.ticket.admin_note;
                                }
                            } else {
                                location.reload();
                            }
                        }
                    });
                },

                resetFilters() {
                    this.filters.search = '';
                    this.filters.status = '';
                    this.filters.priority = '';
                    this.filters.month = '';
                    this.filters.year = '';
                },

                sortBy(col) {
                    if (this.sortCol === col) {
                        this.sortAsc = !this.sortAsc;
                    } else {
                        this.sortCol = col;
                        this.sortAsc = true;
                    }
                },

                async openDrawer(ticket) {
                    this.selectedTicket = ticket;
                    this.adminNote = ticket.admin_note || '';
                    this.drawerOpen = true;

                    if (ticket.status === 'Open' || ticket.status === 'In Progress') {
                        try {
                            const response = await axios.post(`/admin/tickets/${ticket.id}/open`);
                            if (response.data.success && response.data.updated) {
                                const idx = this.tickets.findIndex(t => t.id === ticket.id);
                                if(idx !== -1) this.tickets[idx].status = 'Open';
                                this.selectedTicket.status = 'Open';
                            }
                        } catch (error) {
                            console.error("Error opening ticket:", error);
                        }
                    }
                },

                async resolveTicket() {
                    if (!this.selectedTicket.id) return;
                    this.loading = true;
                    try {
                        const response = await axios.post(`/admin/tickets/${this.selectedTicket.id}/resolve`, {
                            admin_note: this.adminNote
                        });

                        if (response.data.success) {
                            const idx = this.tickets.findIndex(t => t.id === this.selectedTicket.id);
                            if(idx !== -1) {
                                this.tickets[idx].status = 'Resolved';
                                this.tickets[idx].admin_note = this.adminNote;
                            }
                            this.selectedTicket.status = 'Resolved';
                            alert('Ticket Resolved Successfully');
                        }
                    } catch (error) {
                        console.error("Error resolving ticket:", error);
                        alert('Failed to resolve ticket');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.7rem center;
            background-size: 1em;
        }
    </style>
@endsection