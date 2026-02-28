<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-300 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">Menu Privileges</h1>
                    <p class="text-green-600 text-sm">Manage menu levels</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @elseif(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @elseif(session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-yellow-700 font-medium">{{ session('warning') }}</p>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="mb-6 flex flex-wrap gap-3">
            <a href="/menu-create" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create New Menu
            </a>
            <button onclick="showViewRolesModal()" class="inline-flex items-center px-6 py-3 bg-white border-2 border-green-200 hover:border-green-300 text-green-800 hover:text-green-900 font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                View Roles
            </button>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Menu Privilege List</h3>
            </div>
            <div class="overflow-x-auto">
                <table id="common_dataTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Menu Item</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role Level</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data will be populated by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- View Roles Modal Script -->
    <script>
        function showViewRolesModal() {
            const rolesHtml = `<div class="overflow-x-auto pb-4" style="max-height: 60vh;">
                <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-7 lg:grid-cols-8 xl:grid-cols-10 gap-4" style="min-width: max-content;">
                    @foreach ($roles as $role)
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200 hover:shadow-lg hover:scale-105 transition-all duration-200" style="min-width: 120px; max-width: 140px;">
                            <div class="text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-600 text-white font-bold text-lg shadow-md mb-3">
                                    {{ $role->Level }}
                                </span>
                                <h6 class="text-xs font-semibold text-gray-800 leading-tight break-words">{{ $role->Role }}</h6>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>`;

            const modal = document.getElementById('swiftModal');
            const iconDiv = document.getElementById('swiftModalIcon');
            const titleEl = document.getElementById('swiftModalTitle');
            const messageEl = document.getElementById('swiftModalMessage');
            const actionsEl = document.getElementById('swiftModalActions');

            if (!modal) return;

            iconDiv.innerHTML = `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>`;
            iconDiv.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-blue-100';

            titleEl.textContent = 'View Roles';
            messageEl.innerHTML = rolesHtml;

            actionsEl.innerHTML = '';
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'w-full py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition duration-200';
            closeBtn.textContent = 'Close';
            closeBtn.addEventListener('click', () => {
                hideSwiftModal();
            });
            actionsEl.appendChild(closeBtn);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    </script>
@endsection