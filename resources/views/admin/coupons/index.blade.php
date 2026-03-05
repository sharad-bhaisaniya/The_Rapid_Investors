@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6 font-sans antialiased" x-data="couponMaster({{ $coupons->toJson() }})">
    
    <!-- Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-indigo-100 bg-indigo-50/50 text-indigo-600 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse"></span>
                <span class="text-[9px] font-black uppercase tracking-[0.2em]">Promotion Registry</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Coupon Management</h1>
            <p class="text-slate-500 text-sm">Create and track high-performance discount campaigns.</p>
        </div>
        <button @click="openForm()"
            class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold transition-all shadow-lg shadow-indigo-200 hover:-translate-y-0.5 active:translate-y-0">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Create New Coupon
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Registry</p>
            <p class="text-3xl font-black text-slate-900" x-text="coupons.length"></p>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Active Codes</p>
            <p class="text-3xl font-black text-emerald-600" x-text="coupons.filter(c => c.active).length"></p>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Global Redeemed</p>
            <p class="text-3xl font-black text-indigo-600" x-text="coupons.reduce((acc, c) => acc + (parseInt(c.used_global) || 0), 0)"></p>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden ring-1 ring-slate-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Coupon Code</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Benefit Type</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Global Limit</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">User Limit</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Expiry</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <template x-for="coupon in coupons" :key="coupon.id">
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-8 py-6">
                                <span class="font-mono font-black text-indigo-600 bg-indigo-50/50 px-3 py-1.5 rounded-lg text-sm tracking-wider uppercase" x-text="coupon.code"></span>
                                <div class="mt-2 text-[10px] text-slate-400 font-bold uppercase tracking-tight" x-show="coupon.min_amount">
                                    Min: ₹<span x-text="coupon.min_amount"></span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-lg font-black text-slate-900" x-text="coupon.value"></span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase" x-text="coupon.type === 'flat' ? 'Flat INR' : '% Percent'"></span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-slate-700" x-text="coupon.used_global || 0"></span>
                                    <span class="text-slate-300">/</span>
                                    <span class="text-sm font-bold text-slate-400" x-text="coupon.global_limit ?? '∞'"></span>
                                </div>
                                <div class="w-20 h-1 bg-slate-100 rounded-full mt-2 overflow-hidden">
                                    <div class="h-full bg-indigo-500" :style="`width: ${coupon.global_limit ? Math.min((coupon.used_global / coupon.global_limit) * 100, 100) : 0}%`" x-show="coupon.global_limit"></div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-50 text-[11px] font-bold text-slate-600 border border-slate-100">
                                    <span x-text="coupon.per_user_limit ? `${coupon.per_user_limit} Times` : 'Unlimited'"></span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-[13px] font-medium text-slate-500" x-text="coupon.expires_at ? new Date(coupon.expires_at).toLocaleDateString('en-GB') : 'Permanent'"></span>
                            </td>
                            <td class="px-8 py-6">
                                <button @click="toggleStatus(coupon)"
                                    :class="coupon.active ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100' : 'bg-slate-50 text-slate-400 ring-1 ring-slate-100'"
                                    class="px-4 py-1 rounded-full font-black text-[9px] tracking-widest uppercase transition-all hover:scale-105 active:scale-95">
                                    <span x-text="coupon.active ? '● Active' : '○ Inactive'"></span>
                                </button>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editCoupon(coupon)" class="p-2.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button @click="confirmDelete(coupon.id)" class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CREATE/EDIT FORM MODAL -->
    <div x-show="showForm" 
         class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         style="display: none;">
        
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden ring-1 ring-black/5" @click.away="closeForm">
            <div class="px-10 py-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                <div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight" x-text="editingId ? 'Modify Coupon' : 'New Promotional Code'"></h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Institutional Campaign Settings</p>
                </div>
                <button @click="closeForm" class="w-10 h-10 flex items-center justify-center bg-white rounded-full shadow-sm text-slate-400 hover:text-slate-600 transition-all text-xl">&times;</button>
            </div>
            
            <div class="p-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Unique Access Code</label>
                        <input x-model="form.code" class="input uppercase font-mono tracking-widest text-base" placeholder="LAUNCH2026">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Discount Type</label>
                        <select x-model="form.type" class="input">
                            <option value="flat">Flat Discount (INR)</option>
                            <option value="percent">Percentage Off (%)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Benefit Value</label>
                        <input x-model="form.value" type="number" class="input" placeholder="0.00">
                    </div>

                    <div class="col-span-1 md:col-span-2 mt-4">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="h-px flex-1 bg-slate-100"></span>
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Usage Guardrails</span>
                            <span class="h-px flex-1 bg-slate-100"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Min. Transaction (₹)</label>
                        <input x-model="form.min_amount" type="number" class="input" placeholder="Leave empty for zero">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Global Usage Limit</label>
                        <input x-model="form.global_limit" type="number" class="input" placeholder="Empty = Unlimited">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Per-User Limit</label>
                        <input x-model="form.per_user_limit" type="number" class="input" placeholder="Empty = Unlimited">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Expiration Date</label>
                        <input x-model="form.expires_at" type="date" class="input">
                    </div>
                </div>
            </div>

            <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-50 flex gap-4 justify-end">
                <button @click="closeForm" class="px-6 py-2.5 text-[13px] font-bold text-slate-500 hover:text-slate-800 transition-colors">Discard</button>
                <button @click="saveCoupon" 
                        class="bg-indigo-600 text-white px-10 py-2.5 rounded-2xl font-bold shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">
                    <span x-text="editingId ? 'Apply Updates' : 'Launch Coupon'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div x-show="showDeleteModal" 
         class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm"
         x-transition style="display: none;">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-sm w-full text-center shadow-2xl ring-1 ring-black/5">
            <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-3xl flex items-center justify-center mx-auto mb-6 rotate-3">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <h3 class="text-2xl font-black text-slate-900 mb-2 tracking-tight">Delete Coupon?</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-8">This action is permanent and will invalidate the code for all future users.</p>
            <div class="flex gap-4">
                <button @click="showDeleteModal = false" class="flex-1 px-6 py-3 bg-slate-50 text-slate-600 rounded-2xl font-bold hover:bg-slate-100">Cancel</button>
                <button @click="deleteCoupon" class="flex-1 px-6 py-3 bg-rose-600 text-white rounded-2xl font-bold shadow-lg shadow-rose-200 hover:bg-rose-700 transition-all">Delete</button>
            </div>
        </div>
    </div>

