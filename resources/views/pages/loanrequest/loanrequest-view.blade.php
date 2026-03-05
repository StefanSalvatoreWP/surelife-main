@extends('layouts.main')

@section('title', 'View Loan Request')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/client.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
    <!-- Header Card -->
    <div class="bg-white rounded-xl border-2 border-blue-300 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-blue-800 mb-2">View Loan Request</h1>
                <p class="text-blue-600 text-sm">Manage loan request for this selected client</p>
            </div>
            <div class="hidden md:block">
                <svg class="w-16 h-16 text-blue-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="/req-loans" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-700 text-white text-sm font-medium rounded transition-colors min-h-[44px]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Return
            </a>
            <a href="/client-view/{{ $clientDetails->Id }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-700 text-white text-sm font-medium rounded transition-colors min-h-[44px]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                View Client
            </a>
            @if($loanRequestDetails->Status == 'Pending')
                <button onclick="showLoanRequestModal('{{ $loanRequestDetails->Id }}', 'verify')" class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-700 text-white text-sm font-medium rounded transition-colors min-h-[44px]">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Verify
                </button>
            @elseif($loanRequestDetails->Status == 'Verified')
                <button onclick="showLoanRequestModal('{{ $loanRequestDetails->Id }}', 'approve')" class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-700 text-white text-sm font-medium rounded transition-colors min-h-[44px]">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approve
                </button>
            @endif
        </div>
    </div>

    <!-- Client Information Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Client Information</h3>
        </div>
        <div class="p-6">
            <!-- Client Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Contract Number</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $clientDetails->ContractNumber }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Client Name</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $clientDetails->LastName . ', ' . $clientDetails->FirstName . ' ' . $clientDetails->MiddleName }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Branch</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $clientBranch->BranchName }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Payment Mode</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $clientTerm->Term }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Amount</p>
                    <p class="text-lg font-semibold text-green-600">₱ {{ number_format($clientTerm->Price, 2) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Status</p>
                    @php
                        $statusColor = $loanRequestDetails->Status == 'Pending' ? 'yellow' : ($loanRequestDetails->Status == 'Verified' ? 'blue' : ($loanRequestDetails->Status == 'Approved' ? 'green' : 'gray'));
                    @endphp
                    <span class="bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 px-3 py-1 rounded-full text-sm font-medium">{{ $loanRequestDetails->Status }}</span>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">Loan Term Selected</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $termMonths }} months</p>
                    <p class="text-sm text-blue-600 mt-1">Interest Rate: {{ $interestRate }}%/month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Computation Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Loanable Amount Computation -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                <h3 class="text-lg font-semibold text-blue-800">Computation of Loanable Amount</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Contract Price</span>
                        <span class="font-semibold text-gray-900">₱ {{ number_format($contractPrice, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Total Premiums Paid</span>
                        <span class="font-semibold text-gray-900">₱ {{ number_format($totalPremiumsPaid, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Premium Paid Percentage (%)</span>
                        <span class="font-semibold text-gray-900">{{ $premiumPaidPercent }}%</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Loanable Percentage (Tier)</span>
                        <span class="font-semibold text-gray-900">
                            @php
                                $tierPercentages = [60 => 30, 80 => 40, 100 => 45];
                                echo ($tierPercentages[$premiumPaidPercent] ?? 0) . '%';
                            @endphp
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Gross Loanable Amount</span>
                        <span class="font-semibold text-gray-900">₱ {{ number_format($grossLoanableAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-red-600">Less: Processing Fee (10%)</span>
                        <span class="font-semibold text-red-600">₱ {{ number_format($processingFee, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 bg-blue-50 rounded-lg px-3 mt-2">
                        <span class="font-bold text-blue-800">Net Loanable Amount</span>
                        <span class="font-bold text-blue-800 text-lg">₱ {{ number_format($netLoanableAmount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Dues Computation -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                <h3 class="text-lg font-semibold text-green-800">Computation of Monthly Dues with Interest</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Gross Loanable Amount</span>
                        <span class="font-semibold text-gray-900">₱ {{ number_format($grossLoanableAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Term (Months)</span>
                        <span class="font-semibold text-gray-900">{{ $termMonths }} months</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Interest Rate</span>
                        <span class="font-semibold text-gray-900">{{ $interestRate }}%</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Total Interest</span>
                        <span class="font-semibold text-gray-900">₱ {{ number_format($totalInterest, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Total Repayable</span>
                        <span class="font-semibold text-gray-900">₱ {{ number_format($totalRepayable, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Monthly Loan Payment</span>
                        <span class="font-semibold text-gray-900">₱ {{ number_format($monthlyLoanPayment, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-amber-600 font-medium">Interest Rate per Month</span>
                        <span class="font-semibold text-amber-600">{{ $interestRate }}%</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-amber-600 font-medium">Total Interest ({{ $termMonths }} months)</span>
                        <span class="font-semibold text-amber-600">₱ {{ number_format($totalInterest, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600">Monthly Interest</span>
                        <span class="font-semibold text-gray-900">₱ {{ number_format($monthlyInterest, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 bg-green-50 rounded-lg px-3 mt-2">
                        <span class="font-bold text-green-800">Total Monthly Due ({{ $termMonths }} months)</span>
                        <span class="font-bold text-green-800 text-lg">₱ {{ number_format($monthlyTotalDue, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Waiver & Signature Section -->
    @if($loanWaiver)
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
            <h3 class="text-lg font-semibold text-purple-800">Waiver of Rights & Digital Signature</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Waiver Info -->
                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Client Name</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $loanWaiver->client_name }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Contract Number</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $loanWaiver->contract_number }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Signed Date</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $loanWaiver->signed_date ? date('F d, Y h:i A', strtotime($loanWaiver->signed_date)) : 'N/A' }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                        <p class="text-xs text-purple-600 uppercase tracking-wide mb-1">Waiver Status</p>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">Signed</span>
                    </div>
                </div>
                <!-- Signature Image -->
                <div class="flex flex-col items-center justify-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Digital Signature</p>
                    <div class="border-2 border-gray-300 rounded-lg p-4 bg-white w-full max-w-md">
                        @if($loanWaiver->signature_data)
                            <img src="{{ $loanWaiver->signature_data }}" alt="Client Signature" class="w-full h-auto max-h-32 object-contain">
                        @else
                            <p class="text-gray-400 text-center">No signature available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Waiver of Rights</h3>
        </div>
        <div class="p-6">
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-yellow-700 font-medium">No waiver signature found for this loan request.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script src="{{ asset('js/req-loan.js') }}"></script>
<script>
    function showLoanRequestModal(loanReqId, action) {
        const actionText = action === 'verify' ? 'verify' : 'approve';
        showSwiftModal('Confirmation', `You are going to ${actionText} this loan request.\n\nYou cannot undo this action. Continue?`, 'warning', [
            {text: 'Submit', class: 'bg-green-500 hover:bg-green-600 text-white', action: 'submitLoanRequest(' + loanReqId + ')'},
            {text: 'Close', class: 'bg-gray-200 hover:bg-gray-300 text-gray-800'}
        ]);
    }
    
    function submitLoanRequest(loanReqId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/submit-req-loan/' + loanReqId;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'PUT';
        
        form.appendChild(csrfToken);
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection
