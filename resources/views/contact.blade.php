@extends('layouts.user')
@section('content')

    <!-- CONTACT HERO SECTION -->
    @if ($banner)
        <section class="w-full px-4 md:px-8 lg:px-16 mt-28 flex justify-center">
            <div
                class="max-w-[1600px] w-full bg-[#F5F7FB] rounded-[30px] 
            flex flex-col items-center text-center px-4 md:px-10 py-20 md:py-28 lg:py-32">

                <!-- Badge -->
                <span data-animate
                    class="fade-up delay-100 inline-block bg-[#0939a4] text-white px-6 py-2 rounded-full 
                text-sm md:text-base mb-6">
                    {{ $banner->title ?? 'Contact us' }}
                </span>

                <!-- Heading -->
                <h1 data-animate
                    class="fade-up delay-200 text-[22px] md:text-[30px] lg:text-[36px] font-semibold 
                text-[#0939a4] leading-snug max-w-2xl">

                    @php
                        $subtitle = trim($banner->subtitle ?? '');
                        $words = preg_split('/\s+/', $subtitle);
                        $firstLine = implode(' ', array_slice($words, 0, 4));
                        $secondLine = implode(' ', array_slice($words, 4));
                    @endphp

                    {{ $firstLine }}
                    @if ($secondLine)
                        <br>
                        {{ $secondLine }}
                    @endif
                </h1>

            </div>
        </section>
    @endif


    <!-- CONTACT FORM SECTION -->
    
    <section class="w-full px-4 md:px-8 lg:px-16 mt-12 flex justify-center" 
         style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        
        <div class="max-w-[1600px] w-full bg-white rounded-2xl border border-gray-200 p-6 md:p-10 grid md:grid-cols-2 gap-10">

            <div class="bg-[#F5F7FB] rounded-xl p-8 flex flex-col justify-between">
                <div>
                    <h2 class="text-[22px] font-bold text-[#0A0E23] mb-1 tracking-tight">
                        Contact Information
                    </h2>
                    <p class="text-[#64748B] text-[14px] font-light mb-8  leading-loose">
                        Say something to start a live chat!
                    </p>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-5 text-center"><i class="fa-solid fa-phone text-sm text-[#0A0E23]"></i></div>
                        <p class="text-[#1F2937] text-[14px] font-normal">
                            {{ $contactDetail->phone ?? '-' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-5 text-center"><i class="fa-solid fa-envelope text-sm text-[#0A0E23]"></i></div>
                        <p class="text-[#1F2937] text-[14px] font-normal">
                            {{ $contactDetail->email ?? '-' }}
                        </p>
                    </div>

                    <div class="flex items-start gap-4 mb-10">
                        <div class="w-5 text-center mt-1"><i class="fa-solid fa-location-dot text-sm text-[#0A0E23]"></i></div>
                        <p class="text-[#1F2937] text-[14px] font-normal leading-relaxed">
                            {!! nl2br(e($contactDetail->address ?? '-')) !!}
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl border border-blue-100 bg-white shadow-sm space-y-5">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-4">
                            <div>
                                <p class="text-[10px] text-blue-600 uppercase tracking-[0.1em] font-bold mb-1">
                                    Proprietor
                                </p>
                                <h4 class="text-[15px] font-bold text-[#0A0E23]">
                                    Namita Rathore
                                </h4>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold mb-1">
                                    BSE Enlistment
                                </p>
                                <p class="text-[13px] font-medium text-gray-800">
                                    6838
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-1">
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold mb-1">
                                    SEBI Reg. No.
                                </p>
                                <p class="text-[13px] font-mono font-medium text-blue-700">
                                    INH000023728
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold mb-1">
                                    Validity
                                </p>
                                <p class="text-[13px] font-medium text-gray-800">
                                    31 Oct 2025 – 2030
                                </p>
                            </div>
                        </div>

                        <p class="text-[9px] text-blue-500 uppercase tracking-[0.2em] text-center font-bold pt-2">
                            Registered Research Analyst
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('inquiry.store') }}" method="POST" class="flex flex-col gap-8 py-4">
                @csrf

                <div class="grid grid-cols-2 gap-8">
                    <div class="group">
                        <label class="text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1 block">First Name</label>
                        <input type="text" name="first_name" required
                            class="w-full border-0 border-b border-gray-200 bg-transparent text-[14px] font-normal py-2 focus:border-[#0939a4] focus:ring-0 outline-none ps-0 transition">
                    </div>

                    <div class="group">
                        <label class="text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1 block">Last Name</label>
                        <input type="text" name="last_name"
                            class="w-full border-0 border-b border-gray-200 bg-transparent text-[14px] font-normal py-2 focus:border-[#0939a4] focus:ring-0 outline-none ps-0 transition">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8">
                    <div class="group">
                        <label class="text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1 block">Email</label>
                        <input type="email" name="email" required
                            class="w-full border-0 border-b border-gray-200 bg-transparent text-[14px] font-normal py-2 focus:border-[#0939a4] focus:ring-0 outline-none ps-0 transition">
                    </div>

                    <div class="group">
                        <label class="text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1 block">Phone Number</label>
                        <input type="text" name="phone"
                            class="w-full border-0 border-b border-gray-200 bg-transparent text-[14px] font-normal py-2 focus:border-[#0939a4] focus:ring-0 outline-none ps-0 transition">
                    </div>
                </div>

                <div>
                    <label class="text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-4 block">Select Subject</label>
                    <div class="flex flex-wrap gap-6 text-[13px] text-gray-700 font-medium">
                        @foreach (['General Inquiry', 'Support', 'Billing', 'Partnership'] as $subject)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="subject" value="{{ $subject }}"
                                    {{ $loop->first ? 'checked' : '' }}
                                    class="w-3.5 h-3.5 appearance-none border border-gray-300 rounded-full checked:bg-[#0939a4] checked:ring-2 checked:ring-offset-2 checked:ring-[#0939a4] transition-all">
                                <span class="group-hover:text-[#0939a4] transition-colors">{{ $subject }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1 block">Message</label>
                    <textarea name="message" rows="3" placeholder="Write your message..."
                        class="w-full border-0 border-b border-gray-200 bg-transparent text-[14px] font-normal py-2 focus:border-[#0939a4] focus:ring-0 outline-none ps-0 resize-none transition"></textarea>
                </div>

                <div class="flex items-center justify-between pt-6">
                    <div class="flex gap-3">
                        <a href="#" class="w-10 h-10 rounded-full border border-gray-100 flex items-center justify-center text-[#0A0E23] hover:bg-[#203182] hover:text-white transition-all">
                            <i class="fa-brands fa-twitter text-sm"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full border border-gray-100 flex items-center justify-center text-[#0A0E23] hover:bg-[#203182] hover:text-white transition-all">
                            <i class="fa-brands fa-instagram text-sm"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full border border-gray-100 flex items-center justify-center text-[#0A0E23] hover:bg-[#203182] hover:text-white transition-all">
                            <i class="fa-brands fa-facebook-f text-sm"></i>
                        </a>
                    </div>

                    <button class="bg-[#27388e] text-white px-10 py-3.5 rounded-lg text-[14px] font-bold shadow-lg hover:bg-blue-800 transform active:scale-95 transition-all">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
</section>





    <!-- FAQ SECTION -->
    @if (isset($faqs) && $faqs->count())
        <section class="w-full px-4 md:px-8 lg:px-16 mt-28 flex justify-center">
            <div class="max-w-[1500px] w-full grid md:grid-cols-2 gap-16">

                <!-- LEFT TITLE AREA -->
                <div class="space-y-8">
                    <span x-data="{ visible: false }" x-intersect.half="visible = true" :class="{ 'animated': visible }"
                        class="fade-up delay-100 inline-block bg-[#0939a4] text-white px-6 py-2 rounded-full text-sm md:text-base mb-4">
                        FAQ
                    </span>

                    <h2 x-data="{ visible: false }" x-intersect.half="visible = true" :class="{ 'animated': visible }"
                        class="fade-up delay-200 text-[28px] md:text-[34px] font-semibold text-[#0A0E23] leading-snug max-w-sm">
                        Common questions from our users
                    </h2>
                </div>

                <!-- RIGHT ACCORDION -->
                <div class="space-y-8">
                    @foreach ($faqs as $index => $faq)
                        <div x-data="{ visible: false }" x-intersect.half="visible = true" :class="{ 'animated': visible }"
                            class="fade-up delay-{{ 100 + $index * 50 }} border-b pb-4">

                            <button class="w-full flex justify-between items-center text-left faq-toggle py-2">
                                <span class="text-[16px] text-[#0A0E23] font-medium">
                                    {{ $faq->question }}
                                </span>
                                <span class="faq-arrow transition-transform duration-300 text-xl">▾</span>
                            </button>

                            <div class="faq-content overflow-hidden transition-all duration-300 ease-in-out max-h-0">
                                <p class="text-gray-600 pt-3 pb-2">
                                    {!! nl2br(e($faq->answer)) !!}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>
    @endif
@endsection
