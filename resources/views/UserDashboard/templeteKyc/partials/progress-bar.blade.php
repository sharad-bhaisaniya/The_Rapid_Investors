<div class="sticky top-0 bg-white/95 backdrop-blur-sm z-40 border-b border-blue-100">

    <div class="w-full h-1 bg-blue-50">
        <div class="h-full bg-blue-700 transition-all duration-700 ease-in-out shadow-[0_0_10px_rgba(29,78,216,0.3)]"
            :style="`width: ${Math.min(step, 5) / 5 * 100}%`">
        </div>
    </div>

    <div class="max-w-4xl mx-auto flex justify-between items-center px-4 py-2.5">

        <div class="flex items-center gap-2">
            <div :class="step >= 1 ? 'bg-blue-700 border-blue-700 text-white' : 'bg-white border-blue-200 text-blue-300'"
                class="w-5 h-5 flex items-center justify-center rounded-full border text-[10px] font-bold transition-all shadow-sm">
                <span x-show="step <= 1">1</span>
                <span x-show="step > 1">✓</span>
            </div>
            <span class="hidden md:block text-[10px] font-bold tracking-wider uppercase transition-colors"
                :class="step >= 1 ? 'text-blue-900' : 'text-blue-300'">Overview</span>
        </div>

        <div class="flex-1 h-[1px] mx-3 bg-blue-50"></div>

        <div class="flex items-center gap-2">
            <div :class="step >= 2 ? 'bg-blue-700 border-blue-700 text-white' : 'bg-white border-blue-200 text-blue-300'"
                class="w-5 h-5 flex items-center justify-center rounded-full border text-[10px] font-bold transition-all shadow-sm">
                <span x-show="step <= 2">2</span>
                <span x-show="step > 2">✓</span>
            </div>
            <span class="hidden md:block text-[10px] font-bold tracking-wider uppercase transition-colors"
                :class="step >= 2 ? 'text-blue-900' : 'text-blue-300'">Invoice</span>
        </div>

        <div class="flex-1 h-[1px] mx-3 bg-blue-50"></div>

        <div class="flex items-center gap-2">
            <div :class="step >= 3 ? 'bg-blue-700 border-blue-700 text-white' : 'bg-white border-blue-200 text-blue-300'"
                class="w-5 h-5 flex items-center justify-center rounded-full border text-[10px] font-bold transition-all shadow-sm">
                <span x-show="step <= 3">3</span>
                <span x-show="step > 3">✓</span>
            </div>
            <span class="hidden md:block text-[10px] font-bold tracking-wider uppercase transition-colors"
                :class="step >= 3 ? 'text-blue-900' : 'text-blue-300'">Risk</span>
        </div>

        <div class="flex-1 h-[1px] mx-3 bg-blue-50"></div>

        <div class="flex items-center gap-2">
            <div :class="step >= 4 ? 'bg-blue-700 border-blue-700 text-white' : 'bg-white border-blue-200 text-blue-300'"
                class="w-5 h-5 flex items-center justify-center rounded-full border text-[10px] font-bold transition-all shadow-sm">
                <span x-show="step <= 4">4</span>
                <span x-show="step > 4">✓</span>
            </div>
            <span class="hidden md:block text-[10px] font-bold tracking-wider uppercase transition-colors"
                :class="step >= 4 ? 'text-blue-900' : 'text-blue-300'">Legal</span>
        </div>

        <div class="flex-1 h-[1px] mx-3 bg-blue-50"></div>

        <div class="flex items-center gap-2">
            <div :class="step >= 5 ? 'bg-blue-700 border-blue-700 text-white' : 'bg-white border-blue-200 text-blue-300'"
                class="w-5 h-5 flex items-center justify-center rounded-full border text-[10px] font-bold transition-all shadow-sm">
                5
            </div>
            <span class="hidden md:block text-[10px] font-bold tracking-wider uppercase transition-colors"
                :class="step >= 5 ? 'text-blue-900' : 'text-blue-300'">Sign</span>
        </div>

    </div>
</div>
