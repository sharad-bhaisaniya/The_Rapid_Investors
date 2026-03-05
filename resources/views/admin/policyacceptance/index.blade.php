@extends('layouts.app')

@section('content')

@php
    $policy = \App\Models\PolicyAcceptance::first();
@endphp

<div class="min-h-screen bg-gray-100 py-10 px-6">

    <div class="max-w-7xl mx-auto">

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                Policy Acceptance Management
            </h1>
            <p class="text-[#0591b2] mt-1">
                Manage your application policy content and visibility.
            </p>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8">

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- MAIN STORE FORM -->
            <form action="{{ route('admin.policyacceptance.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Policy Title
                        </label>
                        <input type="text"
                               name="title"
                               value="{{ old('title', $policy->title ?? '') }}"
                               class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                               required>
                    </div>

                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border">
                        <span class="text-sm font-semibold text-gray-700">
                            Status
                        </span>

                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="status"
                                   value="1"
                                   class="sr-only peer"
                                   {{ isset($policy) && $policy->status ? 'checked' : '' }}>
                            <div class="w-14 h-7 bg-gray-300 rounded-full peer peer-checked:bg-green-500 relative transition-all">
                                <div class="absolute top-1 left-1 bg-white w-5 h-5 rounded-full transition-all peer-checked:translate-x-7"></div>
                            </div>
                        </label>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Short Description
                        </label>
                        <textarea name="description"
                                  rows="3"
                                  class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description', $policy->description ?? '') }}</textarea>
                    </div>

                    <!-- Content -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Full Policy Content
                        </label>
                        <textarea name="content"
                                  id="editor"
                                  rows="8"
                                  class="w-full border border-gray-300 rounded-xl p-3"
                                  required>{{ old('content', $policy->content ?? '') }}</textarea>
                    </div>

                </div>

                <!-- Save Button -->
                <div class="mt-8 flex justify-end">
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition shadow">
                        {{ $policy ? 'Update Policy' : 'Create Policy' }}
                    </button>
                </div>

            </form>

            <!-- CLEAR FORM (SEPARATE - NO NESTING) -->
            @if($policy)
                <div class="mt-4 flex justify-end">
                    <form action="{{ route('admin.policyacceptance.delete', $policy->id) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to clear the policy?');">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition">
                            Clear Policy
                        </button>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('editor');
</script>

@endsection