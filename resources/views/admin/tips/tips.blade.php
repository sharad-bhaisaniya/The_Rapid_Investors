@extends('layouts.app')

@section('content')

    {{-- 1. Global Risk Master Data --}}
    <script>
        window.RISK_MASTER = @json($riskMaster ?? null);
    </script>

    <div class="bg-[#f0f2f5] font-sans min-h-screen" x-data="{ showCategoryModal: false }">
        <div class="max-w-[1400px] mx-auto">


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




            <form action="{{ route('tips.equity.store') }}" method="POST" id="equityTipForm" enctype="multipart/form-data">
                @csrf

                {{-- HIDDEN INPUTS --}}
                <input type="hidden" name="tip_type" value="equity">
                <input type="hidden" name="category_id" id="selected_category" value="{{ old('category_id') }}">
                <input type="hidden" name="call_type" id="selected_call" value="{{ old('call_type', 'Buy') }}">

                {{-- CRITICAL: Token, Symbol Name & Exchange for DB --}}
                <input type="hidden" name="symbol_token" id="symbol_token" value="{{ old('symbol_token') }}">
                <input type="hidden" name="stock_name" id="real_stock_name" value="{{ old('stock_name') }}">
                <input type="hidden" name="exchange" id="selected_exchange" value="{{ old('exchange', 'NSE') }}">

                <div class="flex justify-between items-center mb-4">
                    <div class="flex gap-2">
                        <a href="{{ route('admin.tips.future_Option') }}"
                            class="w-[12rem] flex items-center space-x-2 bg-blue-700 text-[10px] font-black text-[#fdfdfd] px-3 py-3.5 rounded-lg border border-blue-100 hover:bg-blue-600 hover:text-white transition-all uppercase tracking-wider">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                            <span>GO TO FUTURE & OPTION</span>
                        </a>
                        <a href="{{ route('admin.tips.index') }}"
                            class="w-[12rem] flex items-center space-x-2 bg-blue-700 text-[10px] font-black text-[#fdfdfd] px-3 py-3.5 rounded-lg border border-blue-100 hover:bg-blue-600 hover:text-white transition-all uppercase tracking-wider">
                            <i class="fa-solid fa-list"></i>
                            <span>SHOW ALL TIPS</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[250px_1fr] gap-4">

                    {{-- LEFT SIDEBAR --}}
                    <div class="space-y-4">
                        {{-- Category --}}
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

                        {{-- Visibility --}}
                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
                            <h2
                                class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center">
                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span> Visibility
                            </h2>
                            <div class="grid grid-cols-1 gap-1.5">
                                @foreach ($plans as $plan)
                                    <div class="plan-checkbox">
                                        <input type="checkbox" name="plans[]" value="{{ $plan->id }}"
                                            id="plan_{{ $plan->id }}" class="hidden peer"
                                            {{ is_array(old('plans')) && in_array($plan->id, old('plans')) ? 'checked' : '' }}>
                                        <label for="plan_{{ $plan->id }}"
                                            class="block border border-gray-100 rounded-md p-2 text-[10px] font-bold text-center cursor-pointer transition-all peer-checked:bg-[#2a5298] peer-checked:text-white hover:bg-gray-50">
                                            {{ $plan->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- MAIN CONTENT --}}
                    <div class="space-y-4" x-data="stockSearchEquity()" x-init="init()">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-visible z-10 relative">

                            {{-- Top Bar --}}
                            <div
                                class="bg-gray-50 border-b border-gray-100 p-3 flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center">
                                    <span class="text-[11px] font-black text-[#2a5298] uppercase tracking-wider">Equity Cash
                                        Tip</span>
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

                                {{-- SEARCH LOGIC (Exchange -> Name -> Token) --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                                    {{-- 1. Exchange Selection --}}
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-gray-400 uppercase mb-1">Exchange</label>
                                        <div class="flex bg-gray-50 rounded-lg p-1 border border-gray-100">
                                            <div class="flex-1 py-1.5 text-[10px] font-bold text-center cursor-pointer select-box active-box rounded-md"
                                                data-single="exchange" data-value="NSE" @click="setExchange('NSE')">NSE
                                            </div>
                                            <div class="flex-1 py-1.5 text-[10px] font-bold text-center cursor-pointer select-box rounded-md"
                                                data-single="exchange" data-value="BSE" @click="setExchange('BSE')">BSE
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 2. Script Name Search (API Based) --}}
                                    <div class="relative">
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Script
                                            Name</label>
                                        <input type="text" x-model="search" @input="searchNames()"
                                            @click.outside="isOpen = false" placeholder="e.g. RELIANCE"
                                            autocomplete="off"
                                            class="w-full border-b-2 border-gray-100 focus:border-[#2a5298] py-2 text-base font-bold uppercase outline-none transition-all">

                                        {{-- Loader --}}
                                        <div x-show="isLoading" class="absolute right-0 top-8 text-gray-400 text-xs">
                                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                                        </div>

                                        {{-- Results --}}
                                        <div x-show="isOpen && filteredNames.length" x-cloak
                                            class="absolute z-50 w-full bg-white shadow-xl border border-gray-100 max-h-60 overflow-y-auto mt-1 rounded-md">
                                            <template x-for="name in filteredNames" :key="name">
                                                <div @click="selectName(name)"
                                                    class="px-4 py-2 hover:bg-gray-50 cursor-pointer text-sm font-bold text-gray-700 border-b border-gray-50"
                                                    x-text="name"></div>
                                            </template>
                                        </div>

                                        {{-- No Results Message --}}
                                        <div x-show="isOpen && !isLoading && filteredNames.length === 0 && search.length > 1"
                                            class="absolute z-50 w-full bg-white shadow-xl p-3 text-xs text-gray-500 mt-1 rounded-lg">
                                            No stocks found in <span x-text="currentExchange"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Selected Contract Info (Confirmation) --}}
                                <div x-show="selectedTokenSymbol"
                                    class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg text-center animate-fade-in">
                                    <span class="text-[10px] text-green-600 font-bold uppercase block">Ready to
                                        Publish</span>
                                    <span class="text-sm font-black text-green-800" x-text="selectedTokenSymbol"></span>
                                </div>
                                <div x-show="tokenError"
                                    class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-center text-red-600 text-xs font-bold"
                                    x-text="tokenError"></div>

                                {{-- Calculator --}}
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

                                {{-- Price Input Grid --}}
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

                                    <!-- <button type="submit"
                                        class="bg-[#2a5298] text-white px-12 py-3.5 rounded-xl font-black text-xs shadow-lg hover:shadow-[#2a5298]/30 transition-all uppercase tracking-[2px]">Publish
                                        Equity Tip</button> -->

                                        <button type="submit" id="submitBtn"
    class="bg-[#2a5298] text-white px-12 py-3.5 rounded-xl font-black text-xs shadow-lg hover:shadow-[#2a5298]/30 transition-all uppercase tracking-[2px] flex items-center gap-2">
    <span id="btnText">Publish Equity Tip</span>
    <i id="btnLoader" class="fa-solid fa-circle-notch fa-spin hidden"></i>