</div>

<style>
    .input {
        background-color: #f8fafc;
        border: 2px solid #f1f5f9;
        padding: .85rem 1.25rem;
        border-radius: 1.25rem;
        width: 100%;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
    }
    .input:focus {
        border-color: #6366f1;
        background-color: #fff;
        box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.08);
    }
    .input::placeholder {
        color: #cbd5e1;
        font-weight: 500;
        text-transform: none;
        letter-spacing: normal;
    }
</style>

<script>
function couponMaster(data) {
    return {
        coupons: data,
        showForm: false,
        showDeleteModal: false,
        editingId: null,
        deletingId: null,

        form: {
            code: '',
            type: 'flat',
            value: '',
            min_amount: '',
            per_user_limit: '',
            global_limit: '',
            expires_at: ''
        },

        openForm() {
            this.resetForm();
            this.showForm = true;
        },

        closeForm() {
            this.showForm = false;
        },

        editCoupon(c) {
            this.form = { 
                ...c,
                // Ensure NULL values from DB show as empty strings in the input for UX
                global_limit: c.global_limit === null ? '' : c.global_limit,
                per_user_limit: c.per_user_limit === null ? '' : c.per_user_limit,
                min_amount: c.min_amount === null ? '' : c.min_amount,
            };
            this.editingId = c.id;
            this.showForm = true;
        },

        confirmDelete(id) {
            this.deletingId = id;
            this.showDeleteModal = true;
        },

        async saveCoupon() {
            let url = this.editingId ? `/admin/coupons/${this.editingId}` : '/admin/coupons';
            let method = this.editingId ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                if (!res.ok) {
                    const errData = await res.json();
                    throw new Error(errData.message || "Failed to save coupon.");
                }

                const updatedData = await res.json();

                if (this.editingId) {
                    const index = this.coupons.findIndex(c => c.id == this.editingId);
                    this.coupons[index] = updatedData;
                } else {
                    this.coupons.unshift(updatedData);
                }

                this.closeForm();
            } catch (e) {
                alert(e.message);
            }
        },

        async deleteCoupon() {
            try {
                const res = await fetch(`/admin/coupons/${this.deletingId}`, {
                    method: 'DELETE',
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if(res.ok) {
                    this.coupons = this.coupons.filter(c => c.id !== this.deletingId);
                    this.showDeleteModal = false;
                }
            } catch (e) {
                alert("Deletion error.");
            }
        },

        async toggleStatus(coupon) {
            const originalStatus = coupon.active;
            coupon.active = !coupon.active; // Optimistic UI update

            try {
                const res = await fetch(`/admin/coupons/${coupon.id}/toggle`, {
                    method: 'PATCH',
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if(!res.ok) throw new Error();
            } catch (e) {
                coupon.active = originalStatus; // Rollback
                alert("Toggle failed.");
            }
        },

        resetForm() {
            this.editingId = null;
            this.form = {
                code: '',
                type: 'flat',
                value: '',
                min_amount: '',
                per_user_limit: '',
                global_limit: '',
                expires_at: ''
            }
        }
    }
}
</script>
@endsection