<!-- 2023 SilverDust) S. Maceren -->
@extends('layouts.main')

@section('styles')
<style>
    .custom-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1.25rem;
        padding-right: 2.5rem;
    }
</style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white border-2 border-green-200 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-green-800 mb-2">
                        <svg class="w-8 h-8 inline-block mr-2 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Assign Plan
                    </h1>
                    <p class="text-green-600 text-sm md:text-base">
                        {{ $client->LastName }}, {{ $client->FirstName }} {{ $client->MiddleName }}
                    </p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-blue-700 font-medium">Assign existing plan to another member.</p>
            </div>
        </div>

        <!-- Error Alert -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-4 mb-6">
            <a href="/client-view/{{ $client->Id }}"
                class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Return
            </a>
        </div>

        <!-- Form -->
        <form action="/submit-client-assign/{{ $client->Id }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Personal Information Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Personal Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <!-- Last Name & First Name -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                @php $prevLastName = old('lastname'); @endphp
                                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200"
                                    id="lastName" name="lastname" value="{{ $prevLastName }}" maxlength="30"
                                    placeholder="Enter last name" />
                                @error('lastname')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php $prevFirstName = old('firstname'); @endphp
                                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200"
                                    id="firstName" name="firstname" value="{{ $prevFirstName }}" maxlength="30"
                                    placeholder="Enter first name" />
                                @error('firstname')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Middle Name & Gender -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                @php $prevMiddleName = old('middlename'); @endphp
                                <label for="middleName" class="block text-sm font-medium text-gray-700 mb-1">Middle
                                    Name</label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200"
                                    id="middleName" name="middlename" value="{{ $prevMiddleName }}" maxlength="30"
                                    placeholder="Enter middle name" />
                            </div>
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 bg-white custom-select" id="gender" name="gender">
                                    @php $selectedGender = old('gender'); @endphp
                                    <option value="Male" {{ $selectedGender === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $selectedGender === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Birth Date & Age -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                @php $prevBirthDate = old('birthdate'); @endphp
                                <label for="birthDate" class="block text-sm font-medium text-gray-700 mb-1">Birth Date <span
                                        class="text-red-500">*</span></label>
                                <input type="date"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200"
                                    id="birthDate" name="birthdate" value="{{ $prevBirthDate }}" />
                                @error('birthdate')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php $prevAge = old('age'); @endphp
                                <label for="age" class="block text-sm font-medium text-gray-700 mb-1">Age</label>
                                <input type="number"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed"
                                    id="age" name="age" value="{{ $prevAge }}" readonly />
                                @error('age')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Region & Branch -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 bg-white custom-select" id="region" name="region">
                                    @php $selectedRegion = old('region'); @endphp
                                    @foreach($regions as $region)
                                        <option value="{{ $region->Id }}" {{ $selectedRegion == $region->Id ? 'selected' : '' }}>
                                            {{ $region->RegionName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('region')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="branch" class="block text-sm font-medium text-gray-700 mb-1">Branch <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 bg-white custom-select" id="branch" name="branch">
                                    @php $selectedBranch = old('branch'); @endphp
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->Id }}" {{ $selectedBranch == $branch->Id ? 'selected' : '' }}>
                                            {{ $branch->BranchName }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="defBranch" value="{{ $selectedBranch }}" />
                                @error('branch')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Province & City -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                @php $selectedProvince = old('province'); @endphp
                                <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 bg-white custom-select" id="province" name="province">
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->Province }}" {{ $selectedProvince == $province->Province ? 'selected' : '' }}>
                                            {{ $province->Province }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('province')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php $selectedCity = old('city'); @endphp
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                                <input type="hidden" id="prevCity" value="{{ $selectedCity }}" />
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 bg-white custom-select" id="city" name="city">
                                    @foreach($cities as $city)
                                        <option value="{{ $city->City }}" {{ $selectedCity == $city->City ? 'selected' : '' }}>
                                            {{ $city->City }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('city')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Barangay & ZIP code -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                @php $selectedBarangay = old('barangay'); @endphp
                                <label for="barangay" class="block text-sm font-medium text-gray-700 mb-1">Barangay <span class="text-red-500">*</span></label>
                                <input type="hidden" id="prevBarangay" value="{{ $selectedBarangay }}" />
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 bg-white custom-select" id="barangay" name="barangay">
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay->Barangay }}" {{ $selectedBarangay == $barangay->Barangay ? 'selected' : '' }}>
                                            {{ $barangay->Barangay }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('barangay')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php $prevZipcode = old('zipcode'); @endphp
                                <label for="zipcode" class="block text-sm font-medium text-gray-700 mb-1">ZIP Code</label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed"
                                    id="zipcode" name="zipcode" value="{{ $prevZipcode }}" readonly />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Payment Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <!-- Payment Type & Assign Fee -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                @php $paymentType = 'Standard'; @endphp
                                <label for="paymentType" class="block text-sm font-medium text-gray-700 mb-1">Payment
                                    Type</label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed font-medium"
                                    id="paymentType" name="paymenttype" value="{{ $paymentType }}" readonly />
                                @error('paymenttype')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php $paymentAmount = 250; @endphp
                                <label for="paymentAmount" class="block text-sm font-medium text-gray-700 mb-1">Assign
                                    Fee</label>
                                <div class="relative">
                                    <input type="text"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 font-bold cursor-not-allowed"
                                        id="paymentAmount" name="paymentamount"
                                        value="₱ {{ number_format($paymentAmount, 2) }}" readonly />
                                </div>
                                @error('paymentamount')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- OR Series Code & OR No -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                @php $prevOrSeriesCode = old('orseriescode'); @endphp
                                <label for="orSeriesCode" class="block text-sm font-medium text-gray-700 mb-1">O.R Series
                                    Code <span class="text-red-500">*</span></label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200"
                                    id="orSeriesCode" name="orseriescode" value="{{ $prevOrSeriesCode }}" maxlength="30"
                                    placeholder="Enter series code" />
                                @error('orseriescode')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php $prevOrNo = old('orno'); @endphp
                                <label for="orNo" class="block text-sm font-medium text-gray-700 mb-1">O.R No. <span
                                        class="text-red-500">*</span></label>
                                <input type="text"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200"
                                    id="orNo" name="orno" value="{{ $prevOrNo }}" maxlength="30"
                                    placeholder="Enter OR number" />
                                @error('orno')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Payment Method & Payment Date -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200 bg-white custom-select" id="paymentMethod" name="paymentmethod">
                                    @php $selectedPaymentMethod = old('paymentmethod'); @endphp
                                    <option value="Cash" {{ $selectedPaymentMethod === 'Cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                                @error('paymentmethod')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php $prevPaymentDate = old('paymentdate'); @endphp
                                <label for="paymentDate" class="block text-sm font-medium text-gray-700 mb-1">Payment Date
                                    <span class="text-red-500">*</span></label>
                                <input type="date"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200"
                                    id="paymentDate" name="paymentdate" value="{{ $prevPaymentDate }}" />
                                @error('paymentdate')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Alert -->
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mt-6 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-amber-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-amber-700 font-medium">
                        <strong>Important:</strong> Once submitted, this data cannot be changed. Please review all
                        information carefully before proceeding.
                    </p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center mt-8">
                <button type="submit"
                    class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Submit Assignment
                </button>
            </div>
        </form>
    </div>
    <script src="{{ asset('js/client-create.js') }}"></script>
@endsection