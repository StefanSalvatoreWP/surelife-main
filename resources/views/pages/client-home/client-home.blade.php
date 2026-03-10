<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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

        @php
            // Use PackagePrice (actual package price) not PaymentTerm Price (term amount)
            $base_price = $clients->PackagePrice;
            $total_payments = 0;

            switch($clients->Term){
                case "Spotcash": $total_price = $base_price; break;	
                case "Annual": $total_price = $base_price * 5; break;
                case "Semi-Annual": $total_price = ($base_price * 2) * 5; break;
                case "Quarterly": $total_price = ($base_price * 4) * 5; break;
                case "Monthly": $total_price = $base_price * 60; break;
                default: $total_price = $base_price * 60;
            }

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

        <!-- Modern Payment Summary Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Package Price Card -->
                <div class="rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300" style="background-color: #3b82f6; color: white;">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0 pr-3">
                            <p class="text-sm font-medium mb-1" style="color: rgba(255,255,255,0.8);">Package Price</p>
                            <p class="text-2xl font-bold whitespace-nowrap" style="color: white;">₱ {{ number_format($total_price, 2) }}</p>
                        </div>
                        <div class="rounded-full p-3 flex-shrink-0" style="background-color: rgba(255,255,255,0.3);">
                            <svg class="w-6 h-6" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Payment Card -->
                <div class="rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300" style="background-color: #9333ea; color: white;">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0 pr-3">
                            <p class="text-sm font-medium mb-1" style="color: rgba(255,255,255,0.8);">Total Payment</p>
                            <p class="text-2xl font-bold whitespace-nowrap" style="color: white;">₱ {{ number_format($total_payments, 2) }}</p>
                        </div>
                        <div class="rounded-full p-3 flex-shrink-0" style="background-color: rgba(255,255,255,0.3);">
                            <svg class="w-6 h-6" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Balance Card -->
                @if($balance > 0)
                    <div class="rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300" style="background-color: #ef4444; color: white;">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0 pr-3">
                                <p class="text-sm font-medium mb-1" style="color: rgba(255,255,255,0.8);">Outstanding Balance</p>
                                <p class="text-2xl font-bold whitespace-nowrap" style="color: white;">₱ {{ number_format($balance, 2) }}</p>
                            </div>
                            <div class="rounded-full p-3 flex-shrink-0" style="background-color: rgba(255,255,255,0.3);">
                                <svg class="w-6 h-6" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300" style="background-color: #22c55e; color: white;">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0 pr-3">
                                <p class="text-sm font-medium mb-1" style="color: rgba(255,255,255,0.8);">Balance</p>
                                <p class="text-2xl font-bold whitespace-nowrap" style="color: white;">₱ 0.00</p>
                                <p class="text-xs mt-1" style="color: rgba(255,255,255,0.8);">✓ Fully Paid</p>
                            </div>
                            <div class="rounded-full p-3 flex-shrink-0" style="background-color: rgba(255,255,255,0.3);">
                                <svg class="w-6 h-6" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment Status Card -->
                @php
                    $paymentStatus = 'Active';
                    $isLapsed = false;
                    $lastPayment = $payments->where('VoidStatus', '!=', '1')->sortByDesc('Date')->first();
                    if($lastPayment) {
                        $lastPaymentDate = \Carbon\Carbon::parse($lastPayment->Date);
                        $monthsDiff = $lastPaymentDate->diffInMonths(\Carbon\Carbon::now());
                        if($monthsDiff > 3) {
                            $paymentStatus = 'Lapse';
                            $isLapsed = true;
                        }
                    } else {
                        $paymentStatus = 'No Payment';
                    }
                @endphp
                @if($isLapsed)
                    <div class="rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300" style="background-color: #f97316; color: white;">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0 pr-3">
                                <p class="text-sm font-medium mb-1" style="color: rgba(255,255,255,0.8);">Payment Status</p>
                                <p class="text-2xl font-bold" style="color: white;">{{ $paymentStatus }}</p>
                            </div>
                            <div class="rounded-full p-3 flex-shrink-0" style="background-color: rgba(255,255,255,0.3);">
                                <svg class="w-6 h-6" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300" style="background-color: #10b981; color: white;">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0 pr-3">
                                <p class="text-sm font-medium mb-1" style="color: rgba(255,255,255,0.8);">Payment Status</p>
                                <p class="text-2xl font-bold" style="color: white;">{{ $paymentStatus }}</p>
                            </div>
                            <div class="rounded-full p-3 flex-shrink-0" style="background-color: rgba(255,255,255,0.3);">
                                <svg class="w-6 h-6" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Client Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
                        <div class="flex items-center">
                            <div class="bg-white/20 rounded-full p-2 mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Client Information</h3>
                                <p class="text-indigo-100 text-sm">{{ $clients->ContractNumber }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Client Name Banner -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 mb-6 border border-gray-200">
                            <div class="flex items-center">
                                <div class="bg-indigo-100 rounded-full p-3 mr-4 flex-shrink-0">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xl font-bold text-gray-900 truncate">{{ $clients->LastName . ', ' . $clients->FirstName . ' ' . $clients->MiddleName }}</h4>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        @if($clients->Status == '1')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                                <span class="w-2 h-2 bg-gray-500 rounded-full mr-1.5"></span>Pending
                                            </span>
                                        @elseif($clients->Status == '2')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-1.5"></span>Verified
                                            </span>
                                        @elseif($clients->Status == '3')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>Approved
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ $clients->Package }}</span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">{{ $clients->Term }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Information Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contract Details -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Contract Details
                                </h5>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Contract No.</span>
                                        <span class="text-gray-900 text-sm font-medium">{{ $clients->ContractNumber }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Package</span>
                                        <span class="text-gray-900 text-sm font-medium">{{ $clients->Package }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Term</span>
                                        <span class="text-gray-900 text-sm font-medium whitespace-nowrap">{{ $clients->Term }} (₱{{ number_format($clients->Price, 2) }})</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Region</span>
                                        <span class="text-gray-900 text-sm font-medium text-right ml-4">{{ $clients->RegionName }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Branch</span>
                                        <span class="text-gray-900 text-sm font-medium text-right ml-4">{{ $clients->BranchName }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Details -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Personal Details
                                </h5>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Birth Date</span>
                                        <span class="text-gray-900 text-sm font-medium">{{ $clients->BirthDate }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Age</span>
                                        <span class="text-gray-900 text-sm font-medium">{{ $clients->Age }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Gender</span>
                                        <span class="text-gray-900 text-sm font-medium">{{ $clients->Gender }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Civil Status</span>
                                        <span class="text-gray-900 text-sm font-medium">{{ $clients->CivilStatus }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Occupation</span>
                                        <span class="text-gray-900 text-sm font-medium text-right ml-4">{{ $clients->Occupation }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Contact Information
                                </h5>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Mobile</span>
                                        <span class="text-gray-900 text-sm font-medium">{{ $clients->MobileNumber }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Home</span>
                                        <span class="text-gray-900 text-sm font-medium">{{ $clients->HomeNumber ?? 'N/A' }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Email</span>
                                        <span class="text-gray-900 text-sm font-medium break-all ml-4">{{ $clients->EmailAddress }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Address
                                </h5>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Province</span>
                                        <span class="text-gray-900 text-sm font-medium text-right ml-4">{{ $clients->ProvinceName }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">City</span>
                                        <span class="text-gray-900 text-sm font-medium text-right ml-4">{{ $clients->CityName }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Barangay</span>
                                        <span class="text-gray-900 text-sm font-medium text-right ml-4">{{ $clients->BarangayName }}</span>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-500 text-sm">Street</span>
                                        <span class="text-gray-900 text-sm font-medium text-right ml-4">{{ $clients->Street }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Collection Info -->
                        <div class="mt-6">
                            <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center mb-4">
                                <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Collection Details
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                    <p class="text-yellow-600 text-xs font-medium mb-1">Best Place to Collect</p>
                                    <p class="text-gray-900 text-sm font-medium">{{ $clients->BestPlaceToCollect ?? 'Not specified' }}</p>
                                </div>
                                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                    <p class="text-yellow-600 text-xs font-medium mb-1">Best Time to Collect</p>
                                    <p class="text-gray-900 text-sm font-medium">{{ $clients->BestTimeToCollect ?? 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Beneficiaries -->
                        <div class="mt-6">
                            <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center mb-4">
                                <svg class="w-4 h-4 mr-2 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                Beneficiaries
                            </h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div class="bg-pink-50 rounded-lg p-3 border border-pink-200">
                                    <p class="text-pink-600 text-xs font-medium mb-1">Principal</p>
                                    <p class="text-gray-900 text-sm font-medium">{{ $clients->PrincipalBeneficiaryName }} ({{ $clients->PrincipalBeneficiaryAge }})</p>
                                </div>
                                @if($clients->Secondary1Name)
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <p class="text-gray-500 text-xs font-medium mb-1">Beneficiary 1</p>
                                        <p class="text-gray-900 text-sm font-medium">{{ $clients->Secondary1Name }} ({{ $clients->Secondary1Age }})</p>
                                    </div>
                                @endif
                                @if($clients->Secondary2Name)
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <p class="text-gray-500 text-xs font-medium mb-1">Beneficiary 2</p>
                                        <p class="text-gray-900 text-sm font-medium">{{ $clients->Secondary2Name }} ({{ $clients->Secondary2Age }})</p>
                                    </div>
                                @endif
                                @if($clients->Secondary3Name)
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <p class="text-gray-500 text-xs font-medium mb-1">Beneficiary 3</p>
                                        <p class="text-gray-900 text-sm font-medium">{{ $clients->Secondary3Name }} ({{ $clients->Secondary3Age }})</p>
                                    </div>
                                @endif
                                @if($clients->Secondary4Name)
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <p class="text-gray-500 text-xs font-medium mb-1">Beneficiary 4</p>
                                        <p class="text-gray-900 text-sm font-medium">{{ $clients->Secondary4Name }} ({{ $clients->Secondary4Age }})</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Payment History -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600">
                        <div class="flex items-center">
                            <div class="bg-white/20 rounded-full p-2 mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Payment History</h3>
                        </div>
                    </div>
                    <div class="p-4 max-h-96 overflow-y-auto">
                        @php $displayIndex = 1; @endphp
                        @foreach ($payments as $paymentKey => $paymentIndex)
                            @if($paymentIndex->VoidStatus != '1')
                                <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-100 hover:bg-gray-100 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1 min-w-0 pr-2">
                                            <p class="text-xs text-gray-500">{{ $paymentIndex->Date }}</p>
                                            <p class="text-sm font-semibold text-gray-900 whitespace-nowrap">₱ {{ number_format($paymentIndex->AmountPaid, 2) }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 flex-shrink-0">
                                            Success
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span>OR: {{ $paymentIndex->ORNo }}</span>
                                        <span class="text-gray-400">{{ $paymentIndex->Remarks ?? 'Standard' }}</span>
                                    </div>
                                </div>
                                @php $displayIndex++; @endphp
                            @endif
                        @endforeach

                        @if($displayIndex == 1)
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm">No payment records yet</p>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    @if(session('user_roleid') != 7)
                        @if($clients->Status == '3')
                            <div class="p-4 border-t border-gray-200 bg-gray-50">
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="/client-addpayment/{{ $clients->cid }}" class="inline-flex items-center justify-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Add Payment
                                    </a>
                                    <a href="/client-printsoa-pdf/{{ $clients->cid }}" class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors" target="_blank">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        SOA PDF
                                    </a>
                                </div>
                                @if($balance <= 0 && $clients->CFPNO == null)
                                    <a class="mt-2 w-full inline-flex items-center justify-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-lg transition-colors" onclick="showCfpInputModal({{ $clients->cid }})" role="button">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Certificate of Full Payment
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="p-4 border-t border-gray-200 bg-yellow-50">
                                <p class="text-xs text-yellow-700 text-center">Client needs approval to add payments</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
// Certificate of Full Payment Modal
function showCfpInputModal(clientId) {
    const modal = document.getElementById('swiftModal');
    const iconDiv = document.getElementById('swiftModalIcon');
    const titleEl = document.getElementById('swiftModalTitle');
    const messageEl = document.getElementById('swiftModalMessage');
    const actionsEl = document.getElementById('swiftModalActions');

    if (!modal) return;

    iconDiv.innerHTML = `<svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>`;
    iconDiv.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-purple-100';

    titleEl.textContent = 'Certificate of Full Payment';
    messageEl.innerHTML = `
        <div class="text-gray-600 text-sm mb-4">Enter certificate number to generate the Certificate of Full Payment.</div>
        <input type="text" id="cfpNoInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Enter certificate number">
    `;

    actionsEl.innerHTML = '';
    
    const cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.className = 'w-full py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition duration-200';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.addEventListener('click', () => hideSwiftModal());
    actionsEl.appendChild(cancelBtn);

    const submitBtn = document.createElement('button');
    submitBtn.type = 'button';
    submitBtn.className = 'w-full py-3 px-6 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-xl transition duration-200';
    submitBtn.textContent = 'Submit';
    submitBtn.addEventListener('click', () => {
        const cfpNo = document.getElementById('cfpNoInput')?.value;
        if (!cfpNo) {
            alert('Please enter a certificate number');
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/submit-cfp/' + clientId;
        form.innerHTML = `
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="cfp_no" value="${cfpNo}">
        `;
        document.body.appendChild(form);
        form.submit();
    });
    actionsEl.appendChild(submitBtn);

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
</script>
@endsection