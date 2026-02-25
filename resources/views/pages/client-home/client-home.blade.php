<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm">
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="main-grid">
            <!-- Left Column - Client Information -->
            <div id="client-info-column">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Header with Toggle Button -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Client Information</h3>
                        <button id="toggle-client-info" class="p-2 rounded-lg hover:bg-gray-200 transition duration-200" title="Toggle client information">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-6" id="client-info-content">
                        <!-- Contract Section -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-3 pb-2 border-b-2 border-gray-200">Contract</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Contract No.</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->ContractNumber }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Package</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Package }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Term</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Term  . ' ( ₱ ' . number_format($clients->Price, 2) .' )' }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Region</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->RegionName }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Branch</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->BranchName }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Best place to collect</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->BestPlaceToCollect }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Best time to collect</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->BestTimeToCollect }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Status</span>
                                    <span class="text-gray-900 text-sm">
                                        @if($clients->Status == '1')
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">Pending</span>
                                        @elseif($clients->Status == '2')
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-200 text-blue-700">Verified</span>
                                        @elseif($clients->Status == '3')
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-200 text-green-700">Approved</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- Personal Section -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-3 pb-2 border-b-2 border-gray-200">Personal</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Name</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->LastName . ', ' . $clients->FirstName . " " . $clients->MiddleName }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Birth Date</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->BirthDate }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Age</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Age }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Gender</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Gender }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Civil Status</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->CivilStatus }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Occupation</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Occupation }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Birth Place</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->BirthPlace }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Province</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->ProvinceName }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">City</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->CityName }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Barangay</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->BarangayName }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Street</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Street }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Zipcode</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->ZipCode }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Contact Section -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-3 pb-2 border-b-2 border-gray-200">Contact</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Home No.</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->HomeNumber }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Mobile No.</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->MobileNumber }}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Email Address</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->EmailAddress }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Beneficiaries Section -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-3 pb-2 border-b-2 border-gray-200">Beneficiaries</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Principal (Age)</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->PrincipalBeneficiaryName . ' (' . $clients->PrincipalBeneficiaryAge . ')'}}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Beneficiary 1 (Age)</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Secondary1Name . ' (' . $clients->Secondary1Age . ')'}}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Beneficiary 2 (Age)</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Secondary2Name . ' (' . $clients->Secondary2Age . ')'}}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Beneficiary 3 (Age)</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Secondary3Name . ' (' . $clients->Secondary3Age . ')'}}</span>
                                </div>
                                <div class="flex justify-between py-2 hover:bg-gray-50 px-2 rounded transition">
                                    <span class="text-gray-600 font-medium text-sm">Beneficiary 4 (Age)</span>
                                    <span class="text-gray-900 text-sm">{{ $clients->Secondary4Name . ' (' . $clients->Secondary4Age . ')'}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Payment History -->
            <div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                        <h3 class="text-lg font-semibold text-gray-800">Payment History</h3>
                    </div>
                    <div class="p-6">
                        @php
                                $base_price = $clients->Price;
                                $total_payments = 0;

                                switch($clients->Term){
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
                                        $total_price = $base_price * 60;
                                }

                                // Calculate payments BEFORE displaying cards
                                foreach ($payments as $paymentKey => $paymentIndex) {
                                    if($paymentIndex->VoidStatus != '1' && 
                                        ($paymentIndex->Remarks == null || 
                                        $paymentIndex->Remarks == 'Standard' || 
                                        $paymentIndex->Remarks == 'Partial' || 
                                        $paymentIndex->Remarks == 'Custom')){
                                        $total_payments += $paymentIndex->AmountPaid;
                                    }
                                }
                                $balance = $total_price - $total_payments;
                        @endphp
                        
                        <!-- Custom CSS for Dynamic Icon Visibility -->
                        <style>
                            /* 
                                Logic: Hide icons ONLY when:
                                1. Screen is XL or larger (where 4-column grid activates)
                                2. AND Sidebar is OPEN (causing cramping)
                            */
                            @media (min-width: 1280px) {
                                #main-grid.sidebar-open .dashboard-card-icon {
                                    display: none !important;
                                }
                            }
                        </style>

                        <!-- Payment Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                            <!-- Package Price Card -->
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-blue-600 font-medium mb-1">Package Price</p>
                                        <p class="text-2xl font-bold text-blue-900">₱ {{ number_format($total_price, 2) }}</p>
                                    </div>
                                    <div class="bg-blue-200 rounded-full p-3 dashboard-card-icon">
                                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Package Payment Card -->
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-purple-600 font-medium mb-1">Total Package Payment</p>
                                        <p class="text-2xl font-bold text-purple-900">₱ {{ number_format($total_payments, 2) }}</p>
                                    </div>
                                    <div class="bg-purple-200 rounded-full p-3 dashboard-card-icon">
                                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            @if($balance > 0)
                                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-red-600 font-medium mb-1">Outstanding Balance</p>
                                            <p class="text-2xl font-bold text-red-900">₱ {{ number_format($balance, 2) }}</p>
                                        </div>
                                        <div class="bg-red-200 rounded-full p-3 dashboard-card-icon">
                                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-green-600 font-medium mb-1">Balance</p>
                                            <p class="text-2xl font-bold text-green-900">₱ {{ number_format(0, 2) }}</p>
                                            <p class="text-xs text-green-600 mt-1">Fully Paid</p>
                                        </div>
                                        <div class="bg-green-200 rounded-full p-3 dashboard-card-icon">
                                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Payment Status Card - Hidden for fully paid clients -->
                            @if($balance > 0)
                                @php
                                    $paymentStatus = 'Active';
                                    $isLapsed = false;
                                    
                                    // Get last payment date (excluding voided)
                                    $lastPayment = $payments->where('VoidStatus', '!=', '1')
                                        ->sortByDesc('Date')
                                        ->first();
                                    
                                    if($lastPayment) {
                                        $lastPaymentDate = \Carbon\Carbon::parse($lastPayment->Date);
                                        $monthsDiff = $lastPaymentDate->diffInMonths(\Carbon\Carbon::now());
                                        
                                        if($monthsDiff > 3) {
                                            $paymentStatus = 'Lapse';
                                            $isLapsed = true;
                                        }
                                    }
                                @endphp
                                @if($isLapsed)
                                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm text-red-600 font-medium mb-1">Payment Status</p>
                                                <p class="text-2xl font-bold text-red-900">{{ $paymentStatus }}</p>
                                            </div>
                                            <div class="bg-red-200 rounded-full p-3 dashboard-card-icon">
                                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm text-green-600 font-medium mb-1">Payment Status</p>
                                                <p class="text-2xl font-bold text-green-900">{{ $paymentStatus }}</p>
                                            </div>
                                            <div class="bg-green-200 rounded-full p-3 dashboard-card-icon">
                                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <!-- Payment Table -->
                        <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
                            <table id="common_dataTable" class="table table-hover font-sm w-100">
                                <thead class="bg-gradient-to-r from-green-500 to-green-600">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">No</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Series Code</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">OR No.</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Amount Paid</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Payment Type</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                                        @if(session('user_roleid') != 7)
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                            <tbody>
                                @php $displayIndex = 1; @endphp
                                @foreach ($payments as $paymentKey => $paymentIndex)
                                    @if($paymentIndex->VoidStatus != '1')
                                        <tr>
                                            <td>{{ $displayIndex }}</td>
                                            <td>
                                                @if ($paymentIndex->officialReceipt && $paymentIndex->officialReceipt->orBatch)
                                                    {{ $paymentIndex->officialReceipt->orBatch->SeriesCode }}
                                                @else
                                                    <span class="text-secondary">Not available</span>
                                                @endif
                                            </td>
                                            <td>{{ $paymentIndex->ORNo }}</td>
                                            <td>P {{ number_format($paymentIndex->AmountPaid, 2) }}</td>
                                            <td>
                                                @php
                                                    $paymentType = $paymentIndex->Remarks ?? 'Standard';
                                                    $displayType = $paymentType;
                                                    $typeClass = 'px-3 py-1 rounded-full text-xs font-semibold bg-blue-200 text-blue-800';
                                                    
                                                    if($paymentType == 'Partial') {
                                                        $typeClass = 'px-3 py-1 rounded-full text-xs font-semibold bg-yellow-200 text-yellow-800';
                                                    } elseif($paymentType == 'Custom' || $paymentType == 'Custom Add Payment') {
                                                        $typeClass = 'px-3 py-1 rounded-full text-xs font-semibold bg-purple-200 text-purple-800';
                                                        $displayType = 'Custom';
                                                    } elseif($paymentType == 'Transfer') {
                                                        $typeClass = 'px-3 py-1 rounded-full text-xs font-semibold bg-indigo-200 text-indigo-800';
                                                    } elseif($paymentType == 'Reinstatement') {
                                                        $typeClass = 'px-3 py-1 rounded-full text-xs font-semibold bg-green-200 text-green-800';
                                                    } elseif($paymentType == 'Change Mode') {
                                                        $typeClass = 'px-3 py-1 rounded-full text-xs font-semibold bg-orange-200 text-orange-800';
                                                    } elseif($paymentType == 'Penalty') {
                                                        $typeClass = 'px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-800';
                                                    }
                                                @endphp
                                                <span class="{{ $typeClass }}">
                                                    {{ $displayType }}
                                                </span>
                                            </td>
                                            <td>{{ $paymentIndex->Date }}</td>
                                            <td>
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-green-600 text-white shadow-sm">
                                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Success
                                                </span>
                                            </td>
                                            @if(session('user_roleid') != 7)
                                                <td>
                                                    <a data-bs-toggle="modal" data-bs-target="#paymentVoidModal" data-payment-id="{{ $paymentIndex->Id }}" data-payment-orno="{{ $paymentIndex->ORNo }}" role="button">
                                                        <span class="badge bg-danger">Void</span>
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                        @php $displayIndex++; @endphp
                                    @endif
                                @endforeach
                            </tbody>
                            </table>
                        </div>
                        @if(session('user_roleid') != 7)
                            @if($clients->Status == '3')
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <a href="/client-addpayment/{{ $clients->cid }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-medium rounded-md shadow-sm hover:shadow transition duration-150" role="button">Add Payment</a>
                                    <a href="/client-printsoa/{{ $clients->cid }}?export=true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-medium rounded-md shadow-sm hover:shadow transition duration-150" role="button" target="_blank">SOA (CSV)</a>
                                    <a href="/client-printsoa-pdf/{{ $clients->cid }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white text-sm font-medium rounded-md shadow-sm hover:shadow transition duration-150" role="button" target="_blank">SOA (PDF)</a>
                                    @if($balance <= 0)
                                        @if($clients->CFPNO == null)
                                            <a class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white text-sm font-medium rounded-md shadow-sm hover:shadow transition duration-150" data-bs-toggle="modal" data-bs-target="#showCfpNoInputModal" data-client-id="{{ $clients->cid }}" role="button">Certificate of Full Payment</a>
                                        @else
                                            <a href="/client-printcofp/{{ $clients->cid }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white text-sm font-medium rounded-md shadow-sm hover:shadow transition duration-150" role="button">Certificate of Full Payment</a>
                                        @endif
                                    @endif
                                </div>
                            @else
                                <p class="mt-4 text-gray-500 text-sm">** Client needs to be approved to add a new payment.</p>
                                <!-- Debug: Always show PDF button for testing -->
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <a href="/client-printsoa-pdf/{{ $clients->cid }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white text-sm font-medium rounded-md shadow-sm hover:shadow transition duration-150" role="button" target="_blank">SOA (PDF) - Test</a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-client-info');
    const clientInfoContent = document.getElementById('client-info-content');
    const clientInfoColumn = document.getElementById('client-info-column');
    const mainGrid = document.getElementById('main-grid');
    let isCollapsed = false;

    // Initialize sidebar-open class since we start expanded
    mainGrid.classList.add('sidebar-open');

    toggleBtn.addEventListener('click', function() {
        isCollapsed = !isCollapsed;
        
        if (isCollapsed) {
            // Collapse client info
            clientInfoContent.style.display = 'none';
            clientInfoColumn.style.maxWidth = '60px';
            clientInfoColumn.style.minWidth = '60px';
            mainGrid.classList.remove('lg:grid-cols-2');
            mainGrid.classList.add('lg:grid-cols-[60px_1fr]');
            
            // Remove sidebar-open class to show icons
            mainGrid.classList.remove('sidebar-open');
            
            // Change icon to chevron right (expand)
            toggleBtn.innerHTML = `
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            `;
            toggleBtn.setAttribute('title', 'Expand client information');
            toggleBtn.parentElement.style.justifyContent = 'center';
            toggleBtn.parentElement.querySelector('h3').style.display = 'none';
        } else {
            // Expand client info
            clientInfoContent.style.display = 'block';
            clientInfoColumn.style.maxWidth = 'none';
            clientInfoColumn.style.minWidth = 'auto';
            mainGrid.classList.remove('lg:grid-cols-[60px_1fr]');
            mainGrid.classList.add('lg:grid-cols-2');

            // Add sidebar-open class to hide icons if needed
            mainGrid.classList.add('sidebar-open');
            
            // Change icon to chevron left (collapse)
            toggleBtn.innerHTML = `
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            `;
            toggleBtn.setAttribute('title', 'Collapse client information');
            toggleBtn.parentElement.style.justifyContent = 'space-between';
            toggleBtn.parentElement.querySelector('h3').style.display = 'block';
        }
    });
});
</script>
@endsection