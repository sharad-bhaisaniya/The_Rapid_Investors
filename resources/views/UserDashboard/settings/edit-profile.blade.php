@extends('layouts.userdashboard')

@section('content')
    <div x-data="profileHandler()" class="bg-[#f8fafc] min-h-screen">

        {{-- Main Card Container --}}
        <div class="max-w-7xl mx-auto bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 md:p-8 relative">

            {{-- Success Message --}}
            @if (session('success'))
                <div
                    class="mb-6 p-4 bg-green-50 border border-green-100 text-green-700 rounded-xl flex items-center gap-2 text-sm font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Header Section --}}
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <a href="{{ url('settings') }}"
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-[#0939a4] transition-all border border-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl font-black text-[#0939a4]">Edit Profile</h1>
                            <p class="text-xs text-gray-400 font-medium">Update your personal details</p>
                        </div>
                    </div>

                    <button type="submit"
                        class="bg-[#0939a4] hover:bg-blue-800 text-white text-xs font-bold px-6 py-3 rounded-xl shadow-lg shadow-blue-100 transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd" />
                        </svg>
                        Save Changes
                    </button>
                </div>

                {{-- Image Upload Section --}}
                <div class="flex items-center gap-6 mb-10">
                    <div class="relative group">
                        <div class="w-24 h-24 rounded-full p-1 border-2 border-dashed border-[#0939a4]/30">
                            <img :src="imageUrl" class="w-full h-full rounded-full object-cover">
                        </div>
                        <label
                            class="absolute bottom-0 right-0 bg-[#0939a4] text-white w-8 h-8 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-800 transition shadow-md border-2 border-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path
                                    d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
                                <path
                                    d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                            </svg>
                            <input type="file" name="profile_image" class="hidden" @change="fileChosen">
                        </label>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800">Profile Photo</p>
                        <p class="text-[10px] text-gray-400 mt-1">Accepts JPG, PNG or GIF.</p>
                    </div>
                </div>

                {{-- Form Fields Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    {{-- Full Name --}}
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">Full Name</label>
                        <input type="text" name="name" value="{{ $user->name }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-bold text-gray-700 outline-none focus:border-[#0939a4] focus:ring-1 focus:ring-[#0939a4] transition">
                    </div>

                    <div class="space-y-1">
                            <label class="text-[10px] text-gray-400 font-bold uppercase">Father Name</label>
                            <input type="text"
                                value="{{ $user->father_name ?? '' }}"
                                disabled
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200
                                        rounded-xl text-sm font-bold text-gray-600 cursor-not-allowed">
                        </div>

                    {{-- Date of Birth (Restored) --}}
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">Date of Birth</label>
                        <input type="date" name="dob"
                            value="{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('Y-m-d') : '' }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-bold text-gray-700 outline-none focus:border-[#0939a4] focus:ring-1 focus:ring-[#0939a4] transition">
                    </div>
                    {{-- Gender --}}
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">
                            Gender 
                        </label>

                        <!-- Visible but disabled -->
                        <select disabled
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-bold
                                text-gray-600 bg-gray-50 cursor-not-allowed">
                            <option>
                                {{ ucfirst($user->gender ?? 'N/A') }}
                            </option>
                        </select>

                        <!-- Hidden input to preserve value (no duplicate / no overwrite) -->
                        <input type="hidden" name="gender" value="{{ $user->gender }}">
                    </div>
                    {{-- Email (Dynamic) --}}
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">Email (Verified)</label>
                        <div class="flex gap-2 relative">
                            <input type="email" value="{{ $user->email }}" disabled
                                class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-500 cursor-not-allowed">
                            <button type="button" @click="initUpdateModal('email')"
                                class="absolute right-2 top-2 bottom-2 px-3 bg-blue-50 text-[#0939a4] hover:bg-[#0939a4] hover:text-white rounded-lg font-bold text-[10px] transition-colors border border-blue-100">
                                Change
                            </button>
                        </div>
                    </div>

                    {{-- Phone Number (Dynamic) --}}
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">Phone Number</label>
                        <div class="flex gap-2 relative">
                            <input type="text" value="{{ $user->phone }}" disabled
                                class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-500 cursor-not-allowed">
                            <button type="button" @click="initUpdateModal('phone')"
                                class="absolute right-2 top-2 bottom-2 px-3 bg-blue-50 text-[#0939a4] hover:bg-[#0939a4] hover:text-white rounded-lg font-bold text-[10px] transition-colors border border-blue-100">
                                Change
                            </button>
                        </div>
                    </div>

                    {{-- Password (Dynamic) --}}
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">Password</label>
                        <div class="flex gap-2 relative">
                            <input type="password" value="********" disabled
                                class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-500 cursor-not-allowed">
                            <button type="button" @click="initPasswordModal()"
                                class="absolute right-2 top-2 bottom-2 px-3 bg-blue-50 text-[#0939a4] hover:bg-[#0939a4] hover:text-white rounded-lg font-bold text-[10px] transition-colors border border-blue-100">
                                Update
                            </button>
                        </div>
                    </div>

                    {{-- City (Restored) --}}
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">City</label>
                        <input type="text" name="city" value="{{ $user->city }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-bold text-gray-700 outline-none focus:border-[#0939a4] focus:ring-1 focus:ring-[#0939a4] transition">
                    </div>

                    {{-- State (Restored) --}}
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">State</label>
                        <input type="text" name="state" value="{{ $user->state }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-bold text-gray-700 outline-none focus:border-[#0939a4] focus:ring-1 focus:ring-[#0939a4] transition">
                    </div>

                    {{-- Pincode (Restored) --}}
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">Pincode</label>
                        <input type="text" name="pincode" value="{{ $user->pincode }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-bold text-gray-700 outline-none focus:border-[#0939a4] focus:ring-1 focus:ring-[#0939a4] transition">
                    </div>

                    {{-- Full Address (Restored) --}}
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">Full Address</label>
                        <textarea name="address" rows="3"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-bold text-gray-700 outline-none focus:border-[#0939a4] focus:ring-1 focus:ring-[#0939a4] transition resize-none">{{ $user->address }}</textarea>
                    </div>
                </div>
            </form>
        </div>

        {{-- SINGLE DYNAMIC MODAL --}}
        <div x-show="modal.open" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity">
            <div @click.away="modal.open = false"
                class="bg-white p-6 md:p-8 rounded-[24px] w-full max-w-md shadow-2xl relative">

                <button @click="modal.open = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <h2 class="text-xl font-black text-center mb-1 text-[#0939a4]" x-text="modal.title"></h2>
                <p class="text-xs text-center text-gray-400 mb-6" x-text="modal.subtitle"></p>

                <div x-show="errorMessage"
                    class="mb-4 p-3 bg-red-50 border border-red-100 text-red-600 text-xs font-bold rounded-xl text-center"
                    x-text="errorMessage"></div>

                {{-- STEP 1: Input (Email or Phone) --}}
                <div x-show="modal.step === 1 && modal.type !== 'password'" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold uppercase"
                            x-text="modal.type === 'email' ? 'New Email' : 'New Phone'"></label>
                        <input type="text" x-model="modal.targetValue"
                            :placeholder="modal.type === 'email' ? 'example@mail.com' : 'Enter 10 digit number'"
                            class="w-full px-4 py-3 border border-blue-100 rounded-xl bg-[#0015ff0f] outline-none text-sm font-bold">
                    </div>
                    <button @click="sendOtpRequest()" :disabled="loading"
                        class="w-full py-3 bg-[#0939a4] text-white rounded-xl font-bold text-sm shadow-lg">
                        <span x-show="!loading">Send OTP</span>
                        <span x-show="loading">Sending...</span>
                    </button>
                </div>

                {{-- STEP 1: Password (Verification Step) --}}
                <div x-show="modal.step === 1 && modal.type === 'password'" class="space-y-4">
                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 text-center">
                        <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest mb-1">OTP will be sent to
                        </p>
                        <p class="text-sm font-black text-[#0939a4]">
                            {{ substr($user->phone, 0, 2) . '******' . substr($user->phone, -2) }}
                        </p>
                    </div>
                    <button @click="sendOtpRequest()" :disabled="loading"
                        class="w-full py-3 bg-[#0939a4] text-white rounded-xl font-bold text-sm shadow-lg">
                        <span x-show="!loading">Send OTP to Verify</span>
                        <span x-show="loading">Sending...</span>
                    </button>
                </div>

                {{-- STEP 2: Verify OTP --}}
                <div x-show="modal.step === 2" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 font-bold uppercase text-center block">Enter 6-Digit
                            OTP</label>
                        <input type="text" x-model="modal.otpCode" maxlength="6"
                            class="w-full text-center text-3xl tracking-[0.4em] font-black py-3 border border-blue-100 rounded-xl bg-[#0015ff0f] outline-none">
                    </div>
                    <button @click="verifyOtpRequest()" :disabled="loading"
                        class="w-full py-3 bg-green-600 text-white rounded-xl font-bold text-sm shadow-lg">
                        <span x-show="!loading"
                            x-text="modal.type === 'password' ? 'Verify OTP' : 'Verify & Update'"></span>
                        <span x-show="loading">Verifying...</span>
                    </button>
                </div>

                {{-- STEP 3: New Password --}}
                <div x-show="modal.step === 3 && modal.type === 'password'" class="space-y-4">
                    <div class="space-y-3">
                        <input type="password" x-model="modal.newPassword" placeholder="New Password"
                            class="w-full px-4 py-3 border border-blue-100 rounded-xl bg-[#0015ff0f] outline-none text-sm font-bold">
                        <input type="password" x-model="modal.confirmPassword" placeholder="Confirm Password"
                            class="w-full px-4 py-3 border border-blue-100 rounded-xl bg-[#0015ff0f] outline-none text-sm font-bold">
                    </div>
                    <button @click="updatePasswordFinal()" :disabled="loading"
                        class="w-full py-3 bg-[#0939a4] text-white rounded-xl font-bold text-sm shadow-lg">
                        <span x-show="!loading">Update Password Now</span>
                        <span x-show="loading">Updating...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script>
        function profileHandler() {
            return {
                imageUrl: '{{ $user->getFirstMediaUrl('profile_image') ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}',
                loading: false,
                errorMessage: '',
                // Aapka Base URL
                apiBase: 'http://127.0.0.1:8000/api/profile',
                // apiBase: 'https://bharatstockmarketresearch.com/api/profile', //for live

                modal: {
                    open: false,
                    type: '',
                    step: 1,
                    title: '',
                    subtitle: '',
                    targetValue: '',
                    otpCode: '',
                    newPassword: '',
                    confirmPassword: ''
                },

                initUpdateModal(type) {
                    this.errorMessage = '';
                    this.modal = {
                        open: true,
                        type: type,
                        step: 1,
                        title: type === 'email' ? 'Change Email' : 'Update Phone',
                        subtitle: 'Enter your new contact details to receive OTP',
                        targetValue: '',
                        otpCode: ''
                    };
                },

                initPasswordModal() {
                    this.errorMessage = '';
                    this.modal = {
                        open: true,
                        type: 'password',
                        step: 1,
                        title: 'Reset Password',
                        subtitle: 'Verify your identity via registered mobile',
                        otpCode: '',
                        newPassword: '',
                        confirmPassword: ''
                    };
                },

                // async sendOtpRequest() {
                //     this.loading = true;
                //     this.errorMessage = '';

                //     // Password ke liye alag endpoint, Email/Phone ke liye alag
                //     let url = this.modal.type === 'password' ?
                //         `${this.apiBase}/password/send-otp` :
                //         `${this.apiBase}/otp/send`;

                //     try {
                //         let res = await fetch(url, {
                //             method: 'POST',
                //             headers: {
                //                 'Content-Type': 'application/json',
                //                 'Accept': 'application/json',
                //                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                //             },
                //             body: JSON.stringify({
                //                 type: this.modal.type,
                //                 value: this.modal.targetValue
                //             })
                //         });
                //         let data = await res.json();
                //         if (data.success) {
                //             this.modal.step = 2;
                //             this.modal.subtitle = "OTP sent successfully!";
                //         } else {
                //             this.errorMessage = data.message;
                //         }
                //     } catch (e) {
                //         this.errorMessage = "Connection error.";
                //     }
                //     this.loading = false;
                // },
                async sendOtpRequest() {
    this.loading = true;
    this.errorMessage = '';

    let url = this.modal.type === 'password'
        ? `${this.apiBase}/password/send-otp`
        : `${this.apiBase}/otp/send`;

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content');

        let res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                type: this.modal.type,
                value: this.modal.targetValue
            })
        });

        let data = await res.json();

        if (data.success) {
            this.modal.step = 2;
            this.modal.subtitle = "OTP sent successfully!";
        } else {
            this.errorMessage = data.message;
        }

    } catch (e) {
        this.errorMessage = "Connection error.";
    }

    this.loading = false;
},


                async verifyOtpRequest() {
                    this.loading = true;
                    this.errorMessage = '';

                    // Password ke liye alag endpoint, Email/Phone ke liye alag
                    let url = this.modal.type === 'password' ?
                        `${this.apiBase}/password/verify-otp` :
                        `${this.apiBase}/otp/verify`;

                    try {
                        let res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                type: this.modal.type,
                                otp: this.modal.otpCode,
                                value: this.modal.targetValue
                            })
                        });
                        let data = await res.json();
                        if (data.success) {
                            if (this.modal.type === 'password') {
                                this.modal.step = 3; // Password ke liye next step (New Password input)
                            } else {
                                window.location.reload(); // Email/Phone direct update ho gaye
                            }
                        } else {
                            this.errorMessage = data.message;
                        }
                    } catch (e) {
                        this.errorMessage = "Verification failed.";
                    }
                    this.loading = false;
                },

                // PASSWORD UPDATE FINAL STEP
                async updatePasswordFinal() {
                    if (this.modal.newPassword !== this.modal.confirmPassword) {
                        this.errorMessage = "Passwords do not match!";
                        return;
                    }
                    this.loading = true;
                    this.errorMessage = '';

                    try {
                        // Sahi URL: http://127.0.0.1:8000/api/profile/password/update
                        let res = await fetch(`${this.apiBase}/password/update`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                password: this.modal.newPassword,
                                password_confirmation: this.modal.confirmPassword,
                                otp: this.modal.otpCode // Security ke liye OTP bhi bhej rahe hain
                            })
                        });

                        let data = await res.json();
                        if (data.success) {
                            alert('Password updated successfully!');
                            window.location.reload();
                        } else {
                            this.errorMessage = data.message;
                        }
                    } catch (e) {
                        this.errorMessage = "Update failed.";
                    }
                    this.loading = false;
                },

                fileChosen(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (e) => this.imageUrl = e.target.result;
                    reader.readAsDataURL(file);
                }
            }
        }
    </script>
@endsection
