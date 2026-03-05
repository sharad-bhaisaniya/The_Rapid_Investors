<div x-show="showEmailModal"
     x-transition
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-md rounded-2xl p-6 space-y-4"
         @click.away="showEmailModal = false">

        <h3 class="text-lg font-bold text-center">Verify Email</h3>

        <!-- Email -->
        <div>
            <label class="text-xs font-semibold text-gray-600">Email Address</label>
            <input type="email"
                   x-model="email"
                   class="w-full mt-1 px-4 py-2 border rounded-xl text-sm
                          focus:outline-none focus:ring-2 focus:ring-[#0939a4]">
        </div>

        <!-- OTP -->
        <div x-show="otpSent">
            <label class="text-xs font-semibold text-gray-600">Enter OTP</label>
            <input type="text"
                   x-model="otp"
                   maxlength="6"
                   class="w-full mt-1 px-4 py-2 border rounded-xl
                          text-center tracking-widest
                          focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Error Message -->
        <p x-show="error"
           class="text-xs text-red-500 text-center"
           x-text="error"></p>

      
        <!-- Actions -->
        <div class="flex gap-3">
            <!-- SEND OTP -->
            <button x-show="!otpSent"
                    @click="sendEmailOtp()"
                    :disabled="sendingOtp"
                    class="flex-1 bg-[#0939a4] text-white py-2 rounded-xl
                           text-xs font-bold disabled:opacity-60 transition">
                <span x-show="!sendingOtp">Send OTP</span>
                <span x-show="sendingOtp">Sending...</span>
            </button>

            <!-- VERIFY OTP -->
            <button x-show="otpSent"
                    @click="verifyEmailOtp()"
                    :disabled="verifyingOtp"
                    class="flex-1 bg-green-600 text-white py-2 rounded-xl
                           text-xs font-bold disabled:opacity-60 transition">
                <span x-show="!verifyingOtp">Verify & Continue</span>
                <span x-show="verifyingOtp">Verifying...</span>
            </button>
        </div>

    </div>
</div>