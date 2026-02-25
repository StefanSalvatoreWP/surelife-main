<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-200 p-6 mb-6">
            <h1 class="text-3xl font-bold text-green-800 mb-2">New Payment</h1>
            <p class="text-green-600 text-sm">{{ $clients->LastName . ', ' . $clients->FirstName }}</p>
            <p class="text-green-600 text-xs mt-1">Create a new standard or partial payment for the selected client.</p>
        </div>

        <!-- Alert Messages -->
        @if(session('duplicate'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <p class="text-red-700 font-medium">{{ session('duplicate') }}</p>
            </div>
        @endif

        <!-- Return Button -->
        <div class="mb-6">
            <a href="/client-view/{{ $clients->Id }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-200 ease-in-out">Return</a>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800">PAYMENT INFORMATION</h3>
            </div>
            <div class="p-6">
                <form action="/client-submit-payment/{{ $clients->Id }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <input type="hidden" name="clientbranch" value={{ $clients->BranchId }} />
                        <input type="hidden" name="clientregion" value={{ $clients->RegionId }} />

                            <div>
                                <label for="paymentType" class="block text-sm font-medium text-gray-700 mb-2">Payment Type</label>
                                @if($assignedMemberData != null)
                                    <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentType" name="paymenttype">
                                        @php
                                            $selectedPaymentType = 'Standard';
                                        @endphp
                                        <option value="Standard" {{ $selectedPaymentType === 'Standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="Custom" {{ $selectedPaymentType === 'Custom' ? 'selected' : '' }}>Custom</option>
                                    </select>

                                    @php
                                        $base_price = $client_terms->Price;
                                        $total_payments = 0;

                                        switch($client_terms->Term){
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
                                        
                                        $balance = $total_price;
                                        foreach ($payments as $paymentKey => $paymentIndex)
                                            
                                        if($paymentIndex->VoidStatus != '1' && 
                                            $paymentIndex->Remarks == null || 
                                            ($paymentIndex->Remarks == 'Standard' || $paymentIndex->Remarks == 'Partial' || $paymentIndex->Remarks == 'Custom')){
                                            $total_payments += $paymentIndex->AmountPaid;
                                        }

                                        $balance = $total_price - $total_payments;
                                        if($balance < 0){
                                            $balance = 0;
                                        }   
                                    @endphp
                                @else
                                @php
                                    $selectedPaymentType = old('paymenttype');
                                    
                                    // Calculate lapse status based on payment history
                                    $isLapsed = false;
                                    $lastValidPayment = $payments->filter(function($p) {
                                        return $p->VoidStatus != '1' && 
                                               (is_null($p->Remarks) || in_array($p->Remarks, ['Standard', 'Partial', 'Custom', 'Reinstatement']));
                                    })->sortByDesc('Date')->first();
                                    
                                    if ($lastValidPayment) {
                                        $lastPaymentDate = \Carbon\Carbon::parse($lastValidPayment->Date);
                                        $ninetyDaysAgo = \Carbon\Carbon::now()->subDays(90);
                                        
                                        // Check if the last payment is OLDER than 90 days ago
                                        // If lastPaymentDate < ninetyDaysAgo, it is lapsed
                                        // Future dates (e.g. 2028) are NOT less than ninetyDaysAgo, so they are Active
                                        if ($lastPaymentDate->lt($ninetyDaysAgo)) {
                                            $isLapsed = true;
                                        }
                                    } else {
                                        // No valid payments = lapsed (for approved clients)
                                        $isLapsed = true;
                                    }
                                @endphp
                                
                                @if($isLapsed)
                                    {{-- Lapsed client: Only Reinstatement allowed --}}
                                    <select class="w-full px-4 py-2.5 border border-amber-400 bg-amber-50 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200" id="paymentType" name="paymenttype">
                                        <option value="Reinstatement" selected>Reinstatement</option>
                                    </select>
                                    <p class="text-amber-600 text-xs mt-1 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Client is lapsed. Only Reinstatement payments are allowed.
                                    </p>
                                @else
                                    {{-- Active client: All payment types available --}}
                                    <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentType" name="paymenttype">
                                        <option value="Partial" {{ $selectedPaymentType === 'Partial' ? 'selected' : '' }}>Partial</option>
                                        <option value="Standard" {{ $selectedPaymentType === 'Standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="Transfer" {{ $selectedPaymentType === 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                        <option value="Reinstatement" {{ $selectedPaymentType === 'Reinstatement' ? 'selected' : '' }}>Reinstatement</option>
                                        <option value="Custom" {{ $selectedPaymentType === 'Custom' ? 'selected' : '' }}>Custom</option>
                                        @if($clients->AppliedChangeMode == 0)
                                            <option value="Change Mode" {{ $selectedPaymentType === 'Change Mode' ? 'selected' : '' }}>Change Mode</option>
                                        @endif
                                    </select>
                                @endif
                                @endif
                                @error('paymenttype')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @if($assignedMemberData == null)
                                    @php
                                        $prevPaymentAmount = old('paymentamount');
                                    @endphp
                                    <input type="hidden" id="termAmount" value="{{ $clients->PaymentTermAmount }}" />
                                    <input type="hidden" id="defDownpaymentAmount" value="{{ $prevPaymentAmount }}" />
                                    <label for="paymentAmount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                                    <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount">
                                    </select>
                                @else
                                    @php
                                        $prevPaymentAmount = $balance;
                                    @endphp
                                    <label for="paymentAmount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                                    <input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" name="paymentamount" value="{{ $prevPaymentAmount }}" readonly />
                                @endif
                                @error('paymentamount')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                @php 
                                    $prevOrSeriesCode = old('orseriescode'); 
                                @endphp
                                <label for="orSeriesCode" class="block text-sm font-medium text-gray-700 mb-2">O.R Series Code</label>
                                <div class="relative">
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="orSeriesCode" name="orseriescode" maxlength="30" placeholder="Type or select O.R Series Code" value="{{ $prevOrSeriesCode }}" autocomplete="off" />
                                    <div id="orSeriesCodeDropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto">
                                        <!-- Dropdown options will be populated here -->
                                    </div>
                                </div>
                                <div id="orSeriesCodeLoading" class="hidden text-sm text-gray-500 mt-1">Loading O.R series codes...</div>
                                <div id="orSeriesCodeError" class="hidden text-sm text-red-600 mt-1"></div>
                                    @error('orseriescode')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php 
                                    $prevOrNo = old('orno'); 
                                @endphp
                                <label for="orNo" class="block text-sm font-medium text-gray-700 mb-2">O.R No.</label>
                                <div class="relative">
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="orNo" name="orno" placeholder="Type or select O.R Number" value="{{ $prevOrNo }}" autocomplete="off" />
                                    <div id="orNoDropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto">
                                        <!-- Dropdown options will be populated here -->
                                    </div>
                                </div>
                                <div id="orNoLoading" class="hidden text-sm text-gray-500 mt-1">Loading O.R numbers...</div>
                                <div id="orNoError" class="hidden text-sm text-red-600 mt-1"></div>
                                    @error('orno')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentMethod" name="paymentmethod">
                                    @php
                                        $selectedPaymentMethod = old('paymentmethod');
                                    @endphp
                                    <option value="Cash" {{ $selectedPaymentMethod === 'Cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                                @error('paymentmethod')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $prevPaymentDate = old('paymentdate');
                                @endphp
                                <label for="paymentDate" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                                <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentDate" name="paymentdate" max="2099-12-31" value="{{ $prevPaymentDate }}" />
                                    @error('paymentdate')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                    </div>
                    <!-- Submit Button -->
                    <div class="flex justify-center mt-8">
                        <button type="submit" class="px-12 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Submit Payment</button>
                    </div>
                </form>
                <script>
                    // Debug form submission
                    document.querySelector('form').addEventListener('submit', function(e) {
                        const formData = new FormData(this);
                        console.log('=== FORM SUBMISSION DEBUG ===');
                        console.log('Payment Type:', formData.get('paymenttype'));
                        console.log('Payment Amount:', formData.get('paymentamount'));
                        console.log('OR Series Code:', formData.get('orseriescode'));
                        console.log('OR Number:', formData.get('orno'));
                        console.log('Payment Method:', formData.get('paymentmethod'));
                        console.log('Payment Date:', formData.get('paymentdate'));
                        console.log('Client Region:', formData.get('clientregion'));
                        console.log('Client Branch:', formData.get('clientbranch'));
                        console.log('All Form Data:', Object.fromEntries(formData));
                    });
                </script>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/client-addpayment.js') }}"></script>
@endsection