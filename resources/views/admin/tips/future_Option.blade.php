@extends('layouts.app')

@section('content')

    <!-- @if ($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 rounded mb-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif -->

    <div class="bg-[#f0f2f5] font-sans min-h-screen" x-data="{ showCategoryModal: false }">
        <div class="max-w-[1400px] mx-auto ">

           <div id="notification-stack" class="fixed top-5 right-5 z-[200] flex flex-col gap-3 w-80">
                {{-- 1. Standard Laravel Session Success --}}
                @if (session('success'))
                    <div class="p-4 bg-emerald-500 text-white rounded-xl shadow-2xl flex items-start gap-3 animate-fade-in pointer-events-auto">
                        <i class="fa-solid fa-circle-check mt-1"></i>
                        <div class="text-xs font-bold">{{ session('success') }}</div>
                        <button onclick="this.parentElement.remove()" class="ml-auto opacity-50 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                {{-- 2. Standard Laravel Validation Errors --}}
                @if ($errors->any())
                    <div class="p-4 bg-red-600 text-white rounded-xl shadow-2xl flex items-start gap-3 animate-fade-in">
                        <i class="fa-solid fa-circle-exclamation mt-1"></i>
                        <div class="flex-1">
                            <p class="text-[10px] font-black uppercase mb-1 tracking-wider">Validation Errors</p>
                            <ul class="text-[11px] list-disc pl-3 opacity-90">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button onclick="this.parentElement.remove()" class="opacity-50 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                {{-- 3. Dynamic AJAX Template (Hidden by default) --}}
                <div id="ajax-toast" class="hidden p-4 rounded-xl shadow-2xl flex items-start gap-3 animate-fade-in">
                    <i id="toast-icon" class="fa-solid mt-1"></i>
                    <div class="flex-1">
                        <p id="toast-title" class="text-[10px] font-black uppercase mb-1 tracking-wider"></p>
                        <div id="toast-content" class="text-[11px] opacity-90"></div>
                    </div>
                    <button onclick="document.getElementById('ajax-toast').classList.add('hidden')" class="opacity-50 hover:opacity-100">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('tips.derivative.store') }}" method="POST" id="mainTipForm" enctype="multipart/form-data">
                @csrf

                {{-- HIDDEN INPUTS: These ensure data reaches the controller --}}
                <input type="hidden" name="tip_type" id="tip_type" value="future">
                <input type="hidden" name="category_id" id="selected_category" value="{{ old('category_id') }}">
                <input type="hidden" name="call_type" id="selected_call" value="{{ old('call_type', 'Buy') }}">
                <input type="hidden" name="option_type" id="selected_option_type" value="{{ old('option_type', 'CE') }}">

                {{-- CRITICAL: Token and Full Symbol Name for DB --}}
                <input type="hidden" name="symbol_token" id="symbol_token" value="{{ old('symbol_token') }}">
                <input type="hidden" name="stock_name" id="real_stock_name" value="{{ old('stock_name') }}">

                {{-- NEW: formatted expiry from frontend (YYYY-MM-DD) --}}
                <input type="hidden" name="expiry_date_formatted" id="expiry_date_formatted" value="">

                <div class="flex justify-between items-center ">
                    <div class="flex gap-2">
                        <a href="{{ route('admin.tips.create') }}"
                            class="w-[12rem] flex items-center space-x-2 bg-blue-700 text-[10px] font-black text-[#fdfdfd] px-3 py-3.5 my-3 rounded-lg border border-blue-100 hover:bg-blue-600 hover:text-white transition-all">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                            <span class="tracking-wider">GO TO EQUITY TIPS</span>
                        </a>

                        <a href="{{ route('admin.tips.index') }}"
                            class="w-[12rem] flex items-center space-x-2 bg-blue-700 text-[10px] font-black text-[#fdfdfd] px-3 py-3.5 my-3 rounded-lg border border-blue-100 hover:bg-blue-600 hover:text-white transition-all">
                            <i class="fa-solid fa-list"></i>
                            <span>SHOW ALL TIPS</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[250px_1fr] gap-4">

                    {{-- SIDEBAR --}}
                    <div class="space-y-4">
                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200" x-data="{ deleteMode: false }">
                            <div class="flex justify-between items-center mb-3">
                                <h2
                                    class="text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span> Category
                                </h2>
                                <div class="flex gap-2">
                                    <button type="button" @click="showCategoryModal = true"
                                        class="w-6 h-6 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-full hover:bg-emerald-600 hover:text-white transition-all shadow-sm border border-emerald-100">
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                    </button>
                                    <button type="button" @click="deleteMode = !deleteMode"
                                        class="w-6 h-6 flex items-center justify-center bg-red-50 text-red-600 rounded-full hover:bg-red-600 hover:text-white transition-all shadow-sm border border-red-100">
                                        <i class="fa-solid fa-minus text-[10px]"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach ($categories as $cat)
                                    <div class="relative category-item border border-gray-100 rounded-md p-2 text-[10px] font-bold text-center cursor-pointer select-box hover:bg-gray-50 {{ old('category_id') == $cat->id ? 'active-box' : '' }}"
                                        data-id="{{ $cat->id }}">
                                        <button type="button" x-show="deleteMode" x-transition
                                            @click.stop.prevent="deleteCategory({{ $cat->id }})"
                                            class="absolute -top-1 -right-1 w-4 h-4 bg-red-600 text-white rounded-full">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        {{ $cat->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
                            <h2
                                class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center">
                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span> Visibility
                            </h2>
                            <div class="grid grid-cols-1 gap-1.5">
                                @foreach ($plans as $plan)
                                    <div class="plan-checkbox">
                                        <input type="checkbox" name="plans[]" value="{{ $plan->id }}"
                                            id="plan_{{ $plan->id }}" class="hidden peer">
                                        <label for="plan_{{ $plan->id }}"
                                            class="block border border-gray-100 rounded-md p-2 text-[10px] font-bold text-center cursor-pointer transition-all peer-checked:bg-[#2a5298] peer-checked:text-white hover:bg-gray-50">
                                            {{ $plan->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- MAIN CONTENT AREA --}}
                    <div class="space-y-4" x-data="stockSearchDerivative()" x-init="init()">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-visible relative z-10">

                            {{-- Top Bar: Future/Option + Attachment + Buy/Sell --}}
                            <div
                                class="bg-gray-50 border-b border-gray-100 p-3 flex flex-wrap items-center justify-between gap-4">

                                {{-- Instrument Switch --}}
                                <div class="flex items-center space-x-2 bg-white border rounded-lg p-1">
                                    <div class="px-4 py-1.5 text-[11px] font-black cursor-pointer select-box active-box rounded-md"
                                        data-single="instrument" data-type="future">FUTURE</div>
                                    <div class="px-4 py-1.5 text-[11px] font-black cursor-pointer select-box rounded-md"
                                        data-single="instrument" data-type="option">OPTION</div>
                                </div>

                                {{-- File Attachment (JPEG + PDF) --}}
                                <div x-data="{
                                    fileName: '',
                                    filePreview: null,
                                    isPdf: false,
                                    handleFile(event) {
                                        const file = event.target.files[0];
                                        if (!file) return;
                                
                                        this.fileName = file.name;
                                        this.isPdf = file.type === 'application/pdf';
                                        this.filePreview = null;
                                
                                        if (!this.isPdf) {
                                            const reader = new FileReader();
                                            reader.onload = (e) => {
                                                this.filePreview = e.target.result;
                                            };
                                            reader.readAsDataURL(file);
                                        }
                                    },
                                    clearFile() {
                                        this.fileName = '';
                                        this.filePreview = null;
                                        this.isPdf = false;
                                        this.$el.querySelector('input[type=file]').value = '';
                                    }
                                }" class="relative flex items-center">
                                    <label
                                        class="flex items-center gap-1.5 px-2 py-1 bg-white border border-gray-200 rounded-md cursor-pointer hover:bg-gray-50 transition-all">

                                        {{-- Image Preview --}}
                                        <template x-if="filePreview">
                                            <img :src="filePreview"
                                                class="w-4 h-4 rounded-sm object-cover border border-gray-100"
                                                alt="Chart Preview">
                                        </template>

                                        {{-- PDF Icon --}}
                                        <template x-if="isPdf">
                                            <i class="fa-solid fa-file-pdf text-red-500 text-[12px]"></i>
                                        </template>

                                        {{-- Default Icon --}}
                                        <template x-if="!filePreview && !isPdf">
                                            <i class="fa-solid fa-paperclip text-gray-400 text-[10px]"></i>
                                        </template>

                                        <span class="text-[9px] font-bold text-gray-500 uppercase"
                                            x-text="fileName ? 'Change' : 'Attach Chart'"></span>

                                        <input type="file" name="attachment" class="hidden"
                                            accept=".jpg,.jpeg,.png,.webp,.pdf" @change="handleFile($event)">
                                    </label>

                                    {{-- File name + remove --}}
                                    <template x-if="fileName">
                                        <div class="flex items-center">
                                            <span
                                                class="ml-2 text-[9px] font-medium text-emerald-600 truncate max-w-[90px]"
                                                x-text="fileName"></span>
                                            <button type="button" @click="clearFile()"
                                                class="ml-1 text-red-400 hover:text-red-600">
                                                <i class="fa-solid fa-circle-xmark text-[10px]"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                {{-- Buy/Sell Switch --}}
                                <div class="flex items-center space-x-2 bg-white border rounded-lg p-1">
                                    <div class="px-6 py-1.5 text-[11px] font-black cursor-pointer buy-select-box select-box {{ old('call_type', 'Buy') == 'Buy' ? 'buy-active' : '' }} rounded-md"
                                        data-single="trade" data-value="Buy">BUY</div>
                                    <div class="px-6 py-1.5 text-[11px] font-black cursor-pointer sell-select-box select-box {{ old('call_type') == 'Sell' ? 'sell-active' : '' }} rounded-md"
                                        data-single="trade" data-value="Sell">SELL</div>
                                </div>
                            </div>

                            <div class="p-6">

                                {{-- 
                                ==========================================
                                NEW SCRIPT SELECTION LOGIC (Cascading)
                                ==========================================
                            --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

                                    {{-- 1. Exchange --}}
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-gray-400 uppercase mb-1">Exchange</label>
                                        <select name="exchange" x-model="currentExchange" @change="resetSearch"
                                            class="w-full border-b-2 border-gray-100 focus:border-[#2a5298] py-2 text-base font-bold outline-none bg-transparent">
                                            <option value="NSE">NSE (NFO)</option>
                                            <option value="BSE">BSE (BFO)</option>
                                            <option value="MCX">MCX</option>
                                        </select>
                                    </div>

                                    {{-- 2. Script Name Search --}}
                                    <div class="relative">
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Script
                                            Name</label>
                                        <input type="text" x-model="search" @input="searchNames()"
                                            @click.outside="isOpen = false" placeholder="e.g. NIFTY" autocomplete="off"
                                            class="w-full border-b-2 border-gray-100 focus:border-[#2a5298] py-2 text-base font-bold uppercase outline-none transition-all">

                                        {{-- Loader --}}
                                        <div x-show="isLoading" class="absolute right-0 top-8 text-gray-400 text-xs"><i
                                                class="fa-solid fa-circle-notch fa-spin"></i></div>

                                        {{-- Results --}}
                                        <div x-show="isOpen && filteredNames.length" x-cloak
                                            class="absolute z-50 w-full bg-white shadow-xl border border-gray-100 max-h-60 overflow-y-auto mt-1 rounded-md">
                                            <template x-for="name in filteredNames" :key="name">
                                                <div @click="selectName(name)"
                                                    class="px-4 py-2 hover:bg-gray-50 cursor-pointer text-sm font-bold text-gray-700 border-b border-gray-50"
                                                    x-text="name"></div>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- 3. Expiry Date --}}
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Expiry
                                            Date</label>
                                        <select name="expiry_date" x-model="selectedExpiry" @change="expiryChanged"
                                            class="w-full border-b-2 border-gray-100 focus:border-[#2a5298] py-2 text-base font-bold outline-none bg-transparent">
                                            <option value="">Select Expiry</option>
                                            <template x-for="exp in expiries" :key="exp">
                                                <option :value="exp" x-text="exp"></option>
                                            </template>
                                        </select>
                                        <div x-show="loadingExpiries" class="text-[9px] text-gray-400 text-right">Loading
                                            Dates...</div>
                                    </div>
                                </div>

                                {{-- Option Specific Fields --}}
                                <div id="optionFields"
                                    class="hidden bg-blue-50/50 p-4 rounded-xl border border-blue-100 grid grid-cols-2 gap-6 mb-8 animate-fade-in">
                                    <div>
                                        <label class="block text-[10px] font-black text-blue-600 uppercase mb-2">Option
                                            Type</label>
                                        <div class="flex bg-white rounded-lg p-1 border border-blue-200">
                                            <div class="flex-1 py-1.5 text-[10px] font-bold text-center cursor-pointer select-box active-box rounded-md"
                                                data-single="cepe" data-value="CE" @click="setOptionType('CE')">CALL (CE)
                                            </div>
                                            <div class="flex-1 py-1.5 text-[10px] font-bold text-center cursor-pointer select-box rounded-md"
                                                data-single="cepe" data-value="PE" @click="setOptionType('PE')">PUT (PE)
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-blue-600 uppercase mb-2">Strike
                                            Price</label>
                                        <select name="strike_price" x-model="strikePrice" @change="fetchToken"
                                            class="w-full bg-white border border-blue-200 rounded-lg p-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-blue-400">
                                            <option value="">Select Strike</option>
                                            <template x-for="stk in availableStrikes" :key="stk">
                                                <option :value="stk" x-text="stk"></option>
                                            </template>
                                        </select>
                                        <div x-show="loadingStrikes" class="text-[9px] text-blue-400 text-right">Fetching
                                            Strikes...</div>
                                    </div>
                                </div>

                                {{-- Selected Contract Info (Confirmation) --}}
                                <div x-show="selectedTokenSymbol"
                                    class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg text-center">
                                    <span class="text-[10px] text-green-600 font-bold uppercase block">Ready to
                                        Publish</span>
                                    <span class="text-sm font-black text-green-800" x-text="selectedTokenSymbol"></span>
                                </div>
                                <div x-show="tokenError"
                                    class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-center text-red-600 text-xs font-bold"
                                    x-text="tokenError"></div>

                                {{-- Calculator Section --}}
                                <div class="flex items-center space-x-4 mb-6">
                                    <select
                                        class="calc-mode-selector outline-none border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold">
                                        <option value="percentage">Percentage (%)</option>
                                        <option value="price">Fixed Price (₹)</option>
                                        <option value="fixed_percentage">Fixed % (Auto T2 & SL)</option>
                                        <option value="fixed_price">Fixed ₹ (Auto T2 & SL)</option>
                                    </select>
                                    <input type="number" step="any"
                                        class="manual-target-input manual-t1 outline-none border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold"
                                        placeholder="T1 gap (% or ₹)">
                                    <input type="number" step="any"
                                        class="manual-target-input manual-t2 outline-none border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold"
                                        placeholder="T2 gap (% or ₹)">
                                    <input type="number" step="any"
                                        class="manual-sl-input manual-sl outline-none border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold"
                                        placeholder="SL gap (% or ₹)">
                                </div>

                                <div class="bg-gray-900 rounded-2xl p-1 shadow-inner">
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-px">
                                        <div class="bg-white p-4 rounded-tl-xl md:rounded-l-xl">
                                            <label class="block text-[9px] font-black text-blue-500 uppercase mb-1">Entry
                                                Price</label>
                                            <input type="number" step="any" name="entry_price" id="entry_price"
                                                placeholder="0.00" class="w-full text-xl font-black outline-none">
                                        </div>
                                        <div class="bg-white p-4">
                                            <label
                                                class="block text-[9px] font-black text-yellow-600 uppercase mb-1">Current
                                                Market</label>
                                            <div class="relative">
                                                <input type="number" step="any" name="cmp_price" id="cmp_price"
                                                    placeholder="0.00"
                                                    class="w-full text-xl font-black outline-none text-yellow-600">
                                                <div id="price_loader" class="hidden absolute right-0 top-1"><i
                                                        class="fa-solid fa-spinner fa-spin text-gray-300 text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-white p-4">
                                            <label class="block text-[9px] font-black text-green-500 uppercase mb-1">Target
                                                01</label>
                                            <input type="number" step="any" name="target_price" id="target_1"
                                                placeholder="0.00"
                                                class="w-full text-xl font-black outline-none text-green-600">
                                        </div>
                                        <div class="bg-white p-4">
                                            <label
                                                class="block text-[9px] font-black text-emerald-600 uppercase mb-1">Target
                                                02</label>
                                            <input type="number" step="any" name="target_price_2" id="target_2"
                                                placeholder="0.00"
                                                class="w-full text-xl font-black outline-none text-emerald-700">
                                        </div>
                                        <div class="bg-white p-4 rounded-br-xl md:rounded-r-xl">
                                            <label class="block text-[9px] font-black text-red-500 uppercase mb-1">Stop
                                                Loss</label>
                                            <input type="number" step="any" name="stop_loss" id="stop_loss"
                                                placeholder="0.00"
                                                class="w-full text-xl font-black outline-none text-red-600">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 flex items-center justify-between gap-4">

                                    <button type="reset" onclick="window.location.reload()"
                                        class="text-[10px] font-black text-gray-400 hover:text-red-500 uppercase tracking-widest transition-colors">Clear
                                        Draft</button>
                                    {{-- Footer Remarks (Editable with Default Text) --}}
                                    <div class="mt-5 w-1/2">
                                        <label class="block text-[13px] font-bold text-gray-500 mb-1">
                                            Remarks / Note (Optional)
                                        </label>

                                        <textarea name="remarks" rows="3"
                                            class="w-full min-w-full text-[13px] text-gray-700 border border-gray-300 rounded-xl px-4 pt-3
               focus:outline-none focus:ring-2 focus:ring-blue-300 resize-none">{{ old('remarks', '⚠️ Note: Targets and stop loss are indicative. Please trade with proper risk management.') }}</textarea>
                                    </div>


                                    <button type="submit" id="publishBtn"
                                        class="bg-[#2a5298] text-white px-12 py-3.5 rounded-xl font-black text-xs shadow-lg hover:shadow-[#2a5298]/30 transition-all uppercase tracking-[2px]">Publish
                                        Tip Now</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Category Modal --}}
        <div x-show="showCategoryModal" x-cloak x-transition.opacity
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-[110] p-4">
            <div @click.away="showCategoryModal = false"
                class="bg-white w-full max-w-md rounded-[24px] shadow-2xl overflow-hidden border border-gray-100 animate-fade-in">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em]">Create New Category</h3>
                    <button @click="showCategoryModal = false"
                        class="text-gray-400 hover:text-gray-900 transition-colors"><i
                            class="fa-solid fa-xmark text-lg"></i></button>
                </div>
                <form action="{{ route('admin.tips.category.store') }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Category
                            Name</label>
                        <input type="text" name="name" required autofocus placeholder="e.g. Jackpot, Intraday"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showCategoryModal = false"
                            class="flex-1 py-3.5 bg-gray-100 text-gray-500 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-200 transition-all">Cancel</button>
                        <button type="submit"
                            class="flex-1 py-3.5 bg-emerald-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all">Publish
                            Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.RISK_MASTER = @json($riskMaster ?? null);
    </script>

    <script>
        // Utility: parse expiry strings like "10FEB2026" -> "2026-02-10"
        function parseExpiryStringToISO(val) {
            if (!val) return null;
            val = val.toString().trim().toUpperCase();

            // If already YYYY-MM-DD, return as-is
            if (/^\d{4}-\d{2}-\d{2}$/.test(val)) return val;

            // Pattern like 10FEB2026 or 1JAN2026
            const m = val.match(/^(\d{1,2})([A-Z]{3,9})(\d{2,4})$/);
            if (!m) return null;

            let day = m[1].padStart(2, '0');
            let monStr = m[2].slice(0, 3).toUpperCase();
            let year = m[3].length === 2 ? ('20' + m[3]) : m[3];

            const months = {
                JAN: '01',
                FEB: '02',
                MAR: '03',
                APR: '04',
                MAY: '05',
                JUN: '06',
                JUL: '07',
                AUG: '08',
                SEP: '09',
                OCT: '10',
                NOV: '11',
                DEC: '12'
            };
            const mm = months[monStr] ?? null;
            if (!mm) return null;
            return `${year}-${mm}-${day}`;
        }

        // Before submit: convert expiry to formatted value
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('mainTipForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    // take value from select[name="expiry_date"]
                    const sel = form.querySelector('[name="expiry_date"]');
                    const out = document.getElementById('expiry_date_formatted');
                    if (sel && out) {
                        const iso = parseExpiryStringToISO(sel.value);
                        if (iso) out.value = iso;
                        else out.value = ''; // leave blank if unparseable (server will validate)
                    }
                });
            }
        });
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('stockSearchDerivative', () => ({
                search: '',
                filteredNames: [],
                expiries: [],
                availableStrikes: [],

                isOpen: false,
                isLoading: false,
                loadingExpiries: false,
                loadingStrikes: false,

                currentExchange: 'NSE',
                currentInstrument: 'future',
                selectedName: '',
                selectedExpiry: '',
                strikePrice: '',
                optionRight: 'CE',

                selectedTokenSymbol: '',
                tokenError: '',
                searchDebounce: null,

                init() {
                    window.addEventListener('instrument-changed', (e) => {
                        this.currentInstrument = e.detail.type;

                        // If switching to option, we might need to fetch strikes if expiry is already selected
                        if (this.currentInstrument === 'option' && this.selectedExpiry) {
                            this.fetchStrikes();
                        } else {
                            this.fetchToken();
                        }
                    });
                },

                // 1. Search Unique Names from API
                searchNames() {
                    if (this.search.length < 2) {
                        this.filteredNames = [];
                        return;
                    }
                    this.isLoading = true;
                    this.isOpen = true;

                    if (this.searchDebounce) clearTimeout(this.searchDebounce);

                    this.searchDebounce = setTimeout(async () => {
                        try {
                            const params = new URLSearchParams({
                                query: this.search,
                                exchange: this.currentExchange
                            });
                            const res = await fetch(
                                `/api/angel/search-names?${params.toString()}`);
                            if (!res.ok) throw new Error('API Error');
                            const json = await res.json();
                            this.filteredNames = json.data || [];
                        } catch (e) {
                            console.error(e);
                        } finally {
                            this.isLoading = false;
                        }
                    }, 300);
                },

                // // 2. Select Name -> Fetch Expiries
             


    async selectName(name) {
    // ---------------- UI RESET ----------------
    this.search = name;
    this.selectedName = name;
    this.isOpen = false;
    this.filteredNames = [];
    this.selectedExpiry = '';
    this.expiries = [];
    this.availableStrikes = [];
    this.strikePrice = '';
    this.selectedTokenSymbol = '';

    this.loadingExpiries = true;

    try {
        const params = new URLSearchParams({
            name: name,
            exchange: this.currentExchange
        });

        const res = await fetch(`/api/angel/expiries?${params.toString()}`);
        const json = await res.json();

        let expiries = json.data || [];

        /* ----------------------------------------------------
           👉 helper: Angel expiry (17FEB2026) → Date
        ---------------------------------------------------- */
        const parseAngelExpiry = (expiry) => {
            const day = parseInt(expiry.slice(0, 2));
            const monthStr = expiry.slice(2, 5);
            const year = parseInt(expiry.slice(5, 9));
            const month = new Date(Date.parse(monthStr + " 1")).getMonth();
            return new Date(year, month, day);
        };

        /* ----------------------------------------------------
           👉 Today date (remove expired)
        ---------------------------------------------------- */
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        expiries = expiries.filter(exp => {
            return parseAngelExpiry(exp) >= today;
        });

        /* ----------------------------------------------------
           🛢️ MCX / COMMODITY FIX
           same month → keep latest expiry
        ---------------------------------------------------- */
        if (this.currentExchange === 'MCX') {
            const monthMap = {};

            expiries.forEach(exp => {
                const date = parseAngelExpiry(exp);
                const key = `${date.getFullYear()}-${date.getMonth()}`;

                if (!monthMap[key] || date > parseAngelExpiry(monthMap[key])) {
                    monthMap[key] = exp;
                }
            });

            expiries = Object.values(monthMap);
        }

        /* ----------------------------------------------------
           📌 SORTING (nearest first – common)
        ---------------------------------------------------- */
        expiries = expiries.sort(
            (a, b) => parseAngelExpiry(a) - parseAngelExpiry(b)
        );

        /* ----------------------------------------------------
           📌 NIFTY / BANKNIFTY → only 3 upcoming expiries
        ---------------------------------------------------- */
        if (
            this.currentExchange === 'NSE' &&
            (name === 'NIFTY' || name === 'BANKNIFTY')
        ) {
            expiries = expiries.slice(0, 3);
        }

        /* ----------------------------------------------------
           ✅ SET EXPIRIES + AUTO SELECT (PRICE FIX)
        ---------------------------------------------------- */
        this.expiries = expiries;

        

    } catch (e) {
        console.error(e);
    } finally {
        this.loadingExpiries = false;
    }
},





                // 3. Expiry Changed Logic
                expiryChanged() {
                    if (this.currentInstrument === 'option') {
                        this.fetchStrikes(); // If Option, get strikes
                    } else {
                        this.fetchToken(); // If Future, get token immediately
                    }
                },

                // 4. Fetch Strikes from API
                async fetchStrikes() {
                    if (!this.selectedName || !this.selectedExpiry) return;

                    this.loadingStrikes = true;
                    this.availableStrikes = [];
                    this.strikePrice = ''; // Reset selected strike

                    try {
                        const params = new URLSearchParams({
                            name: this.selectedName,
                            expiry: this.selectedExpiry,
                            exchange: this.currentExchange
                        });
                        const res = await fetch(`/api/angel/get-strikes?${params.toString()}`);
                        const json = await res.json();
                        this.availableStrikes = json.data || [];
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loadingStrikes = false;
                    }
                },

                setOptionType(type) {
                    this.optionRight = type;
                    document.getElementById('selected_option_type').value = type;
                    this.fetchToken();
                },

                resetSearch() {
                    this.search = '';
                    this.selectedName = '';
                    this.selectedExpiry = '';
                    this.expiries = [];
                    this.availableStrikes = [];
                    this.selectedTokenSymbol = '';
                    this.filteredNames = [];
                },

                async fetchToken() {
                    if (!this.selectedName || !this.selectedExpiry) return;

                    if (this.currentInstrument === 'option' && !this.strikePrice) return;

                    this.tokenError = '';
                    this.selectedTokenSymbol = 'Fetching...';

                    try {
                        const params = new URLSearchParams({
                            name: this.selectedName,
                            exchange: this.currentExchange,
                            expiry: this.selectedExpiry,
                            type: this.currentInstrument,
                            strike: this.strikePrice,
                            right: this.optionRight
                        });

                        const res = await fetch(`/api/angel/find-token?${params.toString()}`);
                        const json = await res.json();

                        if (json.status && json.data) {
                            const stock = json.data;
                            this.selectedTokenSymbol = stock.symbol || stock.name || 'Unknown';

                            const tokenVal = stock.token ?? stock.symboltoken ?? stock.tokenId ??
                                '';
                            document.getElementById('symbol_token').value = tokenVal;
                            document.getElementById('real_stock_name').value = stock.symbol ?? stock
                                .name ?? '';

                            this.fetchQuote(tokenVal);
                        } else {
                            this.selectedTokenSymbol = '';
                            this.tokenError = json.message || 'Contract not found.';
                        }

                    } catch (e) {
                        this.selectedTokenSymbol = '';
                        this.tokenError = 'Error fetching contract.';
                    }
                },

                async fetchQuote(token) {
                    const cmpInput = document.getElementById('cmp_price');
                    const entryInput = document.getElementById('entry_price');
                    const loader = document.getElementById('price_loader');

                    if (loader) loader.classList.remove('hidden');

                    let apiExch = (this.currentExchange === 'NSE') ? 'NFO' : ((this
                        .currentExchange === 'BSE') ? 'BFO' : this.currentExchange);

                    try {
                        const res = await fetch(
                            `/api/angel/quote?symbol=${token}&exchange=${apiExch}`);
                        const json = await res.json();

                        let ltp = 0;
                        if (json.status && json.data) {
                            const dataPoint = json.data.fetched ? json.data.fetched[0] : json.data;
                            if (dataPoint) ltp = parseFloat(dataPoint.ltp);
                        }

                        if (ltp > 0) {
                            if (cmpInput) cmpInput.value = ltp;
                            if (entryInput) {
                                entryInput.value = ltp;
                                entryInput.dispatchEvent(new Event('input'));
                            }
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        if (loader) loader.classList.add('hidden');
                    }
                }
            }));
        });
    </script>

    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // move all legacy DOM query listeners inside DOMContentLoaded for safety

            // select-box click handler (categories, trade, instrument, etc.)
            const selectBoxes = document.querySelectorAll('.select-box');
            Array.from(selectBoxes).forEach(box => {
                box.addEventListener('click', () => {
                    const group = box.dataset.single;
                    const isCategory = box.classList.contains('category-item');

                    if (isCategory) {
                        document.querySelectorAll('.category-item').forEach(b => b.classList.remove(
                            'active-box'));
                        box.classList.add('active-box');
                        const idVal = box.dataset.id ?? '';
                        const el = document.getElementById('selected_category');
                        if (el) el.value = idVal;
                        return;
                    }

                    if (group) {
                        document.querySelectorAll(`[data-single="${group}"]`).forEach(b => {
                            b.classList.remove('active-box', 'buy-active', 'sell-active');
                        });

                        if (group === 'trade') {
                            const val = box.dataset.value;
                            const hidden = document.getElementById('selected_call');
                            if (hidden) hidden.value = val;
                            box.classList.add(val === 'Buy' ? 'buy-active' : 'sell-active');
                        } else if (group === 'instrument') {
                            box.classList.add('active-box');
                            const type = box.dataset.type;
                            const hidden = document.getElementById('tip_type');
                            if (hidden) hidden.value = type;
                            const optionFields = document.getElementById('optionFields');
                            if (optionFields) {
                                if (type === 'option') optionFields.classList.remove('hidden');
                                else optionFields.classList.add('hidden');
                            }
                            window.dispatchEvent(new CustomEvent('instrument-changed', {
                                detail: {
                                    type: type
                                }
                            }));
                        } else if (group === 'cepe') {
                            box.classList.add('active-box');
                        }
                    }
                });
            });

          
            // Calculator support – FULL UPDATED (with Fixed ₹ Auto)
            (function() {
                const entry = document.getElementById('entry_price');
                const callType = document.getElementById('selected_call');
                const t1Final = document.getElementById('target_1');
                const t2Final = document.getElementById('target_2');
                const slFinal = document.getElementById('stop_loss');
                const modeSelect = document.querySelector('.calc-mode-selector');
                const manualT1 = document.querySelector('.manual-t1');
                const manualT2 = document.querySelector('.manual-t2');
                const manualSL = document.querySelector('.manual-sl');

                if (!entry || !modeSelect || !callType) return;

                /* -----------------------------
                   Helpers
                ----------------------------- */

                function isBuyCall() {
                    return callType.value === 'Buy';
                }

                function hasManualInput() {
                    const mode = modeSelect.value;

                    // Auto modes need only T1
                    if (mode === 'fixed_percentage' || mode === 'fixed_price') {
                        return manualT1 && manualT1.value;
                    }

                    // Manual modes
                    return (
                        (manualT1 && manualT1.value) ||
                        (manualT2 && manualT2.value) ||
                        (manualSL && manualSL.value)
                    );
                }

                function calculatePrice(gapValue, type) {
                    const entryPrice = parseFloat(entry.value);
                    if (!entryPrice || !gapValue) return '';

                    const isBuy = isBuyCall();
                    const mode = modeSelect.value;
                    const gap = parseFloat(gapValue);

                    let diff = 0;

                    if (mode === 'percentage') {
                        diff = (entryPrice * gap) / 100;
                    } else {
                        // price OR fixed_price
                        diff = gap;
                    }

                    if (type === 'target') {
                        return isBuy ? entryPrice + diff : entryPrice - diff;
                    } else {
                        return isBuy ? entryPrice - diff : entryPrice + diff;
                    }
                }

                /* -----------------------------
                   Apply Logic
                ----------------------------- */

                function applyManual() {
                    const mode = modeSelect.value;
                    const entryPrice = parseFloat(entry.value);
                    if (!entryPrice) return;

                    const isBuy = isBuyCall();

                    /* 🔥 FIXED ₹ AUTO MODE */
                    if (mode === 'fixed_price') {
                        const gap = parseFloat(manualT1.value);
                        if (isNaN(gap)) return;

                        t1Final.value = (isBuy ? entryPrice + gap : entryPrice - gap).toFixed(2);
                        t2Final.value = (isBuy ? entryPrice + gap * 2 : entryPrice - gap * 2).toFixed(2);
                        slFinal.value = (isBuy ? entryPrice - gap : entryPrice + gap).toFixed(2);
                        return;
                    }

                    /* 🔵 FIXED % AUTO MODE */
                    if (mode === 'fixed_percentage') {
                        const gapPercent = parseFloat(manualT1.value);
                        if (isNaN(gapPercent)) return;

                        const t1Diff = (entryPrice * gapPercent) / 100;
                        const t2Diff = (entryPrice * (gapPercent * 2)) / 100;

                        t1Final.value = (isBuy ? entryPrice + t1Diff : entryPrice - t1Diff).toFixed(2);
                        t2Final.value = (isBuy ? entryPrice + t2Diff : entryPrice - t2Diff).toFixed(2);
                        slFinal.value = (isBuy ? entryPrice - t1Diff : entryPrice + t1Diff).toFixed(2);
                        return;
                    }

                    /* 🟢 MANUAL MODES (percentage / price) */
                    if (manualT1 && manualT1.value) {
                        const v = calculatePrice(manualT1.value, 'target');
                        if (v !== '') t1Final.value = v.toFixed(2);
                    }

                    if (manualT2 && manualT2.value) {
                        const v = calculatePrice(manualT2.value, 'target');
                        if (v !== '') t2Final.value = v.toFixed(2);
                    }

                    if (manualSL && manualSL.value) {
                        const v = calculatePrice(manualSL.value, 'sl');
                        if (v !== '') slFinal.value = v.toFixed(2);
                    }
                }

                /* -----------------------------
                   UI / Recalculate
                ----------------------------- */

                function smartRecalculate() {
                    const mode = modeSelect.value;

                    if (mode === 'fixed_percentage' || mode === 'fixed_price') {
                        if (manualT2) {
                            manualT2.disabled = true;
                            manualT2.value = '';
                            manualT2.placeholder = 'Auto Calculated';
                        }
                        if (manualSL) {
                            manualSL.disabled = true;
                            manualSL.value = '';
                            manualSL.placeholder = 'Auto Calculated';
                        }
                    } else {
                        if (manualT2) {
                            manualT2.disabled = false;
                            manualT2.placeholder = 'T2 gap (% or ₹)';
                        }
                        if (manualSL) {
                            manualSL.disabled = false;
                            manualSL.placeholder = 'SL gap (% or ₹)';
                        }
                    }

                    if (hasManualInput()) {
                        applyManual();
                    } else if (typeof calculateFromRiskMasterFNO === 'function') {
                        calculateFromRiskMasterFNO();
                    }
                }

                /* -----------------------------
                   Events
                ----------------------------- */

                [manualT1, manualT2, manualSL].forEach(input => {
                    if (input) input.addEventListener('input', smartRecalculate);
                });

                entry.addEventListener('input', smartRecalculate);
                modeSelect.addEventListener('change', smartRecalculate);

                const tradeBtns = document.querySelectorAll('[data-single="trade"]');
                Array.from(tradeBtns).forEach(btn =>
                    btn.addEventListener('click', () => setTimeout(smartRecalculate, 50))
                );
            })();


            // deleteCategory remains same but moved inside DOM loaded
            window.deleteCategory = function(categoryId) {
                if (!confirm('Are you sure you want to delete this category?')) return;
                fetch(`/admin/tips-categories/${categoryId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    }).then(resp => {
                        if (!resp.ok) throw new Error('Delete failed');
                        const el = document.querySelector(`[data-id="${categoryId}"]`);
                        if (el) el.remove();
                    })
                    .catch(error => alert('Unable to delete category.'));
            };
            
        });
    </script> -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. DOM Elements
            const form = document.getElementById('mainTipForm');
            const ajaxToast = document.getElementById('ajax-toast');
            const toastIcon = document.getElementById('toast-icon');
            const toastTitle = document.getElementById('toast-title');
            const toastContent = document.getElementById('toast-content');
            const submitBtn = document.getElementById('publishBtn');

            // 2. Select-box Click Handler (Old Functionality Preserved)
            const selectBoxes = document.querySelectorAll('.select-box');
            Array.from(selectBoxes).forEach(box => {
                box.addEventListener('click', () => {
                    const group = box.dataset.single;
                    const isCategory = box.classList.contains('category-item');

                    if (isCategory) {
                        document.querySelectorAll('.category-item').forEach(b => b.classList.remove('active-box'));
                        box.classList.add('active-box');
                        const idVal = box.dataset.id ?? '';
                        const el = document.getElementById('selected_category');
                        if (el) el.value = idVal;
                        return;
                    }

                    if (group) {
                        document.querySelectorAll(`[data-single="${group}"]`).forEach(b => {
                            b.classList.remove('active-box', 'buy-active', 'sell-active');
                        });

                        if (group === 'trade') {
                            const val = box.dataset.value;
                            const hidden = document.getElementById('selected_call');
                            if (hidden) hidden.value = val;
                            box.classList.add(val === 'Buy' ? 'buy-active' : 'sell-active');
                        } else if (group === 'instrument') {
                            box.classList.add('active-box');
                            const type = box.dataset.type;
                            const hidden = document.getElementById('tip_type');
                            if (hidden) hidden.value = type;
                            const optionFields = document.getElementById('optionFields');
                            if (optionFields) {
                                if (type === 'option') optionFields.classList.remove('hidden');
                                else optionFields.classList.add('hidden');
                            }
                            window.dispatchEvent(new CustomEvent('instrument-changed', { detail: { type: type } }));
                        } else if (group === 'cepe') {
                            box.classList.add('active-box');
                        }
                    }
                });
            });

            // 3. AJAX Submit Handler (Prevents Reload & Handles Toast)
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault(); // 🛑 Stops the page reload

                    // A. Expiry Formatting Logic (Integrated)
                    const sel = form.querySelector('[name="expiry_date"]');
                    const out = document.getElementById('expiry_date_formatted');
                    if (sel && out) {
                        const iso = parseExpiryStringToISO(sel.value);
                        out.value = iso || '';
                    }

                    // B. UI Loading State
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Publishing...';
                    ajaxToast.classList.add('hidden');

                    // C. Prepare Data
                    const formData = new FormData(form);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const result = await response.json();

                        // Setup Toast appearance
                        ajaxToast.classList.remove('hidden', 'bg-red-600', 'bg-emerald-500', 'text-white');
                        ajaxToast.classList.add('text-white');

                        if (response.ok) {
                            // ✅ SUCCESS
                            ajaxToast.classList.add('bg-emerald-500');
                            toastIcon.className = 'fa-solid fa-circle-check mt-1';
                            toastTitle.innerText = 'SUCCESS';
                            toastContent.innerText = result.message || 'Tip Published!';
                            
                            form.reset();
                            // Clear manual selectors visual state
                            document.querySelectorAll('.select-box').forEach(b => b.classList.remove('active-box', 'buy-active', 'sell-active'));
                            
                            // Reset Alpine state (optional)
                            const alpineEl = document.querySelector('[x-data="stockSearchDerivative()"]');
                            if (alpineEl && window.Alpine) {
                                const data = Alpine.$data(alpineEl);
                                data.search = '';
                                data.expiries = [];
                                data.selectedTokenSymbol = '';
                            }
                        } else {
                            // ❌ VALIDATION ERROR
                            ajaxToast.classList.add('bg-red-600');
                            toastIcon.className = 'fa-solid fa-circle-exclamation mt-1';
                            toastTitle.innerText = 'VALIDATION ERROR';
                            
                            if (result.errors) {
                                let errHtml = '<ul class="list-disc pl-3">';
                                Object.values(result.errors).flat().forEach(err => errHtml += `<li>${err}</li>`);
                                errHtml += '</ul>';
                                toastContent.innerHTML = errHtml;
                            } else {
                                toastContent.innerText = result.message || 'Check your inputs.';
                            }
                        }
                    } catch (error) {
                        // 💀 CRITICAL ERROR
                        ajaxToast.classList.remove('hidden');
                        ajaxToast.classList.add('bg-red-600', 'text-white');
                        toastIcon.className = 'fa-solid fa-triangle-exclamation mt-1';
                        toastTitle.innerText = 'SYSTEM ERROR';
                        toastContent.innerText = 'Server error or invalid JSON response.';
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        setTimeout(() => ajaxToast.classList.add('hidden'), 8000);
                    }
                });
            }

            // 4. Calculator support logic (Existing IIFE Preserved)
            (function() {
                const entry = document.getElementById('entry_price');
                const callType = document.getElementById('selected_call');
                const t1Final = document.getElementById('target_1');
                const t2Final = document.getElementById('target_2');
                const slFinal = document.getElementById('stop_loss');
                const modeSelect = document.querySelector('.calc-mode-selector');
                const manualT1 = document.querySelector('.manual-t1');
                const manualT2 = document.querySelector('.manual-t2');
                const manualSL = document.querySelector('.manual-sl');

                if (!entry || !modeSelect || !callType) return;

                const isBuyCall = () => callType.value === 'Buy';

                function hasManualInput() {
                    const mode = modeSelect.value;
                    if (mode === 'fixed_percentage' || mode === 'fixed_price') return manualT1 && manualT1.value;
                    return ((manualT1 && manualT1.value) || (manualT2 && manualT2.value) || (manualSL && manualSL.value));
                }

                function calculatePrice(gapValue, type) {
                    const entryPrice = parseFloat(entry.value);
                    if (!entryPrice || !gapValue) return '';
                    const isBuy = isBuyCall();
                    const mode = modeSelect.value;
                    const gap = parseFloat(gapValue);
                    let diff = (mode === 'percentage') ? (entryPrice * gap) / 100 : gap;
                    return (type === 'target') ? (isBuy ? entryPrice + diff : entryPrice - diff) : (isBuy ? entryPrice - diff : entryPrice + diff);
                }

                function applyManual() {
                    const mode = modeSelect.value;
                    const entryPrice = parseFloat(entry.value);
                    if (!entryPrice) return;
                    const isBuy = isBuyCall();

                    if (mode === 'fixed_price') {
                        const gap = parseFloat(manualT1.value);
                        if (isNaN(gap)) return;
                        t1Final.value = (isBuy ? entryPrice + gap : entryPrice - gap).toFixed(2);
                        t2Final.value = (isBuy ? entryPrice + gap * 2 : entryPrice - gap * 2).toFixed(2);
                        slFinal.value = (isBuy ? entryPrice - gap : entryPrice + gap).toFixed(2);
                        return;
                    }

                    if (mode === 'fixed_percentage') {
                        const gapPercent = parseFloat(manualT1.value);
                        if (isNaN(gapPercent)) return;
                        const t1Diff = (entryPrice * gapPercent) / 100;
                        const t2Diff = (entryPrice * (gapPercent * 2)) / 100;
                        t1Final.value = (isBuy ? entryPrice + t1Diff : entryPrice - t1Diff).toFixed(2);
                        t2Final.value = (isBuy ? entryPrice + t2Diff : entryPrice - t2Diff).toFixed(2);
                        slFinal.value = (isBuy ? entryPrice - t1Diff : entryPrice + t1Diff).toFixed(2);
                        return;
                    }

                    if (manualT1 && manualT1.value) { const v = calculatePrice(manualT1.value, 'target'); if (v !== '') t1Final.value = v.toFixed(2); }
                    if (manualT2 && manualT2.value) { const v = calculatePrice(manualT2.value, 'target'); if (v !== '') t2Final.value = v.toFixed(2); }
                    if (manualSL && manualSL.value) { const v = calculatePrice(manualSL.value, 'sl'); if (v !== '') slFinal.value = v.toFixed(2); }
                }

                function smartRecalculate() {
                    const mode = modeSelect.value;
                    if (mode === 'fixed_percentage' || mode === 'fixed_price') {
                        [manualT2, manualSL].forEach(m => { if(m) { m.disabled = true; m.value = ''; m.placeholder = 'Auto Calculated'; }});
                    } else {
                        if (manualT2) { manualT2.disabled = false; manualT2.placeholder = 'T2 gap (% or ₹)'; }
                        if (manualSL) { manualSL.disabled = false; manualSL.placeholder = 'SL gap (% or ₹)'; }
                    }
                    hasManualInput() ? applyManual() : (typeof calculateFromRiskMasterFNO === 'function' ? calculateFromRiskMasterFNO() : null);
                }

                [manualT1, manualT2, manualSL].forEach(input => { if (input) input.addEventListener('input', smartRecalculate); });
                entry.addEventListener('input', smartRecalculate);
                modeSelect.addEventListener('change', smartRecalculate);
                document.querySelectorAll('[data-single="trade"]').forEach(btn => btn.addEventListener('click', () => setTimeout(smartRecalculate, 50)));
            })();

            // 5. Global Functions
            window.deleteCategory = function(categoryId) {
                if (!confirm('Are you sure?')) return;
                fetch(`/admin/tips-categories/${categoryId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                }).then(resp => {
                    if (resp.ok) document.querySelector(`[data-id="${categoryId}"]`).remove();
                });
            };
        });
</script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .active-box {
            background-color: #2a5298 !important;
            color: white !important;
            border-color: #2a5298 !important;
        }

        .buy-active {
            background-color: #10b981 !important;
            color: white !important;
            border-color: #10b981 !important;
        }

        .sell-active {
            background-color: #ef4444 !important;
            color: white !important;
            border-color: #ef4444 !important;
        }

        .select-box {
            transition: all 0.15s ease-in-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.2s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
