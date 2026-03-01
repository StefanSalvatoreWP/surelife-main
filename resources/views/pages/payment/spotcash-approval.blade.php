@extends('layouts.main')

@section('title', 'Spot Cash Payment Approval')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/client.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
    <div class="bg-white rounded-xl border-2 border-green-300 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-green-800 mb-2">Spot Cash Payment Approval</h1>
                <p class="text-green-600 text-sm">Review and approve spot cash payments</p>
            </div>
            <div class="hidden md:block">
                <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Pending Spot Cash Payments</h3>
        </div>
        <div class="overflow-x-auto">
            <table id="spotcash-approval-table" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">OR Number</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Client Name</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Contract No.</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Date</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md sm:max-w-lg lg:max-w-md mx-auto bg-white rounded-lg shadow-xl">
        <div class="p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Approve Payment</h3>
            <form id="approveForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="approval_remarks">
                        Remarks (Optional)
                    </label>
                    <textarea name="approval_remarks" id="approval_remarks" rows="3" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm sm:text-base"
                        placeholder="Enter approval remarks..."></textarea>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-2 sm:gap-0">
                    <button type="button" onclick="closeModal()" 
                        class="w-full sm:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-4 sm:py-2 sm:px-4 rounded focus:outline-none focus:shadow-outline text-sm sm:text-base min-h-[44px]">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="w-full sm:w-auto bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-4 sm:py-2 sm:px-4 rounded focus:outline-none focus:shadow-outline text-sm sm:text-base min-h-[44px]">
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md sm:max-w-lg lg:max-w-md mx-auto bg-white rounded-lg shadow-xl">
        <div class="p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Reject Payment</h3>
            <form id="rejectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="rejection_remarks">
                        Remarks (Required)
                    </label>
                    <textarea name="approval_remarks" id="rejection_remarks" rows="3" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm sm:text-base"
                        placeholder="Enter rejection reason..." required></textarea>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-2 sm:gap-0">
                    <button type="button" onclick="closeRejectionModal()" 
                        class="w-full sm:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-4 sm:py-2 sm:px-4 rounded focus:outline-none focus:shadow-outline text-sm sm:text-base min-h-[44px]">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="w-full sm:w-auto bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-4 sm:py-2 sm:px-4 rounded focus:outline-none focus:shadow-outline text-sm sm:text-base min-h-[44px]">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        $('#spotcash-approval-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
            autoWidth: false,
            ajax: {
                url: '{{ route("spotcash.approval") }}',
                type: 'GET'
            },
            columns: [
                { data: 'ORNo', name: 'tblpayment.ORNo' },
                { data: null, name: 'tblclient.LastName', render: function(data) {
                    return data.LastName + ', ' + data.FirstName + ' ' + (data.MiddleName || '');
                }},
                { data: 'ContractNumber', name: 'tblclient.ContractNumber', className: 'hidden sm:table-cell' },
                { data: 'amountpaid', name: 'tblpayment.amountpaid', render: function(data) {
                    return '₱ ' + parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 2});
                }},
                { data: 'date', name: 'tblpayment.date', className: 'hidden md:table-cell' },
                { data: 'approval_status', name: 'tblpayment.approval_status', render: function(data) {
                    return '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">' + data + '</span>';
                }},
                { data: null, orderable: false, render: function(data) {
                    var approveBtn = '<button type="button" onclick="openModal(' + data.id + ')" class="action-btn action-btn-assign text-xs sm:text-sm">' +
                        '<svg class="action-icon w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />' +
                        '</svg>' +
                        '<span class="hidden sm:inline">Approve</span>' +
                        '</button>';

                    var rejectBtn = '<button type="button" onclick="openRejectionModal(' + data.id + ')" class="action-btn action-btn-delete text-xs sm:text-sm">' +
                        '<svg class="action-icon w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />' +
                        '</svg>' +
                        '<span class="hidden sm:inline">Reject</span>' +
                        '</button>';

                    return '<div style="display: flex; gap: 0.5rem;" class="flex-col sm:flex-row">' + approveBtn + rejectBtn + '</div>';
                }}
            ],
            columnDefs: [
                {
                    targets: [6],
                    orderable: false,
                    width: '200px'
                }
            ],
            order: [[0, 'desc']]
        });
    });

    let currentPaymentId = null;

    function openModal(paymentId) {
        currentPaymentId = paymentId;
        document.getElementById('approveForm').action = '/spotcash-approve/' + paymentId;
        document.getElementById('approvalModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('approvalModal').classList.add('hidden');
        currentPaymentId = null;
    }

    function openRejectionModal(paymentId) {
        currentPaymentId = paymentId;
        document.getElementById('rejectForm').action = '/spotcash-reject/' + paymentId;
        document.getElementById('rejectionModal').classList.remove('hidden');
    }

    function closeRejectionModal() {
        document.getElementById('rejectionModal').classList.add('hidden');
        currentPaymentId = null;
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        let approvalModal = document.getElementById('approvalModal');
        let rejectionModal = document.getElementById('rejectionModal');
        if (event.target == approvalModal) {
            closeModal();
        }
        if (event.target == rejectionModal) {
            closeRejectionModal();
        }
    }
</script>
@endsection

@endsection
