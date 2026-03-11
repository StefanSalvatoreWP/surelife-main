<!-- 2023 SilverDust) S. Maceren -->
@extends('layouts.main')

@section('content')
    <div class="max-w-4xl mx-auto p-6">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            New Loan Payment
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $clients->LastName . ', ' . $clients->FirstName }}</p>
                    </div>
                    <a href="/client-view/{{ $clients->Id }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>

        @if(session('duplicate'))
            @push('scripts')
                <script>
                    showSwiftModal('Error', '{{ session('duplicate') }}', 'error');
                </script>
            @endpush
        @endif

        @if(session('success'))
            @push('scripts')
                <script>
                    showSwiftModal('Success', '{{ session('success') }}', 'success');
                </script>
            @endpush
        @endif

        <!-- Loan Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium mb-1">Total Loan Amount</p>
                        <p class="text-2xl font-bold text-purple-900">₱ {{ number_format($loanRequestData->Amount, 2) }}</p>
                    </div>
                    <div class="bg-purple-200 rounded-full p-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-red-100 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-orange-600 font-medium mb-1">Remaining Balance</p>
                        <p class="text-2xl font-bold text-orange-900">₱ {{ number_format($loanBalance, 2) }}</p>
                    </div>
                    <div class="bg-orange-200 rounded-full p-3">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                <p class="text-sm text-blue-600 font-medium mb-1">Monthly Payment</p>
                <p class="text-xl font-bold text-blue-900">₱ {{ number_format($loanRequestData->MonthlyAmount, 2) }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                <p class="text-sm text-green-600 font-medium mb-1">Interest Rate</p>
                <p class="text-xl font-bold text-green-900">{{ $loanRequestData->InterestRate ?? 1.25 }}%/mo</p>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-4 border border-amber-200">
                <p class="text-sm text-amber-600 font-medium mb-1">Term Remaining</p>
                <p class="text-xl font-bold text-amber-900">{{ $loanRequestData->term_months ?? $loanRequestData->TermMonths ?? 12 }} months</p>
            </div>
        </div>

        <!-- Advance Payment Preview (Dynamic) -->
        <div id="advancePaymentPreview" class="hidden bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-200 mb-6">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-semibold text-indigo-700">Advance Payment Preview</span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-indigo-600">Months Covered:</p>
                    <p class="font-bold text-indigo-900" id="monthsCovered">-</p>
                </div>
                <div>
                    <p class="text-indigo-600">Balance After Payment:</p>
                    <p class="font-bold text-indigo-900" id="balanceAfterPayment">-</p>
                </div>
            </div>
            <!-- Excess Payment Info -->
            <div id="excessInfo" class="hidden mt-3 pt-3 border-t border-indigo-200">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-purple-600">Excess Amount:</p>
                        <p class="font-bold text-purple-900" id="excessPayment">-</p>
                    </div>
                    <div>
                        <p class="text-green-600">Next Month Minimum:</p>
                        <p class="font-bold text-green-900" id="nextMonthMin">-</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2 italic">Excess payment reduces your next month's minimum due.</p>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h4 class="font-semibold text-gray-700">Payment Details</h4>
            </div>
            <form action="/client-submit-loanpayment/{{ $clients->Id }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="clientbranch" value="{{ $clients->BranchId }}" />
                <input type="hidden" name="clientregion" value="{{ $clients->RegionId }}" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Payment Amount -->
                    <div class="relative">
                        <label for="paymentAmount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                        @php $minPayableAmount = min($loanMonthlyAmount, $loanBalance); @endphp
                        <p class="text-xs text-gray-500 mb-1 h-4">Minimum: ₱ {{ number_format($minPayableAmount, 2) }} | Max: ₱ {{ number_format($loanBalance, 2) }}</p>
                        <input type="number" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition no-spinner" 
                               id="paymentAmount" 
                               name="paymentamount" 
                               min="{{ $minPayableAmount }}" 
                               max="{{ $loanBalance }}" 
                               step="0.01"
                               value="{{ old('paymentamount') ?: $minPayableAmount }}" 
                               placeholder="Enter payment amount"
                               autocomplete="off"
                               required />
                        <div id="paymentSuggestions" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto">
                            @foreach($amounts as $amount)
                                <div class="px-4 py-2 hover:bg-purple-50 cursor-pointer text-sm" data-value="{{ $amount }}">₱ {{ number_format($amount, 2) }}</div>
                            @endforeach
                        </div>
                        <p id="paymentAmountError" class="text-red-500 text-sm mt-1 hidden">Amount must be at least ₱ {{ number_format($loanMonthlyAmount, 2) }}</p>
                        @error('paymentamount')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <p class="text-xs text-gray-500 mb-1 h-4">Select payment type</p>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" id="paymentMethod" name="paymentmethod">
                            @php $selectedPaymentMethod = old('paymentmethod'); @endphp
                            <option value="Cash" {{ $selectedPaymentMethod === 'Cash' ? 'selected' : '' }}>Cash</option>
                        </select>
                        @error('paymentmethod')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- O.R Series Code (Optional) -->
                    <div>
                        <label for="orSeriesCode" class="block text-sm font-medium text-gray-700 mb-2">O.R Series Code <span class="text-gray-400 text-xs">(Optional)</span></label>
                        @if($lastOrSeriesCode)
                            <p class="text-xs text-blue-600 mb-1">Last used: <strong>{{ $lastOrSeriesCode }}</strong></p>
                        @else
                            <p class="text-xs text-gray-500 mb-1">Leave empty if no O.R needed</p>
                        @endif
                        <div class="relative">
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" id="orSeriesCode" name="orseriescode" maxlength="30" value="{{ old('orseriescode') }}" placeholder="Optional - Leave empty if no O.R" autocomplete="off" />
                            <div id="orSeriesCodeDropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto">
                                <!-- Dropdown options will be populated here -->
                            </div>
                        </div>
                        <div id="orSeriesCodeLoading" class="hidden text-sm text-gray-500 mt-1">Loading O.R series codes...</div>
                        <div id="orSeriesCodeError" class="hidden text-sm text-red-600 mt-1"></div>
                        @error('orseriescode')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- O.R No. (Optional) -->
                    <div>
                        <label for="orNo" class="block text-sm font-medium text-gray-700 mb-2">O.R No. <span class="text-gray-400 text-xs">(Optional)</span></label>
                        <p class="text-xs text-gray-500 mb-1">Leave empty if no O.R needed</p>
                        <div class="relative">
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" id="orNo" name="orno" maxlength="30" value="{{ old('orno') }}" placeholder="Optional - Leave empty if no O.R" autocomplete="off" />
                            <div id="orNoDropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto">
                                <!-- Dropdown options will be populated here -->
                            </div>
                        </div>
                        <div id="orNoLoading" class="hidden text-sm text-gray-500 mt-1">Loading O.R numbers...</div>
                        <div id="orNoError" class="hidden text-sm text-red-600 mt-1"></div>
                        @error('orno')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Payment Date -->
                    <div class="md:col-span-2">
                        <label for="paymentDate" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                        <input type="date" class="w-full md:w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" id="paymentDate" name="paymentdate" value="{{ old('paymentdate') ?: date('Y-m-d') }}" />
                        @error('paymentdate')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center pt-4 border-t border-gray-200">
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Submit Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Pass PHP variables to JavaScript
        window.loanPaymentConfig = {
            monthlyPayment: {{ $loanRequestData->MonthlyAmount ?? 0 }},
            remainingBalance: {{ $loanBalance ?? 0 }},
            totalRepayable: {{ $loanRequestData->total_repayable ?? $loanRequestData->TotalRepayable ?? $loanRequestData->Amount ?? 0 }},
            totalPaid: {{ $totalLoanPayments ?? 0 }}
        };
    </script>
    <script src="{{ asset('js/client-addloanpayment.js') }}"></script>
@endsection
@push('styles')
<style>
    /* Hide number input spinners */
    .no-spinner::-webkit-inner-spin-button,
    .no-spinner::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .no-spinner {
        -moz-appearance: textfield;
    }
</style>
@endpush
@push('scripts')
<script>
$(document).ready(function() {
    const $input = $('#paymentAmount');
    const $dropdown = $('#paymentSuggestions');
    
    // Show suggestions on focus
    $input.on('focus', function() {
        $dropdown.removeClass('hidden');
    });
    
    // Hide on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#paymentAmount, #paymentSuggestions').length) {
            $dropdown.addClass('hidden');
        }
    });
    
    // Select suggestion
    $dropdown.on('click', 'div', function() {
        $input.val($(this).data('value'));
        $dropdown.addClass('hidden');
        $input.trigger('input');
    });
});
</script>
@endpush