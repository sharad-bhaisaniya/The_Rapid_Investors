

@extends('layouts.app')

@section('content')
    <style>
        /* Professional filtration styles */
        .filter-input {
            transition: all 0.2s ease-in-out;
        }
        .filter-input:focus {
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }

        /* Tab Styles */
        .tab-btn {
            position: relative;
            transition: all 0.3s ease;
        }
        .tab-btn.active {
            color: #2563eb;
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #2563eb;
        }
    </style>

    <main class="p-4 md:p-6">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-1">Customer Management</h2>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Manage and filter your platform users</p>
            </div>
        <div class="flex items-end gap-2">
    {{-- From Date --}}
    <input type="date" id="exportFrom"
        class="filter-input bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-[10px] font-bold"
        title="From Date">

    {{-- To Date --}}
    <input type="date" id="exportTo"
        class="filter-input bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-[10px] font-bold"
        title="To Date">

    {{-- Export Button --}}
    <button onclick="exportWithFilters()" 
        class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition flex items-center gap-2 shadow-lg shadow-emerald-100">
        <i class="fa-solid fa-file-csv text-sm"></i>
        Export CSV
    </button>
</div>
        </div>

        {{-- NEW TABS SECTION --}}
        <div class="flex items-center gap-8 border-b border-gray-100 mb-6 px-2">
            <!-- <button onclick="setTab('all')" id="tab-all" class="tab-btn active pb-3 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-slate-900">
                All Users
            </button> -->
            <button onclick="setTab('active')" id="tab-active" class="tab-btn active pb-3 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-slate-900 flex items-center gap-2">
                Active <span class="bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full text-[10px]">{{ $users->where('status', 'Active')->count() }}</span>
            </button>
            <button onclick="setTab('inactive')" id="tab-inactive" class="tab-btn pb-3 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-slate-900 flex items-center gap-2">
                Inactive <span class="bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full text-[10px]">{{ $users->where('status', 'Inactive')->count() }}</span>
            </button>
        </div>

        {{-- ADVANCED FILTRATION GRID --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-10 gap-4 items-end">
                
                {{-- Search --}}
                <div class="col-span-2 lg:col-span-3">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Search Customer</label>
                    <div class="relative">
                        <input type="text" id="searchInput" 
                            placeholder="Name, Email, or Phone..."
                            class="filter-input w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs font-bold outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute right-4 top-3.5 text-gray-300"></i>
                    </div>
                </div>

                {{-- Role --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Role</label>
                    <select id="roleFilter" class="filter-input w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs font-bold outline-none">
                        <option value="">All</option>
                        @php
                            $allRoles = [];
                            foreach ($users as $u) {
                                // Checking if it's an array (from manual collection) or object (Eloquent)
                                $r = is_array($u) ? $u['role'] : $u->getRoleNames()->first();
                                if ($r && !in_array($r, $allRoles)) $allRoles[] = $r;
                            }
                        @endphp
                        @foreach ($allRoles as $role)
                            <option value="{{ $role }}">{{ strtoupper($role) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date --}}
                <div class="col-span-1 lg:col-span-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Exact Date</label>
                    <input id="dateFilter" type="date" class="filter-input w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-xs font-bold outline-none">
                </div>

                {{-- Month --}}
                <div class="col-span-1 lg:col-span-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Month</label>
                    <select id="monthFilter" class="filter-input w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs font-bold outline-none">
                        <option value="">All Months</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ sprintf('%02d', $m) }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Year --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Year</label>
                    <select id="yearFilter" class="filter-input w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs font-bold outline-none">
                        <option value="">All Years</option>
                        @for ($y = date('Y'); $y >= 2023; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Reset --}}
                <div class="col-span-1 lg:col-span-1">
                    <button id="resetFilters" class="w-full bg-gray-100 text-gray-500 py-2.5 rounded-xl font-black text-[10px] uppercase flex items-center justify-center hover:bg-gray-200 transition-all gap-2">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
            </div>
        </div>



        <!-- Deleted User Toast -->
<div id="deletedUserToast"
     class="fixed top-6 right-6 z-50 hidden items-center gap-3 
            bg-rose-600 text-white px-5 py-3 rounded-xl shadow-2xl
            text-sm font-bold tracking-wide">
    <i class="fa-solid fa-circle-exclamation text-lg"></i>
    <span>This user has been deleted and details are not available.</span>
</div>

        {{-- DESKTOP TABLE --}}
        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden desktop-table">
            <div class="overflow-x-auto">
                <table class="w-full text-xs" id="usersTable">
                    <thead class="bg-gray-50 text-gray-500 border-b border-gray-100">
                        <tr>
                            <th class="p-4 text-left font-black uppercase tracking-widest">#</th>
                            <th class="p-4 text-left font-black uppercase tracking-widest">Identity</th>
                            <th class="p-4 text-left font-black uppercase tracking-widest">Contact Info</th>
                            <th class="p-4 text-center font-black uppercase tracking-widest">Status</th>
                            <th class="p-4 text-center font-black uppercase tracking-widest">Joined Date</th>
                            <th class="p-4 text-right font-black uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="tableBody">
                        @foreach ($users as $user)
                            @php
                                // Handle data whether it is an Eloquent object or a manual array from Controller
                                $isDeleted = is_array($user) ? ($user['status'] === 'Inactive') : false;
                                $name = is_array($user) ? $user['name'] : $user->name;
                                $email = is_array($user) ? $user['email'] : $user->email;
                                $phone = is_array($user) ? $user['phone'] : ($user->phone ?? '');
                                $role = is_array($user) ? $user['role'] : ($user->getRoleNames()->first() ?? 'Customer');
                                $profileImageUrl = is_array($user) ? $user['profile_image'] : $user->getFirstMediaUrl('profile_images');
                                $status = is_array($user) ? $user['status'] : 'Active';
                                
                                $createdAtObj = is_array($user) ? \Carbon\Carbon::parse($user['created_at']) : $user->created_at;
                                $createdDate = $createdAtObj ? $createdAtObj->format('Y-m-d') : '';
                                $createdMonth = $createdAtObj ? $createdAtObj->format('m') : '';
                                $createdYear = $createdAtObj ? $createdAtObj->format('Y') : '';
                            @endphp

                            <tr class="hover:bg-blue-50/40 transition-colors user-row group" 
                                data-name="{{ strtolower($name) }}"
                                data-email="{{ strtolower($email) }}"
                                data-phone="{{ strtolower($phone) }}"
                                data-role="{{ strtolower($role) }}"
                                data-status="{{ strtolower($status) }}"
                                data-date="{{ $createdDate }}"
                                data-month="{{ $createdMonth }}"
                                data-year="{{ $createdYear }}">

                                <td class="p-4 text-gray-400 font-bold">
                                    {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                                </td>

                               <td class="p-4">
                                <div class="flex items-center gap-3">
                                    {{-- Avatar Section --}}
                                    @if ($profileImageUrl)
                                        <img src="{{ $profileImageUrl }}" alt="{{ $name }}" class="w-10 h-10 rounded-xl border border-gray-200 object-cover shadow-sm">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-black text-xs">
                                            {{ strtoupper(substr($name, 0, 2)) }}
                                        </div>
                                    @endif

                                    <div>
                                       {{-- Redirection Link on Name --}}
@if($status === 'Inactive')
    <a href="javascript:void(0);"
       onclick="showDeletedUserAlert()"
       class="font-black text-slate-900 text-sm tracking-tight hover:text-rose-600 transition-colors block cursor-not-allowed">
        {{ $name }}
    </a>
@else
    <a href="{{ route('admin.users.show', is_array($user) ? $user['id'] : $user->id) }}"
       class="font-black text-slate-900 text-sm tracking-tight hover:text-blue-600 transition-colors block">
        {{ $name }}
    </a>
@endif
                                        <span class="text-[10px] text-blue-500 font-black uppercase tracking-tighter">
                                            ID: {{ is_array($user) ? $user['id'] : $user->bsmr_id }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                                <td class="p-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700">{{ $email }}</span>
                                        <span class="text-[10px] font-bold text-gray-400 font-mono">{{ $phone ?: 'NO PHONE' }}</span>
                                    </div>
                                </td>

                                <td class="p-4 text-center">
                                    @if($status === 'Active')
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-600 rounded text-[9px] font-black uppercase tracking-widest">Active</span>
                                    @else
                                        <span class="px-2 py-1 bg-rose-100 text-rose-600 rounded text-[9px] font-black uppercase tracking-widest">Inactive</span>
                                    @endif
                                </td>

                                <td class="p-4 text-center">
                                    <span class="text-gray-500 font-bold">{{ $createdAtObj ? $createdAtObj->format('d M, Y') : '—' }}</span>
                                </td>

                               <td class="p-4 text-right">
    <div class="flex items-center justify-end gap-3">


@if($user['can_refund'])
    <button
        type="button"
        onclick="openRefundModal(
            {{ $user['id'] }},
            {{ $user['subscription_id'] }}
        )"
        class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg
               text-[10px] font-black uppercase tracking-widest
               hover:bg-indigo-700 transition">
        Refund
    </button>
@endif

      {{-- SHOW DELETE ONLY FOR ACTIVE USER AND NOT SUPER ADMIN --}}
@if($status === 'Active' && !(is_array($user) ? $user['role'] === 'super-admin' : $user->hasRole('super-admin')))
    <form 
        action="{{ route('admin.customers.destroy', is_array($user) ? $user['id'] : $user->id) }}" 
        method="POST"
        onsubmit="return confirm('Are you sure you want to delete this customer?');"
        class="inline-flex"
    >
        @csrf
        @method('DELETE')

        <button
            type="submit"
            class="flex items-center gap-1 text-rose-500 hover:scale-110 transition-transform font-bold uppercase text-[10px] tracking-widest"
            title="Delete Customer"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-3.5 w-3.5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3-3h4a1 1 0 011 1v1H9V5a1 1 011-1z" />
            </svg>
            Delete
        </button>
    </form>
@endif

    </div>
</td>
                            </tr>
                        @endforeach
                        
                        {{-- No Data Found Row --}}
                        <tr id="noDataRow" style="display: none;">
                            <td colspan="6" class="p-12 text-center text-gray-400 text-xs font-bold uppercase tracking-widest">
                                No customers found for the selected filters
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="bg-gray-50/50 p-5 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                    Showing {{ $users->firstItem() }} - {{ $users->lastItem() }} of {{ $users->total() }} Users
                </p>
                <div>{{ $users->links('pagination.dots') }}</div>
            </div>
        </div>


        @include('admin.users.partials._refund-modal')
    </main>

  <script>
    // ✅ Default tab = ACTIVE
    let currentStatusTab = 'active';

    function setTab(status) {
        currentStatusTab = status;

        // Update UI tabs
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('tab-' + status).classList.add('active');

        filterUsers();
    }

    function exportWithFilters() {
        const from = document.getElementById('exportFrom')?.value || '';
        const to   = document.getElementById('exportTo')?.value || '';

        const status = currentStatusTab || 'active';
        let url = `{{ route('users.export') }}?status=${status}`;

        if (from) url += `&from=${from}`;
        if (to)   url += `&to=${to}`;

        window.location.href = url;
    }

    function filterUsers() {
        const search = document.getElementById("searchInput").value.toLowerCase().trim();
        const role = document.getElementById("roleFilter").value.toLowerCase();
        const selectedDate = document.getElementById("dateFilter").value;
        const selectedMonth = document.getElementById("monthFilter").value;
        const selectedYear = document.getElementById("yearFilter").value;
        const rows = document.querySelectorAll(".user-row");
        let visibleCount = 0;

        rows.forEach(row => {
            const data = row.dataset;
            let isVisible = true;

            // ✅ ONLY ACTIVE OR INACTIVE (NO ALL)
            if (data.status !== currentStatusTab) {
                isVisible = false;
            }

            if (isVisible && search &&
                !(data.name.includes(search) ||
                  data.email.includes(search) ||
                  data.phone.includes(search))) {
                isVisible = false;
            }

            if (isVisible && role && data.role !== role) isVisible = false;
            if (isVisible && selectedDate && data.date !== selectedDate) isVisible = false;
            if (isVisible && selectedMonth && data.month !== selectedMonth) isVisible = false;
            if (isVisible && selectedYear && data.year !== selectedYear) isVisible = false;

            row.style.display = isVisible ? "" : "none";
            if (isVisible) visibleCount++;
        });

        document.getElementById("noDataRow").style.display = visibleCount === 0 ? "" : "none";
    }

    // Attach listeners
    [
        document.getElementById("searchInput"),
        document.getElementById("roleFilter"),
        document.getElementById("dateFilter"),
        document.getElementById("monthFilter"),
        document.getElementById("yearFilter")
    ].forEach(el => {
        el.addEventListener("input", filterUsers);
        el.addEventListener("change", filterUsers);
    });

    // ✅ Reset goes back to ACTIVE (not ALL)
    document.getElementById("resetFilters").addEventListener("click", () => {
        document.getElementById("searchInput").value = "";
        document.getElementById("roleFilter").value = "";
        document.getElementById("dateFilter").value = "";
        document.getElementById("monthFilter").value = "";
        document.getElementById("yearFilter").value = "";
        setTab('active');
    });

    // ✅ IMPORTANT: run filter on page load
    filterUsers();
</script>

    <script>
function showDeletedUserAlert() {
    const toast = document.getElementById('deletedUserToast');

    if (!toast) return;

    // Show toast
    toast.classList.remove('hidden');
    toast.classList.add('flex');

    // Auto hide after 3 seconds
    setTimeout(() => {
        toast.classList.add('hidden');
        toast.classList.remove('flex');
    }, 3000);
}
</script>

<script>
    function openRefundModal(userId, subscriptionId) {
        document.getElementById('refund_user_id').value = userId;
        document.getElementById('refund_subscription_id').value = subscriptionId;

        const modal = document.getElementById('refundModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeRefundModal() {
        const modal = document.getElementById('refundModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection

