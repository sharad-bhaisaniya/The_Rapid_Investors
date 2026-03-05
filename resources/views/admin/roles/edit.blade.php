@extends('layouts.app')

@section('title', 'Access Control: ' . $role->name)

@section('header')
    <header class="bg-white border-b border-gray-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <h2 class="font-black text-xl text-gray-900 leading-none tracking-tight">Access Control</h2>
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mt-1">Configuring Role: <span class="text-blue-600">{{ $role->name }}</span></p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('roles.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-800 transition-colors">Cancel</a>
                
                <button type="submit" form="role-form" class="bg-blue-600 px-6 py-2 rounded-lg text-white text-xs font-bold uppercase tracking-widest hover:bg-blue-700 shadow-md shadow-blue-100 transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="py-8 bg-[#f9fafb] min-h-screen" x-data="{ 
        search: '', 
        filterMode: 'all',
        
        matches(el) {
            const text = el.innerText.toLowerCase();
            const searchMatch = this.search === '' || text.includes(this.search.toLowerCase());
            const checkbox = el.querySelector('input');
            const isChecked = checkbox ? checkbox.checked : false;
            
            if (this.filterMode === 'selected') return searchMatch && isChecked;
            if (this.filterMode === 'unselected') return searchMatch && !isChecked;
            return searchMatch;
        },

        shouldShowModule(categoryName, el) {
            const searchLower = this.search.toLowerCase();
            const catMatch = categoryName.toLowerCase().includes(searchLower);
            if (catMatch && this.filterMode === 'all') return true;
            const labels = Array.from(el.querySelectorAll('.perm-label'));
            return labels.some(label => this.matches(label));
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <form id="role-form" method="POST" action="{{ route('roles.update', $role->id) }}">
                @csrf @method('PUT')

                <div class="mb-8 grid grid-cols-1 md:grid-cols-12 gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm items-center">
                    <div class="md:col-span-4 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input x-model="search" type="text" placeholder="Search modules or permissions..." 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>

                    <div class="md:col-span-4 flex items-center justify-center space-x-1 bg-gray-50 p-1 rounded-lg border border-gray-100">
                        <button type="button" @click="filterMode = 'all'" :class="filterMode === 'all' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500'" class="flex-1 py-1 rounded-md text-[10px] font-bold uppercase transition-all">All</button>
                        <button type="button" @click="filterMode = 'selected'" :class="filterMode === 'selected' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500'" class="flex-1 py-1 rounded-md text-[10px] font-bold uppercase transition-all">Selected</button>
                        <button type="button" @click="filterMode = 'unselected'" :class="filterMode === 'unselected' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500'" class="flex-1 py-1 rounded-md text-[10px] font-bold uppercase transition-all">Unselected</button>
                    </div>

                    <div class="md:col-span-4 flex items-center justify-end space-x-6 border-l pl-4 border-gray-100">
                        <div class="text-right">
                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Role Name</label>
                            <input type="text" name="name" value="{{ old('name', $role->name) }}" class="text-sm font-bold text-gray-800 p-0 border-none focus:ring-0 text-right w-32 bg-transparent focus:border-b focus:border-blue-200">
                        </div>
                        <div class="flex flex-col gap-1">
                            <button type="button" onclick="toggleAllVisible(true)" class="text-[9px] font-bold text-blue-600 hover:text-blue-800 uppercase tracking-tighter">Select All</button>
                            <button type="button" onclick="toggleAllVisible(false)" class="text-[9px] font-bold text-red-500 hover:text-red-700 uppercase tracking-tighter">Clear All</button>
                        </div>
                    </div>
                </div>
                <div class="mt-8 mb-3 flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 px-6 py-3 rounded-xl text-white text-xs font-bold uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all active:scale-95">
                        Update Role Permissions
                    </button>
                </div>

                @php
                    $groupedPermissions = $permissions->groupBy(function($item) {
                        $parts = explode(' ', trim($item->name));
                        return count($parts) > 1 ? ucfirst(end($parts)) : 'System';
                    });
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($groupedPermissions as $category => $items)
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col transition-all duration-300"
                             x-show="shouldShowModule('{{ $category }}', $el)"
                             x-cloak
                             x-data="{ 
                                get checkedCount() {
                                    return Array.from($el.querySelectorAll('.perm-check')).filter(c => c.checked).length;
                                },
                                toggleModule() {
                                    const labels = Array.from($el.querySelectorAll('.perm-label'));
                                    const visibleCheckboxes = labels.filter(l => l.style.display !== 'none').map(l => l.querySelector('input'));
                                    const anyUnchecked = visibleCheckboxes.some(c => !c.checked);
                                    visibleCheckboxes.forEach(c => c.checked = anyUnchecked);
                                }
                             }">
                            
                            <div class="p-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/20">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 bg-blue-50 rounded-lg flex items-center justify-center border border-blue-100 shadow-sm">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-xs text-gray-800 uppercase tracking-tight">{{ $category }}</h4>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter"><span x-text="checkedCount"></span> / {{ $items->count() }} Active</p>
                                    </div>
                                </div>
                                <button type="button" @click="toggleModule()" class="text-[9px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 uppercase transition-colors">Toggle</button>
                            </div>

                            <div class="p-3 grid grid-cols-2 gap-2">
                                @foreach ($items as $permission)
                                    <label x-show="matches($el)" 
                                        class="perm-label group relative flex items-center p-2 rounded-xl border border-gray-50 bg-white cursor-pointer transition-all hover:border-blue-200 hover:shadow-sm">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                            class="perm-check h-3.5 w-3.5 text-blue-600 rounded border-gray-300 focus:ring-0 transition-all mr-2"
                                            {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                        
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-tight group-hover:text-blue-600 truncate">
                                            {{ str_replace(strtolower($category), '', strtolower($permission->name)) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- ✅ UPDATE BUTTON ADDED -->
                <!-- <div class="mt-8 flex justify-center">
                    <button type="submit"
                        class="bg-blue-600 px-10 py-3 rounded-xl text-white text-sm font-bold uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all active:scale-95">
                        Update Role Permissions
                    </button>
                </div> -->

            </form>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        label:has(input:checked) {
            background-color: #eff6ff !important;
            border-color: #bfdbfe !important;
        }
        label:has(input:checked) span {
            color: #2563eb !important;
        }
    </style>

    <script>
        function toggleAllVisible(checked) {
            document.querySelectorAll('.perm-label').forEach(label => {
                if (label.style.display !== 'none') {
                    const cb = label.querySelector('.perm-check');
                    if(cb) cb.checked = checked;
                }
            });
        }
    </script>
@endsection
