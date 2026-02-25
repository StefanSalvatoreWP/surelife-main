<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">Update Package</h1>
                    <p class="text-green-600 text-sm">Update selected package for your clients</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('duplicate'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-700 font-medium">{{ session('duplicate') }}</p>
                </div>
            </div>
        @endif

        <!-- Return Button -->
        <div class="mb-6">
            <a href="/package" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Return
            </a>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Package Information</h3>
            </div>
            <div class="p-6">
                <form action="/submit-package-update/{{ $packages->Id }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Package Name and Price -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            @php
                                $prevPackage = old('packagename', $packages->Package);
                            @endphp
                            <label for="packageName" class="block text-sm font-semibold text-gray-700 mb-2">Package Name</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" id="packageName" name="packagename" maxlength="30" value="{{ $prevPackage }}" placeholder="Enter package name" />
                            @error('packagename')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            @php
                                $prevPrice = old('packageprice', $packages->Price);
                            @endphp
                            <label for="packagePrice" class="block text-sm font-semibold text-gray-700 mb-2">Price</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₱</span>
                                </div>
                                <input type="number" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" id="packagePrice" name="packageprice" placeholder="0.00" value="{{ $prevPrice }}" step="0.01" />
                            </div>
                            @error('packageprice')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Payment Terms Section -->
                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-gray-700 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Payment Terms Pricing
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Spotcash -->
                            <div>
                                @php
                                    $prevSpotPrice = old('spotcashprice', $paymentterms[0]->Price);
                                @endphp
                                <label for="spotcashPrice" class="block text-sm font-semibold text-gray-700 mb-2">Spotcash</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input type="number" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" id="spotcashPrice" name="spotcashprice" placeholder="0.00" value="{{ $prevSpotPrice }}" step="0.01" />
                                </div>
                                @error('spotcashprice')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Annual -->
                            <div>
                                @php
                                    $prevAnnualPrice = old('annualprice', $paymentterms[1]->Price);
                                @endphp
                                <label for="annualPrice" class="block text-sm font-semibold text-gray-700 mb-2">Annual</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input type="number" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" id="annualPrice" name="annualprice" placeholder="0.00" value="{{ $prevAnnualPrice }}" step="0.01" />
                                </div>
                                @error('annualprice')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Semi-Annual -->
                            <div>
                                @php
                                    $prevSemiAnnualPrice = old('semiannualprice', $paymentterms[2]->Price);
                                @endphp
                                <label for="semiannualPrice" class="block text-sm font-semibold text-gray-700 mb-2">Semi-Annual</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input type="number" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" id="semiannualPrice" name="semiannualprice" placeholder="0.00" value="{{ $prevSemiAnnualPrice }}" step="0.01" />
                                </div>
                                @error('semiannualprice')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quarterly -->
                            <div>
                                @php
                                    $prevQuarterlyPrice = old('quarterlyprice', $paymentterms[3]->Price);
                                @endphp
                                <label for="quarterlyPrice" class="block text-sm font-semibold text-gray-700 mb-2">Quarterly</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input type="number" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" id="quarterlyPrice" name="quarterlyprice" placeholder="0.00" value="{{ $prevQuarterlyPrice }}" step="0.01" />
                                </div>
                                @error('quarterlyprice')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Monthly -->
                            <div>
                                @php
                                    $prevMonthlyPrice = old('monthlyprice', $paymentterms[4]->Price);
                                @endphp
                                <label for="monthlyPrice" class="block text-sm font-semibold text-gray-700 mb-2">Monthly</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input type="number" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" id="monthlyPrice" name="monthlyprice" placeholder="0.00" value="{{ $prevMonthlyPrice }}" step="0.01" />
                                </div>
                                @error('monthlyprice')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center mt-8">
                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Update Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>  
@endsection