</button>
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
                        <input type="text" name="name" required autofocus placeholder="e.g. Intraday, Jackpot"
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

    {{-- DEPENDENCIES --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- 2. ALPINE JS: SEARCH & EXCHANGE LOGIC (CLEAN) --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('stockSearchEquity', () => ({
                search: '',
                filteredNames: [],
                isOpen: false,
                isLoading: false,
                currentExchange: 'NSE',
                selectedTokenSymbol: '',
                tokenError: '',
                searchDebounce: null,

                init() {},

                setExchange(val) {
                    this.currentExchange = val;
                    document.getElementById('selected_exchange').value = val;
                    this.search = '';
                    this.selectedTokenSymbol = '';
                    this.filteredNames = [];
                },

                // 1. Search Equity Names (New Route)
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
                            // 🔥 Calling New Equity-Specific Route
                            const res = await fetch(
                                `/api/angel/search-equity-names?${params.toString()}`);
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

                // 2. Select Name -> Find Equity Token (New Route)
                async selectName(name) {
                    this.search = name;
                    this.isOpen = false;
                    this.filteredNames = [];
                    this.selectedTokenSymbol = 'Fetching...';
                    this.tokenError = '';

                    try {
                        const params = new URLSearchParams({
                            name: name,
                            exchange: this.currentExchange
                        });

                        // 🔥 Calling New Equity-Specific Route
                        const res = await fetch(
                            `/api/angel/find-equity-token?${params.toString()}`);
                        const json = await res.json();

                        if (res.ok && json.status) {
                            const stock = json.data;
                            this.selectedTokenSymbol = stock.symbol;

                            document.getElementById('symbol_token').value = stock.token;
                            document.getElementById('real_stock_name').value = stock.symbol;

                            if (stock.token) {
                                this.fetchQuote(stock.token);
                            }
                        } else {
                            this.selectedTokenSymbol = '';
                            this.tokenError = json.message || 'Token not found.';
                        }
                    } catch (e) {
                        console.error(e);
                        this.selectedTokenSymbol = '';
                        this.tokenError = 'Error fetching details.';
                    }
                },

                // 3. Fetch Quote (Existing Shared Route)
                async fetchQuote(token) {
                    const cmpInput = document.getElementById('cmp_price');
                    const entryInput = document.getElementById('entry_price');
                    const loader = document.getElementById('price_loader');

                    if (loader) loader.classList.remove('hidden');

                    try {
                        const res = await fetch(
                            `/api/angel/quote?symbol=${token}&exchange=${this.currentExchange}`);
                        const json = await res.json();

                        let ltp = 0;
                        if (json.status && json.data) {
                            const dataPoint = json.data.fetched ? json.data.fetched[0] : json.data;
                            if (dataPoint && dataPoint.ltp) {
                                ltp = parseFloat(dataPoint.ltp);
                            }
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

    {{-- 3. VANILLA JS: UI INTERACTION & CALCULATOR --}}
    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Visual Selection
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
                        } else if (group === 'exchange') {
                            box.classList.add('active-box');
                        }
                    }
                });
            });

            // Delete Category Function
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
                }).catch(error => alert('Unable to delete category.'));
            };


            // --- CALCULATOR LOGIC (IIFE) ---
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

                /* -------------------------
                   Helpers
                ------------------------- */

                const isBuy = () => callType.value === 'Buy';

                function hasManualInput() {
                    const mode = modeSelect.value;

                    // Auto modes → only T1 needed
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

                    const gap = parseFloat(gapValue);
                    const mode = modeSelect.value;

                    let diff = 0;
                    if (mode === 'percentage') {
                        diff = (entryPrice * gap) / 100;
                    } else {
                        diff = gap; // price / fixed_price
                    }

                    if (type === 'target') {
                        return isBuy() ? entryPrice + diff : entryPrice - diff;
                    } else {
                        return isBuy() ? entryPrice - diff : entryPrice + diff;
                    }
                }

                /* -------------------------
                   Apply Logic
                ------------------------- */

                function applyManual() {
                    const mode = modeSelect.value;
                    const entryPrice = parseFloat(entry.value);
                    if (!entryPrice) return;

                    /* 🔥 FIXED ₹ AUTO (Universal Difference) */
                    if (mode === 'fixed_price') {
                        const gap = parseFloat(manualT1.value);
                        if (isNaN(gap)) return;

                        t1Final.value = (isBuy() ? entryPrice + gap : entryPrice - gap).toFixed(2);
                        t2Final.value = (isBuy() ? entryPrice + gap * 2 : entryPrice - gap * 2).toFixed(2);
                        slFinal.value = (isBuy() ? entryPrice - gap : entryPrice + gap).toFixed(2);
                        return;
                    }

                    /* 🔵 FIXED % AUTO */
                    if (mode === 'fixed_percentage') {
                        const gapPercent = parseFloat(manualT1.value);
                        if (isNaN(gapPercent)) return;

                        const t1Diff = (entryPrice * gapPercent) / 100;
                        const t2Diff = (entryPrice * (gapPercent * 2)) / 100;

                        t1Final.value = (isBuy() ? entryPrice + t1Diff : entryPrice - t1Diff).toFixed(2);
                        t2Final.value = (isBuy() ? entryPrice + t2Diff : entryPrice - t2Diff).toFixed(2);
                        slFinal.value = (isBuy() ? entryPrice - t1Diff : entryPrice + t1Diff).toFixed(2);
                        return;
                    }

                    /* 🟢 MANUAL MODES */
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

                /* -------------------------
                   Risk Master Fallback
                ------------------------- */

                function calculateFromRiskMaster() {
                    if (!window.RISK_MASTER) return;

                    const entryPrice = parseFloat(entry.value);
                    if (!entryPrice) return;

                    const type = window.RISK_MASTER.calculation_type;
                    const t1Gap = parseFloat(window.RISK_MASTER.target1_value);
                    const t2Gap = parseFloat(window.RISK_MASTER.target2_value);
                    const slGap = parseFloat(window.RISK_MASTER.stoploss_value);

                    const calc = (gap) =>
                        type === 'percentage' ? (entryPrice * gap) / 100 : gap;

                    t1Final.value = (isBuy() ? entryPrice + calc(t1Gap) : entryPrice - calc(t1Gap)).toFixed(2);
                    t2Final.value = t2Gap ?
                        (isBuy() ? entryPrice + calc(t2Gap) : entryPrice - calc(t2Gap)).toFixed(2) :
                        '';
                    slFinal.value = (isBuy() ? entryPrice - calc(slGap) : entryPrice + calc(slGap)).toFixed(2);
                }

                /* -------------------------
                   UI Recalculate
                ------------------------- */

                function smartRecalculate() {
                    const mode = modeSelect.value;

                    // Disable T2 & SL for auto modes
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
                    } else {
                        calculateFromRiskMaster();
                    }
                }

                /* -------------------------
                   Events
                ------------------------- */

                [manualT1, manualT2, manualSL].forEach(input => {
                    if (input) input.addEventListener('input', smartRecalculate);
                });

                entry.addEventListener('input', smartRecalculate);
                modeSelect.addEventListener('change', smartRecalculate);

                document.querySelectorAll('[data-single="trade"]').forEach(btn => {
                    btn.addEventListener('click', () => setTimeout(smartRecalculate, 50));
                });
            })();


        });
    </script> -->

    {{-- 3. VANILLA JS: UI INTERACTION & CALCULATOR --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. HANDLE VISUAL SELECTION ---
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
                        } else if (group === 'exchange') {
                            box.classList.add('active-box');
                        }
                    }
                });
            });

            // --- 2. DELETE CATEGORY ---
            window.deleteCategory = function(categoryId) {
                if (!confirm('Are you sure you want to delete this category?')) return;
                fetch(`/admin/tips-categories/${categoryId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(resp => {
                    if (!resp.ok) throw new Error('Delete failed');
                    const el = document.querySelector(`[data-id="${categoryId}"]`);
                    if (el) el.remove();
                }).catch(error => alert('Unable to delete category.'));
            };

            // --- 3. CALCULATOR LOGIC (IIFE) ---
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

                const isBuy = () => callType.value === 'Buy';

                function hasManualInput() {
                    const mode = modeSelect.value;
                    if (mode === 'fixed_percentage' || mode === 'fixed_price') return manualT1 && manualT1.value;
                    return ((manualT1 && manualT1.value) || (manualT2 && manualT2.value) || (manualSL && manualSL.value));
                }

                function calculatePrice(gapValue, type) {
                    const entryPrice = parseFloat(entry.value);
                    if (!entryPrice || !gapValue) return '';
                    const gap = parseFloat(gapValue);
                    const mode = modeSelect.value;
                    let diff = (mode === 'percentage') ? (entryPrice * gap) / 100 : gap;

                    if (type === 'target') return isBuy() ? entryPrice + diff : entryPrice - diff;
                    return isBuy() ? entryPrice - diff : entryPrice + diff;
                }

                function applyManual() {
                    const mode = modeSelect.value;
                    const entryPrice = parseFloat(entry.value);
                    if (!entryPrice) return;

                    if (mode === 'fixed_price') {
                        const gap = parseFloat(manualT1.value);
                        if (isNaN(gap)) return;
                        t1Final.value = (isBuy() ? entryPrice + gap : entryPrice - gap).toFixed(2);
                        t2Final.value = (isBuy() ? entryPrice + gap * 2 : entryPrice - gap * 2).toFixed(2);
                        slFinal.value = (isBuy() ? entryPrice - gap : entryPrice + gap).toFixed(2);
                        return;
                    }

                    if (mode === 'fixed_percentage') {
                        const gapPercent = parseFloat(manualT1.value);
                        if (isNaN(gapPercent)) return;
                        const t1Diff = (entryPrice * gapPercent) / 100;
                        const t2Diff = (entryPrice * (gapPercent * 2)) / 100;
                        t1Final.value = (isBuy() ? entryPrice + t1Diff : entryPrice - t1Diff).toFixed(2);
                        t2Final.value = (isBuy() ? entryPrice + t2Diff : entryPrice - t2Diff).toFixed(2);
                        slFinal.value = (isBuy() ? entryPrice - t1Diff : entryPrice + t1Diff).toFixed(2);
                        return;
                    }

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

                function calculateFromRiskMaster() {
                    if (!window.RISK_MASTER) return;
                    const entryPrice = parseFloat(entry.value);
                    if (!entryPrice) return;
                    const type = window.RISK_MASTER.calculation_type;
                    const t1Gap = parseFloat(window.RISK_MASTER.target1_value);
                    const t2Gap = parseFloat(window.RISK_MASTER.target2_value);
                    const slGap = parseFloat(window.RISK_MASTER.stoploss_value);
                    const calc = (gap) => type === 'percentage' ? (entryPrice * gap) / 100 : gap;

                    t1Final.value = (isBuy() ? entryPrice + calc(t1Gap) : entryPrice - calc(t1Gap)).toFixed(2);
                    t2Final.value = t2Gap ? (isBuy() ? entryPrice + calc(t2Gap) : entryPrice - calc(t2Gap)).toFixed(2) : '';
                    slFinal.value = (isBuy() ? entryPrice - calc(slGap) : entryPrice + calc(slGap)).toFixed(2);
                }

                function smartRecalculate() {
                    const mode = modeSelect.value;
                    if (mode === 'fixed_percentage' || mode === 'fixed_price') {
                        [manualT2, manualSL].forEach(m => { if(m) { m.disabled = true; m.value = ''; m.placeholder = 'Auto Calculated'; }});
                    } else {
                        if (manualT2) { manualT2.disabled = false; manualT2.placeholder = 'T2 gap (% or ₹)'; }
                        if (manualSL) { manualSL.disabled = false; manualSL.placeholder = 'SL gap (% or ₹)'; }
                    }
                    hasManualInput() ? applyManual() : calculateFromRiskMaster();
                }

                [manualT1, manualT2, manualSL].forEach(input => { if (input) input.addEventListener('input', smartRecalculate); });
                entry.addEventListener('input', smartRecalculate);
                modeSelect.addEventListener('change', smartRecalculate);
                document.querySelectorAll('[data-single="trade"]').forEach(btn => {
                    btn.addEventListener('click', () => setTimeout(smartRecalculate, 50));
                });
            })();

            // --- 4. NEW: AJAX FORM SUBMISSION (STAY ON PAGE) ---
         // --- 4. AJAX FORM SUBMISSION ---
const form = document.getElementById('equityTipForm');
const errorContainer = document.getElementById('ajax-error-container');
const errorList = document.getElementById('ajax-error-list');

if (form) {
    form.addEventListener('submit', async function(e) {
    e.preventDefault(); 

    const toast = document.getElementById('ajax-toast');
    const toastIcon = document.getElementById('toast-icon');
    const toastTitle = document.getElementById('toast-title');
    const toastContent = document.getElementById('toast-content');

    // Reset UI
    toast.classList.add('hidden');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Publishing...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();

        // Prepare Toast UI
        toast.classList.remove('hidden', 'bg-red-600', 'bg-emerald-500', 'text-white');
        toast.classList.add('text-white');

        if (response.ok) {
            // ✅ SUCCESS POPUP
            toast.classList.add('bg-emerald-500');
            toastIcon.className = 'fa-solid fa-circle-check mt-1';
            toastTitle.innerText = 'Success';
            toastContent.innerText = result.message || 'Tip Published!';
            
            form.reset();
            // Clear manual UI boxes
            document.querySelectorAll('.select-box').forEach(b => b.classList.remove('active-box', 'buy-active', 'sell-active'));
        } else {
            // ❌ ERROR POPUP
            toast.classList.add('bg-red-600');
            toastIcon.className = 'fa-solid fa-circle-exclamation mt-1';
            toastTitle.innerText = 'Error';
            
            if (result.errors) {
                let errHtml = '<ul class="list-disc pl-3">';
                Object.values(result.errors).flat().forEach(err => errHtml += `<li>${err}</li>`);
                errHtml += '</ul>';
                toastContent.innerHTML = errHtml;
            } else {
                toastContent.innerText = result.message || 'Validation failed.';
            }
        }
    } catch (error) {
        toast.classList.remove('hidden');
        toast.classList.add('bg-red-600', 'text-white');
        toastIcon.className = 'fa-solid fa-triangle-exclamation mt-1';
        toastTitle.innerText = 'System Error';
        toastContent.innerText = 'Check internet connection or server logs.';
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        // Auto-hide after 6 seconds
        setTimeout(() => toast.classList.add('hidden'), 6000);
    }
});
}
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
