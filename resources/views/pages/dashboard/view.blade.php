@extends('layouts.main')

@section('content')
    <div class="max-w-full mx-auto px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <!-- Header Section -->
        <div class="mb-6 sm:mb-8">
            <div class="bg-white border-2 border-primary-400 rounded-xl shadow-lg p-4 sm:p-6">
                <div class="flex items-start sm:items-center space-x-3">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 flex-shrink-0 mt-1 sm:mt-0 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold truncate text-gray-800">Dashboard</h1>
                        <p class="text-gray-600 mt-1 text-sm sm:text-base">View sales and collections based on MCPR or real-time data</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Stats Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <!-- Sales of the Day Card -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <div class="bg-gradient-to-br from-green-200 to-green-300 p-3 sm:p-4 rounded-xl shadow-md">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-600 text-xs sm:text-sm font-medium">Sales of the Day</p>
                            <h2 class="text-2xl sm:text-4xl font-bold text-gray-800" id="salesTodayCount">
                                <span class="inline-block animate-pulse">...</span>
                            </h2>
                        </div>
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Today's Date</p>
                            <p class="text-sm font-semibold text-gray-700">{{ date('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collections of the Day Card -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <div class="bg-gradient-to-br from-blue-200 to-blue-300 p-3 sm:p-4 rounded-xl shadow-md">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-blue-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-600 text-xs sm:text-sm font-medium">Collections of the Day</p>
                            <h2 class="text-2xl sm:text-4xl font-bold text-gray-800" id="collectionsTodayAmount">
                                <span class="inline-block animate-pulse">...</span>
                            </h2>
                        </div>
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Today's Date</p>
                            <p class="text-sm font-semibold text-gray-700">{{ date('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Year Filter Section -->
        <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 mb-6 sm:mb-8">
            @php
                $years = [];
                $currentYear = date("Y");
                for($i = 2005; $i <= $currentYear; $i++){
                    $years[$i] = $i;
                }
                $latestYear = end($years);
            @endphp
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                <label for="dashboard-year" class="text-gray-700 font-semibold flex items-center space-x-2 text-sm sm:text-base">
                    <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Select Year</span>
                </label>
                
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <select id="dashboard-year" class="input-field w-full sm:w-48 text-sm sm:text-base">
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                        @if(request('year') == null)
                            <option value="{{ $latestYear }}" selected hidden>{{ $latestYear }}</option>
                        @else
                            <option value="{{ request('year') }}" selected hidden>{{ request('year') }}</option>
                        @endif
                    </select>
                    
                    <button id="searchDashboardYear" class="bg-green-100 hover:bg-green-200 text-green-700 font-semibold rounded-lg shadow-md hover:shadow-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-300 focus:ring-offset-2 flex items-center space-x-2 whitespace-nowrap text-sm sm:text-base px-4 sm:px-6">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Search</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 lg:gap-8">
            <!-- Collections Chart -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300">
                <div class="bg-gradient-to-r from-blue-100 to-blue-200 px-4 sm:px-6 py-3 sm:py-4">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h2 class="text-lg sm:text-xl font-bold text-blue-800">COLLECTIONS</h2>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="relative" style="height: 300px; max-height: 400px;">
                        <canvas id="collectionsChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>

            <!-- New Sales Chart -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300">
                <div class="bg-gradient-to-r from-green-100 to-green-200 px-4 sm:px-6 py-3 sm:py-4">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <h2 class="text-lg sm:text-xl font-bold text-green-800">NEW SALES</h2>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="relative" style="height: 300px; max-height: 400px;">
                        <canvas id="salesChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endsection