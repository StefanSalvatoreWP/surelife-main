<!-- 2024 SilverDust) S. Maceren -->
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Loan Request</h3>
                @if(!$loanRequest)
                    <button onclick="showLoanRequestModal()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-semibold rounded-md shadow-sm hover:shadow transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Request a Loan
                    </button>
                @else
                    <div
                        class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-500 text-sm font-semibold rounded-md border border-gray-200 cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Request Sent
                    </div>
                @endif
            </div>

            <!-- Eligibility Alert -->
            @if($loanRequest)
                {{-- Loan request exists - show status info --}}
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-3a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-blue-700 font-medium">Loan request submitted at {{ $tier }}% tier. Status: {{ $loanStatus }}</p>
                    </div>
                </div>
            @elseif($isEligible)
                {{-- No loan request, but eligible - show eligibility --}}
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-green-700 font-medium">You are eligible for loan request! ({{ $tier }}% tier)</p>
                    </div>
                </div>
            @else
                {{-- No loan request, not eligible - show reason --}}
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0v-6a1 1 0 112 0v6zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-yellow-700 font-medium">{{ $eligibilityMessage ?: 'You are not yet eligible for loan request.' }}</p>
                    </div>
                </div>
            @endif

        </div>
        <!-- Loan Details Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Loan Amount Card -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium mb-2">Loan Request Amount</p>
                        <p class="text-3xl font-bold text-blue-900">₱ {{ number_format($netLoanAmount, 2) }}</p>
                    </div>
                    <div class="bg-blue-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Monthly Payment Card -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium mb-2">Amount to Pay Every Month</p>
                        <p class="text-3xl font-bold text-purple-900">₱ {{ number_format($monthlyLoanAmount, 2) }}</p>
                    </div>
                    <div class="bg-purple-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Loan Term Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-6 border border-indigo-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-indigo-600 font-medium mb-2">Loan Term</p>
                        <p class="text-3xl font-bold text-indigo-900">{{ $termMonths ?? 12 }} months</p>
                        <p class="text-sm text-indigo-500 mt-1">Interest: 1.25%/month</p>
                    </div>
                    <div class="bg-indigo-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Status Card with Tracker -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm text-green-600 font-medium mb-2">Status</p>
                        @if($loanStatus == 'Pending')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-200 text-gray-700">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                {{ $loanStatus }}
                            </span>
                        @elseif($loanStatus == 'Verified')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-200 text-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ $loanStatus }}
                            </span>
                        @elseif($loanStatus == 'Approved')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-200 text-green-700">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ $loanStatus }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-200 text-gray-700">{{ $loanStatus }}</span>
                        @endif
                    </div>
                    <div class="bg-green-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                @if($loanRequest)
                <!-- Status Tracker -->
                <div class="mt-4 pt-4 border-t border-green-200">
                    <div class="flex items-center justify-between">
                        @php
                            $steps = ['Pending', 'Verified', 'Approved', 'Completed'];
                            $currentStep = array_search($loanStatus, $steps);
                            if ($currentStep === false) $currentStep = 0;
                        @endphp
                        @foreach($steps as $index => $step)
                            <div class="flex flex-col items-center flex-1">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $index <= $currentStep ? 'bg-green-500' : 'bg-gray-300' }}">
                                    @if($index < $currentStep)
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @elseif($index == $currentStep)
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <span class="text-gray-500 font-semibold text-xs">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <span class="mt-1 text-xs font-medium {{ $index <= $currentStep ? 'text-green-600' : 'text-gray-400' }}">{{ $step }}</span>
                            </div>
                            @if($index < count($steps) - 1)
                                <div class="flex-1 h-0.5 mx-1 {{ $index < $currentStep ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Balance Card -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-6 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-orange-600 font-medium mb-2">Remaining Balance</p>
                        <p class="text-3xl font-bold text-orange-900">₱ {{ number_format($loanBalance, 2) }}</p>
                    </div>
                    <div class="bg-orange-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Interest Info Card -->
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-6 border border-amber-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-amber-600 font-medium mb-2">Interest Information</p>
                        <p class="text-xl font-bold text-amber-900">1.25% per month</p>
                        <p class="text-sm text-amber-600 mt-1">Total: ₱ {{ number_format(($loanableAmount ?? 0) * 0.0125 * ($termMonths ?? 12), 2) }}</p>
                    </div>
                    <div class="bg-amber-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LOAN APPLICATION MODAL -->
    <div id="loanApplicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-0 border w-full max-w-2xl shadow-xl rounded-lg bg-white">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <h3 class="text-xl font-bold text-gray-900">Loan Application</h3>
                <button onclick="closeLoanModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 bg-gradient-to-b from-slate-50 to-white">

                <form id="loanApplicationForm" method="POST" action="/submit-client-loanrequest/{{ session('user_id') }}">
                    @csrf
                    <input type="hidden" name="waiver_signed" id="waiverSigned" value="0">
                    <input type="hidden" name="signature_data" id="signatureData" value="">

                    <!-- Loan Details + Monthly Payment Breakdown (Horizontal on Desktop) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Loan Details Card -->
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 shadow-lg">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="bg-white/20 rounded-full p-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-bold text-white tracking-wide">Loan Details</h4>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Loanable Amount</p>
                                    <p class="text-xl font-bold text-blue-600">₱ {{ number_format($loanableAmount ?? 0, 2) }}</p>
                                </div>
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Processing Fee</p>
                                    <p class="text-xl font-bold text-rose-600">₱ {{ number_format($processingFee ?? 0, 2) }}</p>
                                </div>
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Net Amount</p>
                                    <p class="text-xl font-bold text-emerald-600">₱ {{ number_format($netLoanAmount ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Payment Card -->
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-6 shadow-lg">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="bg-white/20 rounded-full p-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-bold text-white tracking-wide">Monthly Payment Breakdown</h4>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Loan Payment</p>
                                    <p class="text-xl font-bold text-purple-600" id="monthlyLoanPayment">₱ 0.00</p>
                                </div>
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Contract Premium</p>
                                    <p class="text-xl font-bold text-gray-700">₱ {{ number_format($monthlyContractPremium ?? 0, 2) }}</p>
                                </div>
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Total Monthly Due</p>
                                    <p class="text-xl font-bold text-amber-600" id="totalMonthlyDue">₱ 0.00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Select Loan Term (Above Waiver) -->
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 mb-6 shadow-lg">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-white/20 rounded-full p-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white tracking-wide">Select Loan Term</h4>
                                    <p class="text-sm text-white/80">Interest rate: 1.25% per month</p>
                                </div>
                            </div>
                            <div class="lg:w-64">
                                <select name="term_months" id="termMonths" required
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-gray-800 font-semibold text-lg focus:outline-none focus:ring-2 focus:ring-amber-400"
                                    onchange="calculateMonthlyPayment()">
                                    <option value="">-- Select Term --</option>
                                    <option value="2">2 months</option>
                                    <option value="3">3 months</option>
                                    <option value="6">6 months</option>
                                    <option value="9">9 months</option>
                                    <option value="12" selected>12 months</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Waiver of Rights -->
                    <div class="border border-slate-200 rounded-xl p-6 pb-7 mb-6 bg-white shadow-sm">
                        <h4 class="font-semibold text-slate-900 mb-4 text-center text-lg uppercase tracking-widest">Waiver of Rights</h4>

                        <div class="bg-gradient-to-br from-sky-50 via-white to-indigo-50/40 p-4 rounded-lg mb-4 text-sm leading-relaxed border border-sky-100">

                            <p class="mb-3">
                                I <span id="waiverApplicantNameBlank" class="inline-block border-b border-gray-400 min-w-[140px] text-center font-semibold">{{ ($client->firstname ?? '') . ' ' . ($client->lastname ?? '') }}</span> member of sure life care &amp; services with Contract Number <span id="waiverContractNumberBlank" class="inline-block border-b border-gray-400 min-w-[110px] text-center font-semibold">{{ $client->contractnumber ?? '' }}</span> applied for a loan in my Contract.
                            </p>
                            <p class="mb-12">
                                I understand that after applying for a loan , I waive my right of any benefits and privileges stated in the Contract as a member . In Case of loss of life, I also agreed that I have to pay the remaining balance of my loan to be rendered service.
                            </p>

                            <!-- Applicant's Full name & signature - Fixed alignment -->
                            <div class="mt-8 mb-8">
                                <div class="flex justify-between items-end gap-12">
                                    <div class="text-center" style="min-width: 180px;">
                                        <div class="relative" style="height: 20px;">
                                            <p class="font-bold text-gray-900 absolute bottom-0 w-full text-center leading-none mb-0">{{ strtoupper(date('F d, Y')) }}</p>
                                        </div>
                                        <div class="border-b-2 border-gray-500 pb-1"></div>
                                        <p class="text-xs text-gray-500 mt-1 text-center">DATE</p>
                                    </div>
                                    <div class="text-center" style="max-width: 250px;">
                                        <div class="relative" style="height: 20px;">
                                            <p id="waiverPrintedName" class="font-bold text-gray-900 absolute bottom-0 w-full text-center leading-none mb-0 whitespace-nowrap overflow-hidden text-ellipsis text-[clamp(0.65rem,1.25vw,1rem)]">{{ ($client->firstname ?? '') . ' ' . ($client->lastname ?? '') }}</p>
                                            <img id="waiverSignatureOverPrinted" class="hidden absolute bottom-0 z-10 pointer-events-none" style="left: 50%; transform: translate(-50%, -35px); max-height: 80px;" alt="">
                                        </div>
                                        <div class="border-b-2 border-gray-500 pb-1"></div>
                                        <p class="text-xs text-gray-500 mt-1 text-left bg-white px-1 inline-block">Applicant's Full name & signature:</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Signature Pad Trigger -->
                        <div class="mt-6 mb-4 relative z-20">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Digital Signature <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">Click to open the signature pad and sign:</p>
                            <div class="flex items-center gap-3">
                                <button type="button" id="openSignaturePadBtn" onclick="showSignaturePad()"
                                    class="px-4 py-2 bg-blue-600 text-white border border-blue-600 rounded-md text-sm font-semibold hover:bg-blue-700 hover:border-blue-700 transition-colors shadow-sm">
                                    Open Signature Pad
                                </button>

                                <span id="signatureStatus" class="text-xs text-gray-500">Not signed</span>
                            </div>
                        </div>

                        <!-- Signature Pad Modal -->
                        <div id="signaturePadModal" class="hidden fixed inset-0 z-[9999] bg-black/50">
                            <div class="min-h-screen flex items-center justify-center p-4">
                                <div class="bg-white rounded-lg w-full max-w-2xl shadow-lg">
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900">Signature Pad</h3>
                                        <button type="button" onclick="closeSignaturePad()" class="text-gray-500 hover:text-gray-700">✕</button>
                                    </div>
                                    <div class="p-6">
                                        <div id="signatureModalSurface" class="border border-gray-200 rounded-md bg-white overflow-hidden touch-none">
                                            <canvas id="signatureModalCanvas" class="w-full h-[150px] cursor-crosshair block"></canvas>
                                        </div>

                                        <div class="flex items-center justify-between mt-4">
                                            <button type="button" onclick="clearSignature()"
                                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-sm rounded transition-colors">
                                                Clear
                                            </button>
                                            <div class="flex items-center gap-3">
                                                <button type="button" onclick="closeSignaturePad()"
                                                    class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-sm rounded transition-colors">
                                                    Cancel
                                                </button>
                                                <button type="button" onclick="confirmSignature()"
                                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition-colors">
                                                    Confirm Signature
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields for JavaScript auto-fill -->
                        <input type="hidden" id="applicantFullNameInput" value="{{ ($client->firstname ?? $client->FirstName ?? '') . ' ' . ($client->lastname ?? $client->LastName ?? '') }}">
                        <input type="hidden" id="contractNumberInput" value="{{ $client->contractnumber ?? $client->ContractNumber ?? '' }}">

                        <!-- Agreement Checkbox -->
                        <div class="flex items-start mt-6 px-1">
                            <input type="checkbox" id="agreeWaiver" required 
                                class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                            <label for="agreeWaiver" class="text-sm text-gray-700 cursor-pointer select-none">
                                I have read and agree to the Waiver of Rights stated above <span class="text-red-500">*</span>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeLoanModal()" 
                            class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-md transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="submitLoanBtn" disabled
                            class="px-6 py-2.5 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-md transition-colors shadow-sm">
                            Submit Loan Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Loan calculation variables
        const loanableAmount = {{ $loanableAmount ?? 0 }};
        const monthlyContractPremium = {{ $monthlyContractPremium ?? 0 }};
        const interestRate = 0.0125; // 1.25%
        let hasSignature = false;

        function showLoanRequestModal() {
            document.getElementById('loanApplicationModal').classList.remove('hidden');
            calculateMonthlyPayment();
            updateWaiverFields(); // Auto-fill waiver fields when modal opens
        }

        function closeLoanModal() {
            document.getElementById('loanApplicationModal').classList.add('hidden');
        }

        function calculateMonthlyPayment() {
            const termMonths = parseInt(document.getElementById('termMonths').value) || 12;
            
            // Calculate interest: principal × 1.25% × termMonths
            const totalInterest = loanableAmount * interestRate * termMonths;
            const totalRepayable = loanableAmount + totalInterest;
            const monthlyLoanPayment = totalRepayable / termMonths;
            const totalMonthlyDue = monthlyLoanPayment + monthlyContractPremium;
            
            document.getElementById('monthlyLoanPayment').textContent = '₱ ' + monthlyLoanPayment.toFixed(2);
            document.getElementById('totalMonthlyDue').textContent = '₱ ' + totalMonthlyDue.toFixed(2);
        }

        // Signature Canvas
        let signatureCanvas, ctx, isDrawing = false;

        function initSignatureCanvas() {
            signatureCanvas = document.getElementById('signatureModalCanvas');
            ctx = signatureCanvas.getContext('2d');

            const surface = document.getElementById('signatureModalSurface');
            const rect = surface.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            const cssWidth = Math.max(600, Math.floor(rect.width));
            const cssHeight = Math.max(150, Math.floor(rect.height));

            signatureCanvas.width = cssWidth * dpr;
            signatureCanvas.height = cssHeight * dpr;
            signatureCanvas.style.width = cssWidth + 'px';
            signatureCanvas.style.height = cssHeight + 'px';

            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(dpr, dpr);
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            signatureCanvas.addEventListener('mousedown', startDrawing);
            signatureCanvas.addEventListener('mousemove', draw);
            signatureCanvas.addEventListener('mouseup', stopDrawing);
            signatureCanvas.addEventListener('mouseout', stopDrawing);
            signatureCanvas.addEventListener('touchstart', handleTouch);
            signatureCanvas.addEventListener('touchmove', handleTouch);
            signatureCanvas.addEventListener('touchend', stopDrawing);
        }

        function showSignaturePad() {
            document.getElementById('signaturePadModal').classList.remove('hidden');
            const openBtn = document.getElementById('openSignaturePadBtn');
            if (openBtn) {
                openBtn.textContent = hasSignature ? 'Edit Signature' : 'Open Signature Pad';
            }
            initSignatureCanvas();
        }

        function closeSignaturePad() {
            document.getElementById('signaturePadModal').classList.add('hidden');
        }

        function getPos(e) {
            const rect = signatureCanvas.getBoundingClientRect();
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            hasSignature = true;
            updateSubmitButton();
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function confirmSignature() {
            if (!hasSignature) {
                return;
            }

            const dataUrl = signatureCanvas.toDataURL('image/png');
            document.getElementById('signatureData').value = dataUrl;

            const preview = document.getElementById('waiverSignatureOverPrinted');
            if (preview) {
                preview.src = dataUrl;
                preview.classList.remove('hidden');
            }

            const status = document.getElementById('signatureStatus');
            if (status) {
                status.textContent = 'Signed';
            }

            closeSignaturePad();
            updateSubmitButton();
        }

        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 'mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            signatureCanvas.dispatchEvent(mouseEvent);
        }

        function clearSignature() {
            ctx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
            hasSignature = false;
            const preview = document.getElementById('waiverSignatureOverPrinted');
            if (preview) {
                preview.src = '';
                preview.classList.add('hidden');
            }
            const status = document.getElementById('signatureStatus');
            if (status) {
                status.textContent = 'Not signed';
            }
            document.getElementById('signatureData').value = '';
            updateSubmitButton();
        }

        function updateWaiverFields() {
            const fullNameInput = document.getElementById('applicantFullNameInput');
            const contractInput = document.getElementById('contractNumberInput');
            const fullName = (fullNameInput && fullNameInput.value ? fullNameInput.value.trim() : '') || '';
            const contractNumber = (contractInput && contractInput.value ? contractInput.value.trim() : '') || '';

            const nameBlank = document.getElementById('waiverApplicantNameBlank');
            if (nameBlank) {
                nameBlank.textContent = fullName ? (fullName + ' ') : ' ';
            }

            const contractBlank = document.getElementById('waiverContractNumberBlank');
            if (contractBlank) {
                contractBlank.textContent = contractNumber ? (contractNumber + ' ') : ' ';
            }

            const printedName = document.getElementById('waiverPrintedName');
            if (printedName) {
                printedName.textContent = fullName;
            }
        }

        function updateSubmitButton() {
            const agreed = document.getElementById('agreeWaiver').checked;
            const termSelected = document.getElementById('termMonths').value !== '';
            const submitBtn = document.getElementById('submitLoanBtn');
            
            submitBtn.disabled = !(hasSignature && agreed && termSelected);
        }

        // Form submission
        document.getElementById('loanApplicationForm').addEventListener('submit', function(e) {
            if (!hasSignature) {
                e.preventDefault();
                alert('Please sign the waiver form.');
                return false;
            }
            
            // Signature is saved on confirmSignature()
            document.getElementById('waiverSigned').value = '1';
            
            return true;
        });

        // Agreement checkbox listener
        document.getElementById('agreeWaiver').addEventListener('change', updateSubmitButton);
        document.getElementById('termMonths').addEventListener('change', updateSubmitButton);

        document.getElementById('applicantFullNameInput').addEventListener('input', updateWaiverFields);
        document.getElementById('contractNumberInput').addEventListener('input', updateWaiverFields);

        // Close modal on outside click
        document.getElementById('loanApplicationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoanModal();
            }
        });

        // Show success/error modal on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateWaiverFields();
            @if(session('success'))
                showSwiftModal('Success!', '{{ session('success') }}', 'success', [
                    {text: 'OK', class: 'bg-green-500 hover:bg-green-600 text-white'}
                ]);
            @endif

            @if(session('error'))
                showSwiftModal('Error', '{{ session('error') }}', 'error', [
                    {text: 'OK', class: 'bg-red-500 hover:bg-red-600 text-white'}
                ]);
            @endif
        });
    </script>
@endsection
