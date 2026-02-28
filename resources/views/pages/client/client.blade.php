<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/client.css') }}?v={{ time() }}">
    <style>
        /* Critical Modal Styles - Inline to ensure they load */
        .modal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 10000 !important;
            width: 100% !important;
            height: 100% !important;
        }
        .modal.show {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .modal-backdrop {
            display: none !important;
        }
        .modal-dialog {
            z-index: 10002 !important;
            position: relative !important;
        }
        .modal-content {
            z-index: 10003 !important;
            position: relative !important;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-300 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">Client Management</h1>
                    <p class="text-green-600 text-sm">Manage clients from different regions and branches</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
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

        <!-- Action Bar -->
        <div class="flex flex-col gap-4 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <a href="/client-create" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create New Client
                </a>
                
                <!-- Filter Tabs -->
                <div class="flex flex-wrap gap-2">
                    <a href="/client" class="status-filter-link px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') == null ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}" data-status="">
                        All Clients
                    </a>
                    <a href="/client?status=pending" class="status-filter-link px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') == 'pending' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}" data-status="pending">
                        Pending
                    </a>
                    <a href="/client?status=verified" class="status-filter-link px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') == 'verified' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}" data-status="verified">
                        Verified
                    </a>
                    <a href="/client?status=approved" class="status-filter-link px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') == 'approved' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}" data-status="approved">
                        Approved
                    </a>
                    <a href="/client?status=active" class="status-filter-link px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') == 'active' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}" data-status="active">
                        Active
                    </a>
                    <a href="/client?status=lapse" class="status-filter-link px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') == 'lapse' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}" data-status="lapse">
                        Lapse
                    </a>
                </div>
            </div>
            
            <!-- Branch Filter -->
            <div class="flex items-center justify-end gap-3">
                <label for="branchFilter" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Filter by Branch:</label>
                <select id="branchFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 bg-white shadow-sm min-w-[200px]">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->BranchName }}">{{ $branch->BranchName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Client List</h3>
            </div>
            <div class="overflow-x-auto">
                <table id="common_dataTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Name</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">First Name</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Middle Name</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contract No.</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Region</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Branch</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Package</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Term</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data will be populated by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Hidden Delete Form -->
        <form id="deleteForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>
    <script src="{{ asset('js/client.js') }}"></script>
@endsection