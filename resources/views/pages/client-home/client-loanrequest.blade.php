<!-- 2024 SilverDust) S. Maceren -->
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header Card -->
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Loan Request</h3>
                @if(!$loanRequest)
                    {{-- No existing loan - show request button --}}
                    <button onclick="showLoanRequestModal()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-semibold rounded-md shadow-sm hover:shadow transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Request a Loan
                    </button>
                @elseif($loanRequest && $loanRequest->Status == 'Completed' && $loanBalance <= 0)
                    {{-- Loan fully paid - can request new loan --}}
                    <button onclick="showLoanRequestModal()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-semibold rounded-md shadow-sm hover:shadow transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Request a New Loan
                    </button>
                @else
                    {{-- Active loan in progress --}}
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

</div>
        <!-- Loan Details Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Tier Qualified Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-6 border border-indigo-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-indigo-600 font-medium mb-2">Tier Qualified</p>
                        <p class="text-3xl font-bold text-indigo-900">{{ $tier ?? 0 }}%</p>
                        @php
                            $tierNames = [60 => 'Bronze', 80 => 'Silver', 100 => 'Gold'];
                            $tierColors = [60 => 'amber', 80 => 'gray', 100 => 'yellow'];
                            $tierName = $tierNames[$tier] ?? 'Not Qualified';
                            $tierColor = $tierColors[$tier] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-{{ $tierColor }}-100 text-{{ $tierColor }}-800 mt-2">
                            {{ $tierName }}
                        </span>
                    </div>
                    <div class="bg-indigo-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Loanable Amount Card -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium mb-2">Loanable Amount</p>
                        <p class="text-3xl font-bold text-blue-900">₱ {{ number_format($loanableAmount, 2) }}</p>
                    </div>
                    <div class="bg-blue-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Net Amount Card -->
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-6 border border-emerald-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-emerald-600 font-medium mb-2">Net Amount</p>
                        <p class="text-3xl font-bold text-emerald-900">₱ {{ number_format($netLoanAmount, 2) }}</p>
                        <p class="text-xs text-emerald-500 mt-1">After 10% fee</p>
                    </div>
                    <div class="bg-emerald-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Monthly Payment Card -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium mb-2">Monthly Due</p>
                        <p class="text-3xl font-bold text-purple-900">₱ {{ number_format($monthlyLoanAmount, 2) }}</p>
                        <p class="text-xs text-purple-500 mt-1">1.25% interest/mo</p>
                    </div>
                    <div class="bg-purple-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interest Info & Status Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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

            <!-- Loan Term Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-6 border border-indigo-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-indigo-600 font-medium mb-2">Loan Term</p>
                        <p class="text-3xl font-bold text-indigo-900">{{ $termMonths ?? 12 }} months</p>
                        <p class="text-xs text-indigo-500 mt-1">Interest: 1.25%/month</p>
                    </div>
                    <div class="bg-indigo-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Processing Fee & Remaining Balance Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Processing Fee Card -->
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-6 border border-amber-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-amber-600 font-medium mb-2">Processing Fee (10%)</p>
                        <p class="text-3xl font-bold text-amber-900">₱ {{ number_format($processingFee, 2) }}</p>
                        <p class="text-xs text-amber-500 mt-1">Deducted from loanable amount</p>
                    </div>
                    <div class="bg-amber-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Repayable Card -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-6 border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 font-medium mb-2">Total Repayable</p>
                        <p class="text-3xl font-bold text-red-900">₱ {{ number_format($loanableAmount + ($loanableAmount * 0.0125 * ($termMonths ?? 12)), 2) }}</p>
                        <p class="text-xs text-red-500 mt-1">Principal + Interest</p>
                    </div>
                    <div class="bg-red-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status & Remaining Balance Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium mb-2">Status</p>
                        @if(!$loanRequest)
                            <p class="text-gray-500 text-sm">No loan request yet</p>
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

            <!-- Remaining Balance Card -->
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
                @if($loanRequest)
                <!-- Payment Progress -->
                <div class="mt-4 pt-4 border-t border-orange-200">
                    @php
                        // Use actual total_repayable from loan request (not recalculated)
                        $actualTotalRepayable = $loanRequest->total_repayable ?? $loanRequest->TotalRepayable ?? ($loanableAmount + ($loanableAmount * 0.0125 * ($termMonths ?? 12)));
                        $paidAmount = $actualTotalRepayable - $loanBalance;
                        $progressPercent = $actualTotalRepayable > 0 ? round(($paidAmount / $actualTotalRepayable) * 100) : 0;
                        // Cap progress at 100% to avoid display issues
                        if ($progressPercent > 100) $progressPercent = 100;
                        if ($progressPercent < 0) $progressPercent = 0;
                    @endphp
                    <div class="flex justify-between text-xs text-orange-600 mb-1">
                        <span>Payment Progress</span>
                        <span>{{ $progressPercent }}%</span>
                    </div>
                    <div class="w-full bg-orange-200 rounded-full h-2">
                        <div class="bg-orange-500 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>₱ {{ number_format(max(0, $paidAmount), 2) }} paid</span>
                        <span>₱ {{ number_format($actualTotalRepayable, 2) }} total</span>
                    </div>
                    @if($loanPayments && $loanPayments->count() > 0)
                    <button type="button" onclick="showPaymentHistoryModal()" class="mt-3 w-full flex items-center justify-center gap-2 px-3 py-2 bg-white/60 hover:bg-white/80 border border-orange-200 rounded-lg text-sm text-orange-700 font-medium transition-all duration-200 hover:shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        View Payment History ({{ $loanPayments->count() }})
                    </button>
                    @endif
                </div>
                @else
                <div class="mt-4 pt-4 border-t border-orange-200">
                    <p class="text-gray-500 text-sm">No outstanding balance</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- PAYMENT HISTORY MODAL -->
    <div id="paymentHistoryModal" class="fixed inset-0 hidden overflow-y-auto h-full w-full z-50" style="background-color: rgba(0, 0, 0, 0.6);">
        <div class="relative top-20 mx-auto p-0 border w-full max-w-md rounded-xl bg-white shadow-2xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-500 to-orange-600 rounded-t-xl">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 rounded-full p-2">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Loan Payment History</h3>
                </div>
                <button onclick="closePaymentHistoryModal()" class="text-white/70 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-4">
                @if($loanPayments && $loanPayments->count() > 0)
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($loanPayments as $payment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ ($payment->PaymentDate ?? $payment->DateCreated) ? \Carbon\Carbon::parse($payment->PaymentDate ?? $payment->DateCreated)->format('M d, Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-orange-600">
                                    ₱ {{ number_format($payment->Amount ?? 0, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-orange-50">
                            <tr>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-700">Total Paid</td>
                                <td class="px-4 py-3 text-sm text-right font-bold text-orange-600">
                                    ₱ {{ number_format($loanPayments->sum('Amount'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500">No payment records found</p>
                </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                <button onclick="closePaymentHistoryModal()" class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- LOAN APPLICATION MODAL -->
    <div id="loanApplicationModal" class="fixed inset-0 hidden overflow-y-auto h-full w-full z-50" style="background-color: rgba(0, 0, 0, 0.85);">
        <div class="relative top-20 mx-auto p-0 border w-full max-w-2xl rounded-lg bg-white" style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.9);">
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
            <div class="p-6 bg-gradient-to-b from-slate-200 to-slate-300">

                <form id="loanApplicationForm" method="POST" action="/submit-client-loanrequest/{{ session('user_id') }}">
                    @csrf
                    <input type="hidden" name="waiver_signed" id="waiverSigned" value="0">
                    <input type="hidden" name="signature_data" id="signatureData" value="">

                    <!-- Loan Details + Monthly Payment Breakdown (Vertical Stack) -->
                    <div class="grid grid-cols-1 gap-6 mb-6">
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
                            <!-- Row 1: Tier, Loanable -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Tier Qualified</p>
                                    <p class="text-xl font-bold text-indigo-600">
                                        @php
                                            $tierNames = [60 => 'Bronze', 80 => 'Silver', 100 => 'Gold'];
                                            echo ($tierNames[$tier] ?? 'Not Qualified') . ' (' . ($tier ?? 0) . '%)';
                                        @endphp
                                    </p>
                                </div>
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Loanable Amount</p>
                                    <p class="text-xl font-bold text-blue-600">₱ {{ number_format($loanableAmount ?? 0, 2) }}</p>
                                </div>
                            </div>
                            <!-- Row 2: Processing Fee, Net Amount, Total Repayable -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Processing Fee</p>
                                    <p class="text-xl font-bold text-rose-600">₱ {{ number_format($processingFee ?? 0, 2) }}</p>
                                </div>
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Net Amount</p>
                                    <p class="text-xl font-bold text-emerald-600">₱ {{ number_format($netLoanAmount ?? 0, 2) }}</p>
                                </div>
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Total Repayable</p>
                                    <p class="text-xl font-bold text-amber-600" id="totalRepayable">₱ 0.00</p>
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
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Interest/Mo</p>
                                    <p class="text-xl font-bold text-rose-600" id="monthlyInterest">₱ 0.00</p>
                                </div>
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Principal/Mo</p>
                                    <p class="text-xl font-bold text-emerald-600" id="monthlyPrincipal">₱ 0.00</p>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Total Interest</p>
                                    <p class="text-xl font-bold text-rose-600" id="totalInterest">₱ 0.00</p>
                                </div>
                                <div class="bg-white rounded-xl p-4 shadow">
                                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Total Monthly Due</p>
                                    <p class="text-2xl font-bold text-amber-600" id="totalMonthlyDue">₱ 0.00</p>
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
                                    <option value="4">4 months</option>
                                    <option value="5">5 months</option>
                                    <option value="6">6 months</option>
                                    <option value="7">7 months</option>
                                    <option value="8">8 months</option>
                                    <option value="9">9 months</option>
                                    <option value="10">10 months</option>
                                    <option value="11">11 months</option>
                                    <option value="12" selected>12 months</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Waiver of Rights -->
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-6 mb-6 border border-slate-200 shadow-sm">
                        <h4 class="font-semibold text-slate-900 mb-4 text-center text-lg uppercase tracking-widest">Waiver of Rights</h4>

                        <div class="bg-white p-4 rounded-lg mb-4 text-sm leading-relaxed border border-slate-100">

                            <p class="mb-3">
                                I <span id="waiverApplicantNameBlank" class="inline-block border-b border-gray-400 min-w-[140px] text-center font-semibold">{{ ($client->firstname ?? '') . ' ' . ($client->lastname ?? '') }}</span> member of sure life care &amp; services with Contract Number <span id="waiverContractNumberBlank" class="inline-block border-b border-gray-400 min-w-[110px] text-center font-semibold">{{ $client->contractnumber ?? '' }}</span> applied for a loan in my Contract.
                            </p>
                            <p class="mb-12">
                                I understand that after applying for a loan , I waive my right of any benefits and privileges stated in the Contract as a member . In Case of loss of life, I also agreed that I have to pay the remaining balance of my loan to be rendered service.
                            </p>

                            <!-- Applicant's Full name & signature - Fixed alignment -->
                            <div class="mt-8 mb-8">
                                <div class="flex justify-between items-end gap-12">
                                    <div class="text-center" style="width: 150px;">
                                        <div class="relative" style="height: 20px;">
                                            <p class="font-bold text-gray-900 absolute bottom-0 w-full text-center leading-none mb-0">{{ strtoupper(date('F d, Y')) }}</p>
                                        </div>
                                        <div class="border-b-2 border-gray-500 pb-1"></div>
                                        <p class="text-xs text-gray-500 mt-1 text-center">DATE</p>
                                    </div>
                                    <div class="text-center" style="width: 150px;">
                                        <div class="relative" style="min-height: 20px;">
                                            <p id="waiverPrintedName" class="font-bold text-gray-900 text-center leading-tight mb-0 break-words">{{ ($client->firstname ?? '') . ' ' . ($client->lastname ?? '') }}</p>
                                            <img id="waiverSignatureOverPrinted" class="hidden absolute bottom-0 z-10 pointer-events-none" style="left: 50%; transform: translate(-50%, 20px); max-height: 80px;" alt="">
                                        </div>
                                        <div class="border-b-2 border-gray-500 pb-1"></div>
                                        <p class="text-xs text-gray-500 mt-1 text-center leading-tight">Applicant's Full name &<br>signature:</p>
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
                                            <canvas id="signatureModalCanvas" class="w-full h-[300px] cursor-crosshair block"></canvas>
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
            const monthlyInterest = totalInterest / termMonths;
            const monthlyPrincipal = monthlyLoanPayment - monthlyInterest;
            
            // Update Loan Details
            document.getElementById('totalRepayable').textContent = '₱ ' + totalRepayable.toFixed(2);
            
            // Update Monthly Payment Breakdown (loan-only)
            document.getElementById('monthlyInterest').textContent = '₱ ' + monthlyInterest.toFixed(2);
            document.getElementById('monthlyPrincipal').textContent = '₱ ' + monthlyPrincipal.toFixed(2);
            document.getElementById('totalInterest').textContent = '₱ ' + totalInterest.toFixed(2);
            document.getElementById('totalMonthlyDue').textContent = '₱ ' + monthlyLoanPayment.toFixed(2);
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

        // Payment History Modal Functions
        function showPaymentHistoryModal() {
            document.getElementById('paymentHistoryModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePaymentHistoryModal() {
            document.getElementById('paymentHistoryModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close payment history modal on outside click
        document.getElementById('paymentHistoryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentHistoryModal();
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
