<!-- 2024 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-300 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">New Expense</h1>
                    <p class="text-green-600 text-sm">Create new expense for your selected region and branch</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
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
            <a href="/expenses" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Return
            </a>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Expense Information</h3>
            </div>
            <div class="p-6">
                <form action="/submit-expense-insert" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- Region and Branch -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            @php
                                $selectedRegion = old('regionid');
                            @endphp
                            <label for="regionName" class="block text-sm font-semibold text-gray-700 mb-2">Region</label>
                            <div class="relative">
                                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition appearance-none" id="regionName" name="regionid">
                                    @foreach($regions as $region)
                                        <option value="{{ $region->Id }}" {{ $selectedRegion == $region->Id ? 'selected' : '' }}>
                                            {{ $region->RegionName }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            @php
                                $selectedBranch = old('branchid');
                            @endphp
                            <input type="hidden" id="selectedBranch" value="{{ $selectedBranch }}" />
                            <label for="branchName" class="block text-sm font-semibold text-gray-700 mb-2">Branch</label>
                            <div class="relative">
                                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition appearance-none" id="branchName" name="branchid">
                                    <option value="0">Select branch</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            @error('branchid')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Amount and Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            @php
                                $prevExpenseAmount = old('expenseamount', 0);
                            @endphp
                            <label for="expenseAmount" class="block text-sm font-semibold text-gray-700 mb-2">Amount</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₱</span>
                                </div>
                                <input type="number" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" id="expenseAmount" name="expenseamount" value="{{ $prevExpenseAmount }}" placeholder="0.00" step="0.01" />
                            </div>
                            @error('expenseamount')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            @php
                                $prevExpenseDate = old('expensedate');
                            @endphp
                            <label for="expenseDate" class="block text-sm font-semibold text-gray-700 mb-2">Expense Date</label>
                            <div class="relative">
                                <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" id="expenseDate" name="expensedate" value="{{ $prevExpenseDate }}" />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>
                            @error('expensedate')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Description and Image -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            @php
                                $selectedExpenseDesc = old('expensedesc');
                            @endphp
                            <label for="expenseDesc" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                            <div class="relative">
                                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition appearance-none" id="expenseDesc" name="expensedesc">
                                    @foreach($expensedesc as $desc)
                                        <option value="{{ $desc->Id }}" {{ $selectedExpenseDesc == $desc->Id ? 'selected' : '' }}>
                                            {{ $desc->Description }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            @error('expensedesc')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="expenseImage" class="block text-sm font-semibold text-gray-700 mb-2">Image</label>
                            <div class="relative">
                                <input type="file" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100" id="expenseImage" name="expenseimage" accept="image/*" />
                            </div>
                            @error('expenseimage')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="mb-6">
                        @php
                            $prevExpenseNote = old('expensenote');
                        @endphp
                        <label for="expenseNote" class="block text-sm font-semibold text-gray-700 mb-2">Note</label>
                        <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition resize-none" id="expenseNote" name="expensenote" rows="4" placeholder="Add any additional notes about this expense...">{{ $prevExpenseNote }}</textarea>
                        @error('expensenote')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>  
    <script src="{{ asset('js/expense-create.js') }}"></script>
@endsection