<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">View Package - {{ $packages->Package}}</h1>
                    <p class="text-green-600 text-sm">Displays different payment term amounts from the selected package</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @elseif(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @elseif(session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-yellow-700 font-medium">{{ session('warning') }}</p>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3 mb-6">
            <a href="/package" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Return
            </a>
            @if($packages->Active == 1)
                <a class="inline-flex items-center px-4 py-2 bg-red-50 hover:bg-red-100 text-red-900 text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition duration-200 cursor-pointer"
                   onclick="showSwiftModal('Disable Package', 'Are you sure you want to disable package {{ $packages->Package }}?\n\nThis package will no longer be available to clients.', 'warning', [{text: 'Disable Package', class: 'bg-red-500 hover:bg-red-600 text-white', action: 'submitDisablePackage()'}, {text: 'Cancel', class: 'bg-gray-200 hover:bg-gray-300 text-gray-800'}])">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Disable Package
                </a>
            @else
                <a class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition duration-200 cursor-pointer"
                   onclick="showSwiftModal('Enable Package', 'Are you sure you want to enable package {{ $packages->Package }}?\n\nThis package will be made available to all clients.', 'success', [{text: 'Enable Package', class: 'bg-green-500 hover:bg-green-600 text-white', action: 'submitEnablePackage()'}, {text: 'Cancel', class: 'bg-gray-200 hover:bg-gray-300 text-gray-800'}])">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enable Package
                </a>
            @endif
        </div>

        <!-- Package Price Card -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                </svg>
                <p class="text-blue-800 font-semibold text-lg">Package Price: ₱ {{ number_format($packages->Price, 2) }}</p>
            </div>
        </div>
            @php

                // old scheme
                $spotcashProcFee = $spotcashprice * 0.10;
                $annualProcFee = $annualprice * 0.10;
                $semiannualProcFee = $semiannualprice * 0.10;
                $quarterlyProcFee = $quarterlyprice * 0.10;
                $monthlyProcFee = $monthlyprice * 0.10;

                // new scheme
                $annualCollectorComs = $annualprice * 0.20;
                $semiannualCollectorComs = $semiannualprice * 0.20;
                $quarterlyCollectorComs = $quarterlyprice * 0.20;
                $monthlyCollectorComs = $monthlyprice * 0.20;

                $old_monthlyBasic = ($monthlyprice - $monthlyProcFee) * .4;
            @endphp
        </div>
        
        <!-- Old Scheme Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Old Scheme</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-emerald-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Term</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Price</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">10% Processing Free</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">40% Commission</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Gross Remittance</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">6% TAC</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">8% TAC</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Net Remittance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Spotcash</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($spotcashprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($spotcashProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic * 12, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($spotcashprice - ($old_monthlyBasic * 12), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($spotcashprice - $spotcashProcFee) * .06, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($spotcashprice - $spotcashProcFee) * .08, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($spotcashprice - (($spotcashprice - $spotcashProcFee) * .08), 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Annual</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($annualprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($annualProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic * 12, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($annualprice - ($old_monthlyBasic * 12), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($annualprice - $annualProcFee) * .06, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($annualprice - $annualProcFee) * .08, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($annualprice - (($annualprice - $annualProcFee) * .08), 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Semi-Annual</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($semiannualprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($semiannualProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic * 6, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($semiannualprice - ($old_monthlyBasic * 6), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($semiannualprice - $semiannualProcFee) * .06, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($semiannualprice - $semiannualProcFee) * .08, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($semiannualprice - (($semiannualprice - $semiannualProcFee) * .08), 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Quarterly</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($quarterlyprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($quarterlyProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic * 3, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($quarterlyprice - ($old_monthlyBasic * 3), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($quarterlyprice - $quarterlyProcFee) * .06, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($quarterlyprice - $quarterlyProcFee) * .08, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($quarterlyprice - (($quarterlyprice - $quarterlyProcFee) * .08), 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Monthly</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($monthlyprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($monthlyProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($monthlyprice - $old_monthlyBasic, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($monthlyprice - $monthlyProcFee) * .06, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format(($monthlyprice - $monthlyProcFee) * .08, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($monthlyprice - (($monthlyprice - $monthlyProcFee) * .08), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- New Scheme Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">New Scheme</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-emerald-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Term</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Price</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">10% Processing Free</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">40% Commission</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Gross Remittance</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">20% Collectors Com</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Net Remittance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Spotcash</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($spotcashprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($spotcashProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic * 9, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($spotcashprice - ($old_monthlyBasic * 9), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ 0.00</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($spotcashprice - 0, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Annual</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($annualprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($annualProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic * 12, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($annualprice - ($old_monthlyBasic * 12), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($annualCollectorComs, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($annualprice - $annualCollectorComs, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Semi-Annual</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($semiannualprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($semiannualProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic * 6, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($semiannualprice - ($old_monthlyBasic * 6), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($semiannualCollectorComs, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($semiannualprice - $semiannualCollectorComs, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Quarterly</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($quarterlyprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($quarterlyProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic * 3, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($quarterlyprice - ($old_monthlyBasic * 3), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($quarterlyCollectorComs, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($quarterlyprice - $quarterlyCollectorComs, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">Monthly</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($monthlyprice, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($monthlyProcFee, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($old_monthlyBasic, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($monthlyprice - $old_monthlyBasic, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($monthlyCollectorComs, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">₱ {{ number_format($monthlyprice - $monthlyCollectorComs, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/packageview.js') }}"></script>
    <script>
        function submitDisablePackage() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/package-disable/{{ $packages->Id }}';
            
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
        
        function submitEnablePackage() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/package-enable/{{ $packages->Id }}';
            
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