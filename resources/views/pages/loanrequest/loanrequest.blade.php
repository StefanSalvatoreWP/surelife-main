@extends('layouts.main')

@section('title', 'Loan Requests')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/client.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/deposit.css') }}?v={{ time() }}">
    <style>
        /* Loan Request Table Header - Page Specific */
        #loanrequest-table thead th {
            background: linear-gradient(135deg, #dae4e1 0%, #c5cecb 100%) !important;
            color: rgb(95, 89, 89) !important;
            font-weight: 700 !important;
            padding: 1rem 1.5rem !important;
            border: none !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        #loanrequest-table tbody tr {
            background: white !important;
            transition: all 0.2s ease !important;
            border-bottom: 1px solid #f3f4f6;
        }
        #loanrequest-table tbody tr:hover {
            background: #faf5ff !important;
            transform: translateX(4px) !important;
            box-shadow: -4px 0 0 0 #9333ea, 0 2px 8px rgba(147, 51, 234, 0.1) !important;
        }
        #loanrequest-table tbody td {
            padding: 1rem 1.5rem !important;
            border: none !important;
            vertical-align: middle !important;
            color: #374151;
            font-size: 0.875rem;
        }
    </style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
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

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">All Loan Requests</h3>
        </div>
        <div class="overflow-x-auto">
            <table id="loanrequest-table" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contract No.</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Client Name</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Amount</th>
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
        $('#loanrequest-table').DataTable({
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
                { data: 'ContractNumber', name: 'tblclient.ContractNumber' },
                { data: null, name: 'tblclient.LastName', render: function(data) {
                    return data.LastName + ', ' + data.FirstName + ' ' + (data.MiddleName || '');
                }},
                { data: 'Amount', name: 'Amount', className: 'hidden sm:table-cell', render: function(data) {
                    return '₱ ' + parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 2});
                }},
                { data: 'DateRequested', name: 'DateRequested', className: 'hidden md:table-cell' },
                { data: 'Status', name: 'Status', render: function(data) {
                    var status = data;
                    if (status == "Pending") {
                        return '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">' + status + '</span>';
                    } else if (status == "Verified") {
                        return '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">' + status + '</span>';
                    } else if (status == "Approved") {
                        return '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">' + status + '</span>';
                    } else {
                        return '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium">' + status + '</span>';
                    }
                }},
                { data: null, orderable: false, render: function(data) {
                    // If status is Completed, use 3-dots dropdown
                    if (data.Status === 'Completed') {
                        var dropdownHtml = '<div class="action-dropdown">' +
                            '<button onclick="toggleDropdown(this)" class="action-dropdown-btn">' +
                            '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">' +
                            '<path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>' +
                            '</svg></button>' +
                            '<div class="action-dropdown-menu">';
                        
                        // View option
                        dropdownHtml += '<a href="/req-loans/view/' + data.Id + '" class="dropdown-item">' +
                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />' +
                            '</svg><span>View</span></a>';
                        
                        // Remarks option (always show for Completed)
                        dropdownHtml += '<button type="button" onclick="openRemarksModal(\'' + escapeHtml(data.Remarks || 'No remarks') + '\')" class="dropdown-item">' +
                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />' +
                            '</svg><span>Remarks</span></button>';
                        
                        // Delete option
                        dropdownHtml += '<button type="button" onclick="openDeleteModal(' + data.Id + ')" class="dropdown-item dropdown-item-delete">' +
                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />' +
                            '</svg><span>Delete</span></button>';
                        
                        dropdownHtml += '</div></div>';
                        return dropdownHtml;
                    }
                    
                    // Normal status: View and Delete buttons
                    var viewBtn = '<a href="/req-loans/view/' + data.Id + '" class="action-btn action-btn-view text-xs sm:text-sm">' +
                        '<svg class="action-icon w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />' +
                        '</svg>' +
                        '<span class="hidden sm:inline">View</span>' +
                        '</a>';

                    var deleteBtn = '<button type="button" onclick="openDeleteModal(' + data.Id + ')" class="action-btn action-btn-delete text-xs sm:text-sm">' +
                        '<svg class="action-icon w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />' +
                        '</svg>' +
                        '<span class="hidden sm:inline">Delete</span>' +
                        '</button>';

                    return '<div style="display: flex; gap: 0.5rem;" class="flex-col sm:flex-row">' + viewBtn + deleteBtn + '</div>';
                }}
            ],
            columnDefs: [
                {
                    targets: [5],
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

    // Toggle dropdown for 3-dots menu
    function toggleDropdown(btn) {
        const dropdown = btn.closest('.action-dropdown');
        const menu = dropdown.querySelector('.action-dropdown-menu');
        const isOpen = menu.classList.contains('show');
        
        // Close all other dropdowns
        document.querySelectorAll('.action-dropdown-menu.show').forEach(m => {
            m.classList.remove('show');
        });
        document.querySelectorAll('.action-dropdown.open').forEach(d => {
            d.classList.remove('open');
        });
        document.body.classList.remove('dropdown-open');
        
        // Toggle current dropdown
        if (!isOpen) {
            menu.classList.add('show');
            dropdown.classList.add('open');
            document.body.classList.add('dropdown-open');
        }
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
