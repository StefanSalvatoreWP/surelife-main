<!-- 2023 SilverDust) S. Maceren -->
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white border-2 border-green-200 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">
                        View Client ( {{ $clients->LastName . ', ' . $clients->FirstName }} )
                        @if($clients->CompletedMemorial && $clients->CompletedMemorial == 1)
                            - <span class="text-green-600">Served</span>
                        @endif
                    </h1>
                    <p class="text-green-600 text-sm">View client information and payment history</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @php
            $message = 'client';

            // Calculate total price based on payment term
            $base_price = $clients->Price;
            switch ($clients->Term) {
                case "Spotcash":
                    $total_price = $base_price;
                    break;
                case "Annual":
                    $total_price = $base_price * 5;
                    break;
                case "Semi-Annual":
                    $total_price = ($base_price * 2) * 5;
                    break;
                case "Quarterly":
                    $total_price = ($base_price * 4) * 5;
                    break;
                case "Monthly":
                    $total_price = $base_price * 60;
                    break;
                default:
                    $total_price = $base_price;
            }

            // Calculate total payments and last valid payment date in a single loop
            $total_payments = 0;
            $lastValidPaymentDate = null;

            foreach ($payments as $payment) {
                // Check for general inclusion (Standard, Partial, Custom)
                $isStandardOrCustom = ($payment->VoidStatus != '1' &&
                    ($payment->Remarks == null ||
                        $payment->Remarks == 'Standard' ||
                        $payment->Remarks == 'Partial' ||
                        $payment->Remarks == 'Custom'));

                if ($isStandardOrCustom) {
                    $total_payments += $payment->AmountPaid;
                }

                // Check for payment status (includes Reinstatement)
                if (
                    $payment->VoidStatus != '1' &&
                    ($payment->Remarks == null ||
                        $payment->Remarks == 'Standard' ||
                        $payment->Remarks == 'Partial' ||
                        $payment->Remarks == 'Custom' ||
                        $payment->Remarks == 'Reinstatement') &&
                    $payment->AmountPaid > 0
                ) {
                    $pDate = \Carbon\Carbon::parse($payment->Date);
                    if ($lastValidPaymentDate == null || $pDate->gt($lastValidPaymentDate)) {
                        $lastValidPaymentDate = $pDate;
                    }
                }
            }

            // Calculate final balance
            $balance = $total_price - $total_payments;
            $isFullyPaid = $balance <= 0;
            $totalValidPayments = $total_payments;
        @endphp

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @elseif(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
            @php
                if (session('success') == 'Successfully updated the selected client!') {
                    $message = 'client';
                } elseif (session('success') == 'Added new payment!') {
                    $message = 'payment';
                } elseif (session('success') == 'Void payment successful!') {
                    $message = 'payment';
                } elseif (session('success') == 'Added new loan payment!') {
                    $message = 'loan';
                } elseif (session('success') == 'Successfully assigned.') {
                    $message = 'assign';
                }
            @endphp
        @elseif(session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-yellow-700 font-medium">{{ session('warning') }}</p>
                </div>
            </div>
        @elseif(session('approve-cfp-success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-green-700 font-medium">{{ session('approve-cfp-success') }}</p>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-4 mb-6">
            @php
                $statusQuery = request('status') ? ('?status=' . urlencode(request('status'))) : '';
            @endphp
            <a href="{{ '/client' . $statusQuery }}"
                class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Return
            </a>
            <a href="/client-update/{{ $clients->cid }}"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Update
            </a>
            <input type="hidden" id="clientid" value="{{ $clients->cid }}" />
            @if($clients->Status == '1')
                <a class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    data-bs-toggle="modal" data-bs-target="#clientStatusModal" data-client-id="{{ $clients->cid }}"
                    data-client-name="{{ $clients->LastName . ', ' . $clients->FirstName }}" role="button">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Verify
                </a>
            @elseif($clients->Status == '2')
                <a class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    data-bs-toggle="modal" data-bs-target="#clientStatusModal" data-client-id="{{ $clients->cid }}"
                    data-client-name="{{ $clients->LastName . ', ' . $clients->FirstName }}" role="button">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Approve
                </a>
            @endif
            <div class="ml-auto flex gap-4">
                @if(!$canTransfer || ($canTransfer && $canTransfer->TransferClientId == null))
                    {{-- Enable transfer if no record exists OR if record exists but transfer not yet completed --}}
                    <a href="/transfer-client-create/{{ $clients->cid }}"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        role="button">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Transfer Ownership
                    </a>
                @else
                    {{-- Disable transfer if a transfer is already in progress (TransferClientId is set) --}}
                    <button
                        class="inline-flex items-center px-6 py-3 bg-gray-400 text-white font-semibold rounded-lg shadow-md cursor-not-allowed opacity-60"
                        role="button" disabled>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Transfer Ownership
                    </button>
                @endif

                @if(!$clients->CompletedMemorial || $clients->CompletedMemorial == 0)
                    <a data-bs-toggle="modal" data-bs-target="#completeMemorialModal"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        role="button">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Completed Service
                    </a>
                @endif
            </div>
        </div>
        <!-- Tabs Navigation -->
        <ul class="flex flex-wrap border-b border-gray-200 mb-6" id="clientTabs" role="tablist">
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block px-6 py-3 rounded-t-lg {{ $message === 'client' ? 'text-purple-600 border-b-2 border-purple-600 font-semibold' : 'text-gray-500 hover:text-purple-600 hover:border-purple-300' }}"
                    id="client-info-tab" data-bs-toggle="tab" data-bs-target="#clientInfo" type="button" role="tab"
                    aria-controls="clientInfo" aria-selected="{{ $message === 'client' ? 'true' : 'false' }}">Client
                    Information</button>
            </li>
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block px-6 py-3 rounded-t-lg {{ $message === 'payment' ? 'text-purple-600 border-b-2 border-purple-600 font-semibold' : 'text-gray-500 hover:text-purple-600 hover:border-purple-300' }}"
                    id="payment-history-tab" data-bs-toggle="tab" data-bs-target="#paymentHistory" type="button" role="tab"
                    aria-controls="paymentHistory" aria-selected="{{ $message === 'payment' ? 'true' : 'false' }}">Payment
                    History</button>
            </li>
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block px-6 py-3 rounded-t-lg {{ $message === 'loan' ? 'text-purple-600 border-b-2 border-purple-600 font-semibold' : 'text-gray-500 hover:text-purple-600 hover:border-purple-300' }}"
                    id="loan-payments-tab" data-bs-toggle="tab" data-bs-target="#loanPayments" type="button" role="tab"
                    aria-controls="loanPayments" aria-selected="{{ $message === 'loan' ? 'true' : 'false' }}">Loan
                    Payments</button>
            </li>
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block px-6 py-3 rounded-t-lg {{ $message === 'assign' ? 'text-purple-600 border-b-2 border-purple-600 font-semibold' : 'text-gray-500 hover:text-purple-600 hover:border-purple-300' }}"
                    id="assigned-member-tab" data-bs-toggle="tab" data-bs-target="#assignedMember" type="button" role="tab"
                    aria-controls="assignedMember" aria-selected="{{ $message === 'assign' ? 'true' : 'false' }}">Assigned
                    Member</button>
            </li>
        </ul>
        <div class="tab-content" id="clientTabsContent">
            <div class="tab-pane fade {{ $message === 'client' ? 'show active' : '' }}" id="clientInfo" role="tabpanel"
                aria-labelledby="client-info-tab">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Client Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Contract Section -->
                        <div>
                            <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Contract</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contract No.</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->ContractNumber }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Package</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Package }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Term . ' ( ₱ ' . number_format($clients->Price, 2) . ' )' }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->RegionName }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->BranchName }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Best place to collect</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->BestPlaceToCollect }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Best time to collect</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->BestTimeToCollect }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <div class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 flex items-center h-[42px] cursor-default">
                                        @if($clients->Status == '1')
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">Pending</span>
                                        @elseif($clients->Status == '2')
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-200 text-blue-700">Verified</span>
                                        @elseif($clients->Status == '3')
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-200 text-green-700">Approved</span>
                                        @endif
                                    </div>
                                </div>

                                @php
                                    $paymentStatus = 'Active';
                                    $statusClass = 'bg-green-200 text-green-700';
                                    $ninetyDaysAgo = \Carbon\Carbon::now()->subDays(90);

                                    if ($isFullyPaid || $totalValidPayments >= $total_price) {
                                        $paymentStatus = 'Fully Paid';
                                        $statusClass = 'bg-green-300 text-green-800';
                                    } elseif (is_null($lastValidPaymentDate) || $lastValidPaymentDate->lt($ninetyDaysAgo)) {
                                        $paymentStatus = 'Lapse';
                                        $statusClass = 'bg-red-200 text-red-700';
                                    }
                                @endphp
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                                    <div class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 flex items-center h-[42px] cursor-default">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $paymentStatus }}</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">FSA</label>
                                    <div class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 flex items-center h-[42px] overflow-hidden cursor-default">
                                        <span class="text-gray-900 truncate">
                                            @if($clients->FSALastName)
                                                {{ $clients->FSALastName . ', ' . $clients->FSAFirstName . ' ' . ($clients->FSAMiddleName ?? '') }}
                                            @else
                                                <span class="text-gray-400 italic">Not assigned</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Section -->
                        <div>
                            <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Personal</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="md:col-span-2 lg:col-span-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div class="lg:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                                        <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->LastName . ', ' . $clients->FirstName . " " . $clients->MiddleName }}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Birth Date</label>
                                        <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->BirthDate }}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                                        <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Age }}" readonly />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Gender }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Civil Status</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->CivilStatus }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Occupation }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Birth Place</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->BirthPlace }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->ProvinceDisplay ?? $clients->Province }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->CityDisplay ?? $clients->City }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->BarangayDisplay ?? $clients->Barangay }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Zipcode</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->ZipCode }}" readonly />
                                </div>
                                <div class="lg:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Street }}" readonly />
                                </div>
                            </div>
                        </div>

                        <!-- Home Address Section -->
                        <div>
                            <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Home Address</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->HomeProvinceDisplay ?? $clients->HomeProvince ?? '-' }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->HomeCityDisplay ?? $clients->HomeCity ?? '-' }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->HomeBarangayDisplay ?? $clients->HomeBarangay ?? '-' }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Zipcode</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->HomeZipCode ?? '-' }}" readonly />
                                </div>
                                <div class="lg:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->HomeStreet ?? '-' }}" readonly />
                                </div>
                            </div>
                        </div>

                        <!-- Contact Section -->
                        <div>
                            <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Contact</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Home No.</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->HomeNumber }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mobile No.</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->MobileNumber }}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                    @php
                                        // Hide email if it starts with -@
                                        $displayEmail = $clients->EmailAddress;
                                        if (strpos($displayEmail, '-@') === 0) {
                                            $displayEmail = '';
                                        }
                                    @endphp
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $displayEmail }}" readonly />
                                </div>
                            </div>
                        </div>

                        <!-- Beneficiaries Section -->
                        <div>
                            <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Beneficiaries</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Principal (Age)</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->PrincipalBeneficiaryName . ' (' . $clients->PrincipalBeneficiaryAge . ')'}}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 1 (Age)</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Secondary1Name . ' (' . $clients->Secondary1Age . ')'}}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 2 (Age)</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Secondary2Name . ' (' . $clients->Secondary2Age . ')'}}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 3 (Age)</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Secondary3Name . ' (' . $clients->Secondary3Age . ')'}}" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 4 (Age)</label>
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 cursor-default" value="{{ $clients->Secondary4Name . ' (' . $clients->Secondary4Age . ')'}}" readonly />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="tab-pane fade {{ $message === 'payment' ? 'show active' : '' }}" id="paymentHistory" role="tabpanel"
                aria-labelledby="payment-history-tab">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Payment History
                        </h3>
                    </div>
                    <div class="p-6">
                        {{-- Total price and balance calculations moved to top of file --}}

                        <!-- Payment Summary Cards -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-{{ !$isFullyPaid ? '4' : '3' }} gap-4 mb-6">
                            <!-- Package Price Card -->
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-blue-600 font-medium mb-1">Package Price</p>
                                        <p class="text-2xl font-bold text-blue-900">₱ {{ number_format($total_price, 2) }}
                                        </p>
                                    </div>
                                    <div class="bg-blue-200 rounded-full p-3">
                                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Package Payment Card -->
                            <div
                                class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-purple-600 font-medium mb-1">Total Package Payment</p>
                                        <p class="text-2xl font-bold text-purple-900">₱
                                            {{ number_format($total_payments, 2) }}
                                        </p>
                                    </div>
                                    <div class="bg-purple-200 rounded-full p-3">
                                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Balance Card -->

                            @if($balance > 0)
                                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-red-600 font-medium mb-1">Outstanding Balance</p>
                                            <p class="text-2xl font-bold text-red-900">₱ {{ number_format($balance, 2) }}</p>
                                        </div>
                                        <div class="bg-red-200 rounded-full p-3">
                                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div
                                    class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-green-600 font-medium mb-1">Balance</p>
                                            <p class="text-2xl font-bold text-green-900">₱ {{ number_format($balance, 2) }}</p>
                                            <p class="text-xs text-green-600 mt-1">Fully Paid</p>
                                        </div>
                                        <div class="bg-green-200 rounded-full p-3">
                                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Payment Status Card - Hidden for fully paid clients -->
                            @if(!$isFullyPaid)
                                @php
                                    $paymentStatus = 'Active';
                                    $isLapsed = false;
                                    $ninetyDaysAgo = \Carbon\Carbon::now()->subDays(90);

                                    // Use $lastValidPaymentDate calculated at the top of the file
                                    // consistently with the Client Information tab
                                    if (is_null($lastValidPaymentDate) || $lastValidPaymentDate->lt($ninetyDaysAgo)) {
                                        $paymentStatus = 'Lapse';
                                        $isLapsed = true;
                                    }
                                @endphp
                                @if($isLapsed)
                                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm text-red-600 font-medium mb-1">Payment Status</p>
                                                <p class="text-2xl font-bold text-red-900">{{ $paymentStatus }}</p>
                                            </div>
                                            <div class="bg-red-200 rounded-full p-3">
                                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div
                                        class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm text-green-600 font-medium mb-1">Payment Status</p>
                                                <p class="text-2xl font-bold text-green-900">{{ $paymentStatus }}</p>
                                            </div>
                                            <div class="bg-green-200 rounded-full p-3">
                                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button id="filter-clear"
                                class="payment-filter-btn px-4 py-2 bg-white border-2 border-green-200 text-green-800 rounded-lg font-semibold text-sm hover:border-green-300 hover:text-green-900 transition duration-200 active"
                                data-filter="clear">
                                Clear Filter
                            </button>
                            <button id="filter-plan"
                                class="payment-filter-btn px-4 py-2 bg-white border-2 border-green-200 text-green-800 rounded-lg font-semibold text-sm hover:border-green-300 hover:text-green-900 transition duration-200"
                                data-filter="Plan">
                                Plan
                            </button>
                            <button id="filter-others"
                                class="payment-filter-btn px-4 py-2 bg-white border-2 border-green-200 text-green-800 rounded-lg font-semibold text-sm hover:border-green-300 hover:text-green-900 transition duration-200"
                                data-filter="Others">
                                Others
                            </button>
                            <button id="filter-void"
                                class="payment-filter-btn px-4 py-2 bg-white border-2 border-green-200 text-green-800 rounded-lg font-semibold text-sm hover:border-green-300 hover:text-green-900 transition duration-200"
                                data-filter="Void">
                                Void
                            </button>
                        </div>

                        <!-- Payment Table -->
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <table id="common_dataTable" class="table table-hover font-sm w-100">
                                <thead class="bg-gradient-to-r from-purple-50 to-indigo-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                            No</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                            Series Code</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                            OR No.</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                            Amount Paid</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                            Installment</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                            Date</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                            Payment Particular</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr data-remarks="{{ $payment->Remarks }}" data-void="{{ $payment->VoidStatus }}">
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $payment->Id }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $payment->SeriesCode ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $payment->ORNo }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">₱
                                                {{ number_format($payment->AmountPaid, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $payment->Installment ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $payment->Date }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                @if($payment->VoidStatus == 1)
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Void</span>
                                                @elseif($payment->Remarks == null || $payment->Remarks == 'Standard' || $payment->Remarks == 'Partial')
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $payment->Remarks ?? 'Standard' }}</span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $payment->Remarks }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                @if($payment->VoidStatus != 1)
                                                    <a class="action-void text-red-600 hover:text-red-900 font-medium cursor-pointer"
                                                        data-bs-toggle="modal" data-bs-target="#paymentVoidModal"
                                                        data-payment-id="{{ $payment->Id }}"
                                                        data-payment-orno="{{ $payment->ORNo }}">Void</a>
                                                @else
                                                    <span class="text-gray-400">Locked</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(!$canTransfer)
                            @if($clients->Status == '3')
                                @if($balance > 0)
                                    @if($assignedMemberData != null)
                                        <a href="/client-addpayment/{{ $clients->cid }}"
                                            class="inline-flex items-center px-6 py-3 bg-white border-2 border-green-200 hover:border-green-300 text-green-800 hover:text-green-900 font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                            role="button">Create Full Payment</a>
                                    @else
                                        <a href="/client-addpayment/{{ $clients->cid }}"
                                            class="inline-flex items-center px-6 py-3 bg-white border-2 border-green-200 hover:border-green-300 text-green-800 hover:text-green-900 font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                            role="button">Add Payment</a>
                                    @endif
                                @endif
                                <a href="/client-printsoa/{{ $clients->cid }}?export=true"
                                    class="inline-flex items-center px-6 py-3 bg-white border-2 border-purple-200 hover:border-purple-300 text-purple-800 hover:text-purple-900 font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                    role="button" target="_blank">Statement of Account</a>
                                @if($balance <= 0)
                                    @if($clients->CFPNO == null && $cfpApprover == 0)
                                        <a class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                            data-bs-toggle="modal" data-bs-target="#showApproveCfpErrorInputModal"
                                            data-client-id="{{ $clients->cid }}" role="button">Certificate of Full Payment</a>
                                    @elseif($clients->CFPNO == null && $cfpApprover == 1)
                                        <a class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                            data-bs-toggle="modal" data-bs-target="#showApproveCfpInputModal"
                                            data-client-id="{{ $clients->cid }}" role="button">Certificate of Full Payment</a>
                                    @elseif($clients->CFPNO == "NA")
                                        <a class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                            data-bs-toggle="modal" data-bs-target="#showCfpNoInputModal"
                                            data-client-id="{{ $clients->cid }}" role="button">Certificate of Full Payment</a>
                                    @else
                                        <a href="/client-printcofp/{{ $clients->cid }}"
                                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                            role="button">Certificate of Full Payment</a>
                                    @endif
                                @endif
                            @else
                                <p class="mt-4 text-gray-500">** Client needs to be approved to add a new payment.</p>
                            @endif
                        @else
                            @if($canTransfer->TransferClientId != null)
                                <p class="mt-4 text-gray-500">** Ownership has been transferred to this <a
                                        href="/client-view/{{ $canTransfer->TransferClientId }}"
                                        class="text-purple-600 hover:text-purple-700 underline">client</a></p>
                            @else
                                @if($clients->Status == '3')
                                    @if($balance > 0)
                                        <a href="/client-addpayment/{{ $clients->cid }}"
                                            class="inline-flex items-center px-6 py-3 bg-white border-2 border-green-200 hover:border-green-300 text-green-800 hover:text-green-900 font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                            role="button">Add Payment</a>
                                    @endif
                                    <a href="/client-printsoa/{{ $clients->cid }}?export=true"
                                        class="inline-flex items-center px-6 py-3 bg-white border-2 border-purple-200 hover:border-purple-300 text-purple-800 hover:text-purple-900 font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                        role="button" target="_blank">Statement of Account</a>
                                    @if($balance <= 0)
                                        @if($clients->CFPNO == null && $cfpApprover == 0)
                                            <a class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                                data-bs-toggle="modal" data-bs-target="#showApproveCfpErrorInputModal"
                                                data-client-id="{{ $clients->cid }}" role="button">Certificate of Full Payment</a>
                                        @elseif($clients->CFPNO == null && $cfpApprover == 1)
                                            <a class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                                data-bs-toggle="modal" data-bs-target="#showApproveCfpInputModal"
                                                data-client-id="{{ $clients->cid }}" role="button">Certificate of Full Payment</a>
                                        @elseif($clients->CFPNO == "NA")
                                            <a class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                                data-bs-toggle="modal" data-bs-target="#showCfpNoInputModal"
                                                data-client-id="{{ $clients->cid }}" role="button">Certificate of Full Payment</a>
                                        @else
                                            <a href="/client-printcofp/{{ $clients->cid }}"
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4"
                                                role="button">Certificate of Full Payment</a>
                                        @endif
                                    @endif
                                @else
                                    <p class="mt-4 text-gray-500">** Client needs to be approved to add a new payment.</p>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="tab-pane fade {{ $message === 'loan' ? 'show active' : '' }}" id="loanPayments" role="tabpanel"
                aria-labelledby="loan-payments-tab">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Loan Payments
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($hasLoanRequest && $loanBalance > 0)
                            <!-- Loan Payment Table -->
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-4">
                                <table id="loan_paymentsTable" class="table table-hover font-sm w-100">
                                    <thead class="bg-gradient-to-r from-purple-50 to-indigo-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                                No</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                                Series Code</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                                OR No.</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                                Amount Paid</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                                Installment</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                                Date</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                                Status</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($loanPayments as $lp)
                                            <tr data-void="{{ $lp->status == 'void' ? '1' : '0' }}">
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $lp->id }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $lp->SeriesCode ?? 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $lp->orno }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">₱ {{ number_format($lp->amount, 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $lp->installment ?? 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $lp->paymentdate }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    @if($lp->status == 'void')
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Void</span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 text-capitalize">{{ $lp->status }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    @if($lp->status != 'void')
                                                        <a class="action-void text-red-600 hover:text-red-900 font-medium cursor-pointer"
                                                            data-bs-toggle="modal" data-bs-target="#loanPaymentVoidModal"
                                                            data-loan-payment-id="{{ $lp->id }}"
                                                            data-loan-payment-orno="{{ $lp->orno }}">Void</a>
                                                    @else
                                                        <span class="text-gray-400">Locked</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <a href="/client-addloanpayment/{{ $clients->cid }}"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out"
                                role="button">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Payment
                            </a>
                        @else
                            <div class="flex flex-col items-center justify-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-500 text-center">No recent loan request available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="tab-pane fade {{ $message === 'assign' ? 'show active' : '' }}" id="assignedMember" role="tabpanel"
                aria-labelledby="assigned-member-tab">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Assigned Member
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($assignedMemberData != null)
                            <div class="space-y-2">
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Name</span>
                                    <span class="text-gray-900">{{ $assignedMemberData->LastName }} ,
                                        {{ $assignedMemberData->FirstName }} {{ $assignedMemberData->MiddleName }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Gender</span>
                                    <span class="text-gray-900">{{ $assignedMemberData->Gender }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Birth Date</span>
                                    <span class="text-gray-900">{{ $assignedMemberData->BirthDate }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Age</span>
                                    <span class="text-gray-900">{{ $assignedMemberData->Age }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Province</span>
                                    <span class="text-gray-900">{{ $assignedMemberData->Province }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">City</span>
                                    <span class="text-gray-900">{{ $assignedMemberData->City }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Barangay</span>
                                    <span class="text-gray-900">{{ $assignedMemberData->Barangay }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Zipcode</span>
                                    <span class="text-gray-900">{{ $assignedMemberData->Zipcode }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Date Assigned</span>
                                    <span class="text-gray-900">{{ $assignedMemberData->DateCreated }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Assigned By</span>
                                    <span class="text-gray-900">{{ $staff->LastName }} , {{ $staff->FirstName }}
                                        {{ $staff->MiddleName }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium">Attachment</span>
                                    <span class="text-gray-900">
                                        @if($assignedMemberData->Attachment != null)
                                            <a href="{{ asset('uploads/assignedplans/' . $assignedMemberData->Attachment) }}"
                                                target="_blank" class="text-purple-600 hover:text-purple-700 underline">View</a>
                                        @else
                                            <span class="text-gray-400">No image available</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <form class="mt-6" action="/submit-client-assign-attachment/{{ $assignedMemberData->Id }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="border-t border-gray-200 pt-6">
                                    @php
                                        $prevAssignAttachment = old('assignattachment');
                                    @endphp
                                    <label for="assignAttachment" class="block text-sm font-medium text-gray-700 mb-2">Upload
                                        Attachment</label>
                                    <input type="file"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"
                                        id="assignAttachment" name="assignattachment" />
                                    @error('assignattachment')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <button type="submit"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out mt-4">Upload</button>
                                </div>
                            </form>
                        @else
                            @if($clients->Status == 3)
                                <div class="mt-3">
                                    <p class="text-gray-500 mb-4">** No data available **</p>
                                </div>
                                @if($canTransfer)
                                    @if($canTransfer->TransferClientId == null)
                                        <a href="/client-assignplan/{{ $clients->cid }}"
                                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out"
                                            role="button">Assign Now</a>
                                    @endif
                                @else
                                    <a href="/client-assignplan/{{ $clients->cid }}"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out"
                                        role="button">Assign Now</a>
                                @endif
                            @else
                                <p class="mt-2 text-gray-500">** Client needs to be approved to perform this action.</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL APPROVAL FOR CERTIFICATE OF FULL PAYMENT ERROR -->
        <div class="modal fade" id="showApproveCfpErrorInputModal" data-bs-backdrop="static" data-bs-keyboard="false"
            tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content rounded-xl shadow-2xl border-0">
                    <div
                        class="modal-header bg-gradient-to-r from-yellow-500 to-orange-500 text-white border-0 rounded-t-xl">
                        <h5 class="modal-title font-bold flex items-center" id="staticBackdropLabel">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Warning
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <p class="text-gray-700 text-base">Certificate of full payment requires approval.</p>
                    </div>
                    <div class="modal-footer border-0 px-6 pb-6">
                        <button type="button"
                            class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-200"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL APPROVAL FOR CERTIFICATE OF FULL PAYMENT -->
        <div class="modal fade" id="showApproveCfpInputModal" data-bs-backdrop="static" data-bs-keyboard="false"
            tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content rounded-xl shadow-2xl border-0">
                    <div
                        class="modal-header bg-gradient-to-r from-purple-600 to-purple-700 text-white border-0 rounded-t-xl">
                        <h5 class="modal-title font-bold flex items-center" id="staticBackdropLabel">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Certificate Approval
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="space-y-3">
                            <p class="text-gray-700 text-base">You are going to approve the certificate of full payment for
                                this client.</p>
                            <p class="font-bold text-red-600 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                You cannot undo this action. Continue?
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-6 pb-6 gap-3">
                        <button type="button"
                            class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-200"
                            data-bs-dismiss="modal">Close</button>
                        <button type="button"
                            class="px-6 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md transition duration-200"
                            id="cofpApproval">Submit</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CERTIFICATE OF FULL PAYMENT -->
        <div class="modal fade" id="showCfpNoInputModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content rounded-xl shadow-2xl border-0">
                    <div
                        class="modal-header bg-gradient-to-r from-purple-600 to-purple-700 text-white border-0 rounded-t-xl">
                        <h5 class="modal-title font-bold flex items-center" id="staticBackdropLabel">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Certificate of Full Payment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="form-group">
                            <label for="cfpNoInput" class="block text-sm font-medium text-gray-700 mb-2">Certificate
                                Number</label>
                            <input type="text"
                                class="form-control w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                id="cfpNoInput" placeholder="Enter certificate number">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-6 pb-6 gap-3">
                        <button type="button"
                            class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-200"
                            data-bs-dismiss="modal">Close</button>
                        <button type="button"
                            class="px-6 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md transition duration-200"
                            id="downloadCfpWithInput">Submit</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CLIENT STATUS -->
        <div class="modal fade" id="clientStatusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content rounded-xl shadow-2xl border-0">
                    <div class="modal-header bg-gradient-to-r from-blue-600 to-blue-700 text-white border-0 rounded-t-xl">
                        <h5 class="modal-title font-bold flex items-center" id="staticBackdropLabel">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                            Confirmation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="space-y-3">
                            @if($clients->Status == '1')
                                <p class="text-gray-700 text-base">You are going to verify the selected client <span
                                        class="font-semibold text-purple-600" id="clientStatus"></span>.</p>
                            @elseif($clients->Status == '2')
                                <p class="text-gray-700 text-base">You are going to approve the selected client <span
                                        class="font-semibold text-purple-600" id="clientStatus"></span>. Once approved, payment
                                    details cannot be changed.</p>
                            @endif
                            <p class="font-bold text-red-600 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                You cannot undo this action. Continue?
                            </p>
                        </div>
                    </div>
                    <form id="clientStatusForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-footer border-0 px-6 pb-6 gap-3">
                            <button type="button"
                                class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-200"
                                data-bs-dismiss="modal">Close</button>
                            <button type="button"
                                class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-md transition duration-200"
                                id="confirmClientStatus">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL VOID -->
        <div class="modal fade" id="paymentVoidModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content rounded-xl shadow-2xl border-0">
                    <div
                        class="modal-header bg-gradient-to-r from-red-50 to-red-100 border-b-2 border-red-200 text-red-900 rounded-t-xl">
                        <h5 class="modal-title font-bold flex items-center" id="staticBackdropLabel">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Confirmation
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="space-y-3">
                            <p class="text-gray-700 text-base">You are going to void the selected payment with OR No. <span
                                    class="font-semibold text-purple-600" id="paymentToVoid"></span></p>
                            <p class="font-bold text-red-600 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                You cannot undo this action. Continue?
                            </p>
                        </div>
                    </div>
                    <form id="voidForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-footer border-0 px-6 pb-6 gap-3">
                            <button type="button"
                                class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-200"
                                data-bs-dismiss="modal">Close</button>
                            <button type="button"
                                class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-md transition duration-200"
                                id="confirmVoid">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL VOID LOAN -->
        <div class="modal fade" id="loanPaymentVoidModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content rounded-xl shadow-2xl border-0">
                    <div
                        class="modal-header bg-gradient-to-r from-red-50 to-red-100 border-b-2 border-red-200 text-red-900 rounded-t-xl">
                        <h5 class="modal-title font-bold flex items-center" id="staticBackdropLabel">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Confirmation
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="space-y-3">
                            <p class="text-gray-700 text-base">You are going to void the selected payment with OR No. <span
                                    class="font-semibold text-purple-600" id="loanPaymentToVoid"></span></p>
                            <p class="font-bold text-red-600 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                You cannot undo this action. Continue?
                            </p>
                        </div>
                    </div>
                    <form id="voidLoanForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-footer border-0 px-6 pb-6 gap-3">
                            <button type="button"
                                class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-200"
                                data-bs-dismiss="modal">Close</button>
                            <button type="button"
                                class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-md transition duration-200"
                                id="confirmLoanVoid">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL COMPLETE MEMORIAL -->
        <div class="modal fade" id="completeMemorialModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content rounded-xl shadow-2xl border-0">
                    <div class="modal-header bg-gradient-to-r from-green-600 to-green-700 text-white border-0 rounded-t-xl">
                        <h5 class="modal-title font-bold flex items-center" id="staticBackdropLabel">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Confirmation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="space-y-3">
                            <p class="text-gray-700 text-base">You are going to mark the selected client as having completed
                                the memorial service.</p>
                            <p class="font-bold text-red-600 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                You cannot undo this action. Continue?
                            </p>
                        </div>
                    </div>
                    <form action="/submit-complete-memorial/{{ $clients->cid }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-footer border-0 px-6 pb-6 gap-3">
                            <button type="button"
                                class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-200"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit"
                                class="px-6 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md transition duration-200">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <style>
        /* Tab pane visibility control */
        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        /* Payment filter button active states */
        .payment-filter-btn.active {
            background-color: #76df9dff !important;
            /* green-500 */
            border-color: #1aec67ff !important;
            /* green-600 */
            color: #ffffff !important;
            /* white text */
        }

        /* Global pagination styling now handled in app.css */

        /* Void action button styling */
        .action-void {
            color: #dc2626 !important;
            /* red-600 */
            text-decoration: underline;
            cursor: pointer;
        }

        .action-void:hover {
            color: #b91c1c !important;
            /* red-700 */
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            background: #f9fafb !important;
            /* gray-50 */
            border-color: #e5e7eb !important;
            /* gray-200 */
            color: #9ca3af !important;
            /* gray-400 */
        }
    </style>
    <script src="{{ asset('js/client-view.js') }}"></script>
    <script>
        // Custom tab switching (more reliable than Bootstrap)
        document.addEventListener('DOMContentLoaded', function () {
            @if(request('status'))
                localStorage.setItem('clientStatusFilter', '{{ request('status') }}');
            @endif
                                        const tabs = document.querySelectorAll('#clientTabs button[data-bs-toggle="tab"]');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabs.forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Get target pane
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    if (!targetPane) return;

                    // Remove active classes from all tabs
                    tabs.forEach(t => {
                        t.classList.remove('text-purple-600', 'border-b-2', 'border-purple-600', 'font-semibold');
                        t.classList.add('text-gray-500');
                        t.setAttribute('aria-selected', 'false');
                    });

                    // Add active classes to clicked tab
                    this.classList.remove('text-gray-500');
                    this.classList.add('text-purple-600', 'border-b-2', 'border-purple-600', 'font-semibold');
                    this.setAttribute('aria-selected', 'true');

                    // Hide all tab panes
                    tabPanes.forEach(pane => {
                        pane.classList.remove('active');
                    });

                    // Show target pane
                    targetPane.classList.add('active');
                });
            });

            console.log('✅ Custom tabs initialized:', tabs.length, 'tabs found');
        });
    </script>
@endsection