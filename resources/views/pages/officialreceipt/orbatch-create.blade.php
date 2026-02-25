<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-300 p-6 mb-6">
            <h1 class="text-3xl font-bold text-green-800 mb-2">New Official Receipt Batch</h1>
            <p class="text-green-600 text-sm">Create new batch for your selected region and branch.</p>
        </div>

        <!-- Alert Messages -->
        @if(session('duplicate'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <p class="text-red-700 font-medium">{{ session('duplicate') }}</p>
            </div>
        @endif

        <!-- Return Button -->
        <div class="mb-6">
            <a href="/orbatch" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-200 ease-in-out">Return</a>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800">BATCH INFORMATION</h3>
            </div>
            <div class="p-6">
                <form action="/submit-orbatch-insert" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                                @php
                                    $prevSeriesCode = old('seriescode');
                                @endphp
                                <label for="seriesCode" class="block text-sm font-medium text-gray-700 mb-2">Series Code</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="seriesCode" name="seriescode" maxlength="30" value="{{ $prevSeriesCode }}"/>
                                    @error('seriescode')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                        </div>
                        <div>
                                @php
                                    $prevStartOrNum = old('startornum');
                                @endphp
                                <label for="startorNum" class="block text-sm font-medium text-gray-700 mb-2">Start OR Number</label>
                                <input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="startorNum" name="startornum" maxlength="30" value="{{ $prevStartOrNum }}"/>
                                    @error('startornum')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                                @php
                                    $prevEndOrNum = old('endornum');
                                @endphp
                                <label for="endorNum" class="block text-sm font-medium text-gray-700 mb-2">End OR Number</label>
                                <input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="endorNum" name="endornum" maxlength="30" value="{{ $prevEndOrNum }}"/>
                                    @error('endornum')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                        </div>
                        <div>
                                <label for="regionName" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="regionName" name="regionid">
                                    @php
                                        $selectedRegion = old('regionid');
                                    @endphp
                                    @foreach($regions as $region)
                                        <option value="{{ $region->Id }}" {{ $selectedRegion == $region->Id ? 'selected' : '' }}>
                                            {{ $region->RegionName }}
                                        </option>
                                    @endforeach
                                </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                                @php
                                    $selectedBranch = old('branchid');
                                @endphp
                                <input type="hidden" id="selectedBranch" value={{ $selectedBranch }} />
                                <label for="branchName" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="branchName" name="branchid">
                                    <option value="0">Select branch</option>
                                </select>
                                    @error('branchid')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                        </div>
                        <div>
                                @php
                                    $selectedType = old('ortype');
                                @endphp
                                <label for="orType" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="orType" name="ortype">
                                    <option value="1">Standard</option>
                                    <option value="2">Virtual</option>
                                </select>
                                    @error('ortype')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                        </div>
                    </div>
                    <!-- Submit Button -->
                    <div class="flex justify-center mt-8">
                        <button type="submit" class="px-12 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Submit Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>  
    <script src="{{ asset('js/orbatch-create.js') }}"></script>
@endsection