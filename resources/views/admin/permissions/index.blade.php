@extends('layouts.app')

@section('title', 'Permissions')

@section('header')
    <header class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">Permissions</h2>
                <p class="text-xs text-gray-500">Manage system access levels by category</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Total</span>
                <p class="text-lg font-bold text-blue-600 leading-none">{{ $permissions->count() }}</p>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="py-6 bg-[#f8fafc] min-h-screen" x-data="{ openSetup: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg mb-6 p-3 flex flex-wrap items-center justify-between gap-4">
                <form method="POST" action="{{ route('permissions.store') }}" class="flex items-center gap-2 flex-grow max-w-md">
                    @csrf
                    <div class="relative flex-grow">
                        <input name="name" type="text" placeholder="Single permission (e.g. view news)"
                            class="block w-full pl-3 pr-3 py-1.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all"
                            required />
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-blue-800 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-wider hover:bg-black transition-all">
                        Quick Add
                    </button>
                </form>

                <button @click="openSetup = true" class="inline-flex items-center px-4 py-1.5 bg-blue-600 text-white rounded-md font-bold text-xs uppercase tracking-wider hover:bg-blue-700 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Full Module Setup
                </button>
            </div>

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-700 rounded-lg text-xs font-bold uppercase tracking-tight">
                    {{ session('success') }}
                </div>
            @endif

            <div x-show="openSetup" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
                
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="openSetup = false"></div>

                    <div class="bg-white rounded-xl shadow-2xl transform transition-all sm:max-w-lg sm:w-full z-50 overflow-hidden border border-gray-100">
                        <form method="POST" action="{{ route('permissions.bulk_store') }}">
                            @csrf
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-gray-900">Module Auto-Setup</h3>
                                    <button type="button" @click="openSetup = false" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                
                                <p class="text-xs text-gray-500 mb-6">Enter the resource name (e.g., <span class="text-blue-600 font-bold italic">Customers</span>). We will automatically create the View, Create, Edit, and Delete permissions for it.</p>
                                
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Module / Resource Name</label>
                                    <input name="base_name" type="text" placeholder="e.g. Products"
                                        class="block w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" 
                                        required autofocus />
                                </div>

                                <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-100">
                                    <h4 class="text-[10px] font-bold text-blue-600 uppercase mb-2">The system will generate:</h4>
                                    <ul class="text-xs text-blue-800 space-y-1 opacity-80">
                                        <li class="flex items-center"><svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg> view [name]</li>
                                        <li class="flex items-center"><svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg> create [name]</li>
                                        <li class="flex items-center"><svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg> edit [name]</li>
                                        <li class="flex items-center"><svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg> delete [name]</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-2">
                                <button type="submit" class="inline-flex justify-center rounded-lg px-6 py-2 bg-blue-600 text-xs font-bold text-white uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
                                    Generate Permissions
                                </button>
                                <button type="button" @click="openSetup = false" class="inline-flex justify-center rounded-lg px-6 py-2 bg-white border border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-widest hover:bg-gray-50 transition-all">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @php
                $groupedPermissions = $permissions->groupBy(function($item) {
                    $parts = explode(' ', trim($item->name));
                    return count($parts) > 1 ? ucfirst(end($parts)) : 'General';
                });
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse ($groupedPermissions as $category => $items)
                    <div class="bg-white shadow-sm border border-gray-200 rounded-lg flex flex-col h-fit overflow-hidden">
                        <div class="px-3 py-2 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-bold text-gray-700 tracking-tight text-[11px] uppercase">{{ $category }}</h3>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-100">
                                {{ $items->count() }}
                            </span>
                        </div>

                        <div class="p-1">
                            @foreach ($items as $permission)
                                <div x-data="{ isEditing: false }" class="group flex items-center justify-between p-2 rounded-md hover:bg-blue-50/30 transition-all">
                                    
                                    <div class="flex-grow min-w-0">
                                        <template x-if="!isEditing">
                                            <span class="text-xs text-gray-600 truncate block capitalize leading-relaxed">{{ $permission->name }}</span>
                                        </template>
                                        
                                        <template x-if="isEditing">
                                            <form method="POST" action="{{ route('permissions.update', $permission->id) }}" class="flex items-center gap-1">
                                                @csrf @method('PUT')
                                                <input type="text" name="name" value="{{ $permission->name }}" 
                                                    class="text-[11px] p-0.5 border border-blue-400 rounded outline-none w-full focus:ring-1 focus:ring-blue-500" required>
                                                <button type="submit" class="text-green-500 hover:text-green-700">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                                <button type="button" @click="isEditing = false" class="text-gray-400 hover:text-gray-600">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        </template>
                                    </div>

                                    <div class="flex items-center opacity-0 group-hover:opacity-100 transition-opacity ml-2">
                                        <button @click="isEditing = true" class="p-1 text-gray-300 hover:text-blue-500 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('permissions.destroy', $permission->id) }}" onsubmit="return confirm('Permanently delete this permission?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1 text-gray-300 hover:text-red-500 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center bg-white rounded-xl border border-dashed border-gray-200">
                        <svg class="mx-auto h-12 w-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest">No Permissions Found</h3>
                        <p class="text-xs text-gray-400 mt-1">Start by using the Quick Add or Full Setup tools above.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
@endsection