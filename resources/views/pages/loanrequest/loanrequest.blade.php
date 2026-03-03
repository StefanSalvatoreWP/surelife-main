@extends('layouts.main')

@section('title', 'Loan Requests')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/client.css') }}?v={{ time() }}">
    <style>
        /* Page-specific: Loading indicator centered on screen */
        #common_dataTable_processing.dataTables_processing {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            height: 100% !important;
            display: none !important;
            z-index: 9999 !important;
            background: rgba(255, 255, 255, 0.95) !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            color: #374151 !important;
        }
        #common_dataTable_processing.dataTables_processing[style*="display: block"] {
            display: flex !important;
        }
        /* Table width fix */
        #common_dataTable {
            width: 100% !important;
        }
        #common_dataTable_wrapper {
            padding: 1rem;
        }
    </style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
    <!-- Header Card -->
    <div class="bg-white rounded-xl border-2 border-blue-300 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-blue-800 mb-2">Loan Requests</h1>
                <p class="text-blue-600 text-sm">Manage loan requests from different clients</p>
            </div>
            <div class="hidden md:block">
                <svg class="w-16 h-16 text-blue-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        @if(session('error'))
            <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @elseif(session('success'))
            <div class="mt-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @elseif(session('warning'))
            <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-yellow-700 font-medium">{{ session('warning') }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">All Loan Requests</h3>
        </div>
        <div class="overflow-x-auto">
            <table id="common_dataTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden">Id</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contract No.</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Name</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">First Name</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Middle Name</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Date Requested</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Remarks Modal -->
<div id="remarksModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md sm:max-w-lg lg:max-w-md mx-auto bg-white rounded-lg shadow-xl">
        <div class="p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Remarks</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="whitespace-pre-wrap text-sm text-gray-700" id="loanRequestRemarks"></pre>
            </div>
            <div class="mt-4">
                <button type="button" onclick="closeRemarksModal()" 
                    class="w-full sm:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-4 sm:py-2 sm:px-4 rounded focus:outline-none focus:shadow-outline text-sm sm:text-base min-h-[44px]">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md sm:max-w-lg lg:max-w-md mx-auto bg-white rounded-lg shadow-xl">
        <div class="p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Confirm Delete</h3>
            <p class="text-gray-600 mb-2">Delete selected loan request?</p>
            <p class="text-red-600 text-sm mb-4">You cannot undo this action. Continue?</p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex flex-col sm:flex-row sm:justify-between gap-2 sm:gap-0">
                    <button type="button" onclick="closeDeleteModal()" 
                        class="w-full sm:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-4 sm:py-2 sm:px-4 rounded focus:outline-none focus:shadow-outline text-sm sm:text-base min-h-[44px]">
                        Cancel
                    </button>
                    <button type="button" onclick="confirmDelete()" 
                        class="w-full sm:w-auto bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-4 sm:py-2 sm:px-4 rounded focus:outline-none focus:shadow-outline text-sm sm:text-base min-h-[44px]">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        var loadedTable = $('#common_dataTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
            autoWidth: false,
            ajax: {
                url: '/req-loans',
                type: 'GET'
            },
            columns: [
                { data: 'Id', name: 'Id', visible: false },
                { data: 'ContractNumber', name: 'tblclient.ContractNumber' },
                { data: 'LastName', name: 'tblclient.LastName' },
                { data: 'FirstName', name: 'tblclient.FirstName', className: 'hidden sm:table-cell' },
                { data: 'MiddleName', name: 'tblclient.MiddleName', className: 'hidden md:table-cell' },
                { 
                    data: 'Amount', 
                    name: 'Amount',
                    render: function(data, type, row) {
                        return '₱ ' + parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 2});
                    }
                },
                { data: 'DateRequested', name: 'DateRequested', className: 'hidden md:table-cell' },
                { 
                    data: 'Status', 
                    name: 'Status',
                    render: function (data, type, row) {
                        var status = row.Status;
                        if (status == "Pending") {
                            return '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">' + status + '</span>';
                        } else if (status == "Verified") {
                            return '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">' + status + '</span>';
                        } else if (status == "Approved") {
                            return '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">' + status + '</span>';
                        } else {
                            return '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium">' + status + '</span>';
                        }
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        var viewBtn = '<a href="/req-loans/view/' + data.Id + '" class="inline-flex items-center px-3 py-1 bg-blue-500 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors">' +
                            '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />' +
                            '</svg>View</a>';

                        var remarksBtn = '';
                        if (data.Remarks != null && data.Remarks != "" && data.Remarks != "Not available") {
                            remarksBtn = '<button type="button" onclick="openRemarksModal(\'' + escapeHtml(data.Remarks) + '\')" class="inline-flex items-center px-3 py-1 bg-yellow-500 hover:bg-yellow-700 text-white text-xs font-medium rounded transition-colors">' +
                                '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />' +
                                '</svg>Remarks</button>';
                        }

                        var deleteBtn = '<button type="button" onclick="openDeleteModal(' + data.Id + ')" class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-700 text-white text-xs font-medium rounded transition-colors">' +
                            '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />' +
                            '</svg>Delete</button>';

                        return '<div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">' + viewBtn + ' ' + remarksBtn + ' ' + deleteBtn + '</div>';
                    }
                }
            ],
            columnDefs: [
                {
                    targets: [8],
                    orderable: false,
                    width: '200px'
                }
            ],
            order: [[0, 'desc']]
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, '&amp;')
                   .replace(/</g, '&lt;')
                   .replace(/>/g, '&gt;')
                   .replace(/"/g, '&quot;')
                   .replace(/'/g, '&#039;');
    }

    let currentDeleteId = null;

    function openRemarksModal(remarks) {
        document.getElementById('loanRequestRemarks').textContent = remarks;
        document.getElementById('remarksModal').classList.remove('hidden');
    }

    function closeRemarksModal() {
        document.getElementById('remarksModal').classList.add('hidden');
    }

    function openDeleteModal(id) {
        currentDeleteId = id;
        document.getElementById('deleteForm').action = '/submit-req-loan-delete/' + id;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        currentDeleteId = null;
    }

    function confirmDelete() {
        document.getElementById('deleteForm').submit();
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        let remarksModal = document.getElementById('remarksModal');
        let deleteModal = document.getElementById('deleteModal');
        if (event.target == remarksModal) {
            closeRemarksModal();
        }
        if (event.target == deleteModal) {
            closeDeleteModal();
        }
    }
</script>
@endsection

@endsection
