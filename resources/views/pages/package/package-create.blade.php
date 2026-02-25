<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-500 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">New Package</h1>
                    <p class="text-green-600 text-sm">Create new package for your clients</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
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
            <a href="/package" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-200 ease-in-out">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Return to Packages
            </a>
        </div>
        <!-- Form Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Package Information</h3>
            </div>
            <form class="p-6" action="/submit-package-insert" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Package Name -->
                    <div>
                        @php
                            $prevPackage = old('packagename')
                        @endphp
                        <label for="packageName" class="block text-sm font-medium text-gray-700 mb-2">Package Name</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" id="packageName" name="packagename" maxlength="30" value="{{ $prevPackage }}"/>
                        @error('packagename')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Price -->
                    <div>
                        @php
                            $prevPrice = old('packageprice')
                        @endphp
                        <label for="packagePrice" class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" id="packagePrice" name="packageprice" maxlength="30" placeholder="0" value="{{ $prevPrice }}" />
                        @error('packageprice')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Spotcash -->
                    <div>
                        @php
                            $prevSpotPrice = old('spotcashprice')
                        @endphp
                        <label for="spotcashPrice" class="block text-sm font-medium text-gray-700 mb-2">Spotcash</label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" id="spotcashPrice" name="spotcashprice" maxlength="30" placeholder="0" value="{{ $prevSpotPrice }}" />
                        @error('spotcashprice')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Annual -->
                    <div>
                        @php
                            $prevAnnualPrice = old('annualprice')
                        @endphp
                        <label for="annualPrice" class="block text-sm font-medium text-gray-700 mb-2">Annual</label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" id="annualPrice" name="annualprice" maxlength="30" placeholder="0" value="{{ $prevAnnualPrice }}" />
                        @error('annualprice')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Semi-Annual -->
                    <div>
                        @php
                            $prevSemiAnnualPrice = old('semiannualprice')
                        @endphp
                        <label for="semiannualPrice" class="block text-sm font-medium text-gray-700 mb-2">Semi-Annual</label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" id="semiannualPrice" name="semiannualprice" maxlength="30" placeholder="0" value="{{ $prevSemiAnnualPrice }}" />
                        @error('semiannualprice')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Quarterly -->
                    <div>
                        @php
                            $prevQuarterlyPrice = old('quarterlyprice')
                        @endphp
                        <label for="quarterlyPrice" class="block text-sm font-medium text-gray-700 mb-2">Quarterly</label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" id="quarterlyPrice" name="quarterlyprice" maxlength="30" placeholder="0" value="{{ $prevQuarterlyPrice }}" />
                        @error('quarterlyprice')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Monthly -->
                    <div>
                        @php
                            $prevMonthlyPrice = old('monthlyprice')
                        @endphp
                        <label for="monthlyPrice" class="block text-sm font-medium text-gray-700 mb-2">Monthly</label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" id="monthlyPrice" name="monthlyprice" maxlength="30" placeholder="0" value="{{ $prevMonthlyPrice }}" />
                        @error('monthlyprice')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Empty column for spacing -->
                    <div></div>
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-center mt-8">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-200 ease-in-out transform hover:scale-105 shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Create Package
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>  
@endsection