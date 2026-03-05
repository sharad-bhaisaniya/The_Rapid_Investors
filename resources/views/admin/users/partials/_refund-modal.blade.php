<div id="refundModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">

    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-900">Create Refund</h3>
            <button onclick="closeRefundModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Form --}}
        <form method="POST"
              action="{{ route('admin.refund.store') }}"
              enctype="multipart/form-data"
              class="p-6 space-y-4">
            @csrf

            {{-- Hidden IDs --}}
            <input type="hidden" name="user_id" id="refund_user_id">
            <input type="hidden" name="user_subscription_id" id="refund_subscription_id">

            {{-- Transaction ID --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">
                    Transaction ID
                </label>
                <input type="text" name="transaction_id" required
                       class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20">
            </div>

            {{-- Refund Amount --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">
                    Refund Amount
                </label>
                <input type="number" step="0.01" name="refund_amount" required
                       class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20">
            </div>

            {{-- Refund Reason --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">
                    Refund Reason (Customer)
                </label>
                <textarea name="refund_reason" rows="3" required
                          class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20"></textarea>
            </div>

            {{-- Admin Note --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">
                    Admin Note (Internal)
                </label>
                <textarea name="admin_note" rows="2"
                          class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20"></textarea>
            </div>

            {{-- Proof Image --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">
                    Refund Proof Image
                </label>
                <input type="file" name="refund_proof_image" accept="image/*"
                       class="w-full text-xs">
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-4">
                <button type="button"
                        onclick="closeRefundModal()"
                        class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-bold hover:bg-slate-200">
                    Cancel
                </button>

                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-black hover:bg-indigo-700">
                    Record Refund
                </button>
            </div>
        </form>
    </div>
</div>