<!-- 2023 SilverDust) S. Maceren --> 
<!-- UPDATED: Force recompile - 2026-02-27 22:15:00 -->
<!-- Cache-busting: Timestamp updated to force browser reload --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-300 p-6 mb-6">
            <h1 class="text-3xl font-bold text-green-800 mb-2">New Client</h1>
            <p class="text-green-600 text-sm">Create new client for your selected branch</p>
        </div>

        <!-- Alert Messages -->
        @if(session('duplicate'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <p class="text-red-700 font-medium">{{ session('duplicate') }}</p>
            </div>
        @endif

        <!-- Swift-Style Modal -->
        <div id="swiftModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform scale-100 transition-transform duration-300 overflow-hidden">
                <div id="swiftModalHeader" class="px-6 pt-6 pb-4 text-center">
                    <div id="swiftModalIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"></div>
                    <h3 id="swiftModalTitle" class="text-xl font-semibold text-gray-900 mb-2"></h3>
                </div>
                <div id="swiftModalBody" class="px-6 pb-6 text-center">
                    <p id="swiftModalMessage" class="text-gray-600 text-sm leading-relaxed"></p>
                </div>
                <div id="swiftModalActions" class="px-6 pb-6 flex flex-col gap-2"></div>
            </div>
        </div>

        <!-- Return Button -->
        <div class="mb-6">
            <a href="/client" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-200 ease-in-out">Return</a>
        </div>
        <form id="clientForm" action="/submit-client-insert" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- CONTRACT Section -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-xl">
                    <h3 class="text-lg font-bold text-gray-800">CONTRACT</h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="region" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="region" name="region">
                                    @php
                                        $selectedRegion = old('region');
                                    @endphp
                                    @foreach($regions as $region)
                                        <option value="{{ $region->Id }}" {{ $selectedRegion == $region->Id ? 'selected' : '' }}>
                                            {{ $region->RegionName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('region')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="branch" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="branch" name="branch">
                                    @php
                                        $selectedBranch = old('branch');
                                    @endphp
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->Id }}" {{ $selectedBranch == $branch->Id ? 'selected' : '' }}>
                                            {{ $branch->BranchName }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="defBranch" value="{{ $selectedBranch }}" />
                                @error('branch')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <!-- Loading Skeleton -->
                                <div id="branchSkeleton" class="hidden w-full h-[42px] bg-gray-50 border border-gray-200 rounded-lg animate-pulse flex items-center px-4">
                                    <div class="h-4 bg-gray-300 rounded w-2/3"></div>
                                </div>
                            </div>
                            <div>
                                @php
                                    $prevContractNo = old('contractno');
                                @endphp
                                <label for="contractNo" class="block text-sm font-medium text-gray-700 mb-2">Contract No.</label>
                                <div class="searchable-dropdown" id="contractNoWrapper">
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" 
                                           id="contractNoSearch" placeholder="Type or select Contract No." autocomplete="off" value="{{ $prevContractNo }}" />
                                    <input type="hidden" id="contractno" name="contractno" value="{{ $prevContractNo }}" />
                                    <div class="dropdown-list" id="contractNoList"></div>
                                </div>
                                <input type="hidden" id="prevContractNo" value="{{ $prevContractNo }}" />
                                    @error('contractno')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                <label for="package" class="block text-sm font-medium text-gray-700 mb-2">Package</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="package" name="package">
                                    @php
                                        $selectedPackage = old('package');
                                    @endphp
                                    @foreach($packages as $package)
                                        <option value="{{ $package->Id }}" {{ $selectedPackage == $package->Id ? 'selected' : '' }}>
                                            {{ $package->Package }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('package')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                            <div>
                                @php
                                    $prevPackagePrice = old('packageprice');
                                @endphp
                                <label for="packagePrice" class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="packagePrice" name="packageprice" value="{{ $prevPackagePrice }}" readonly />
                                    @error('packageprice')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevPaymentTerm = old('paymentterm');
                                @endphp
                                <label for="paymentTerm" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                                <input type="hidden" id="defPaymentTerm" value="{{ $prevPaymentTerm }}" />
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentTerm" name="paymentterm">
                                </select>
                                @error('paymentterm')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $prevTermAmount = old('termamount');
                                @endphp
                                <label for="termAmount" class="block text-sm font-medium text-gray-700 mb-2">Term Amount</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="termAmount" name="termamount" value="{{ $prevTermAmount }}" readonly />
                                    @error('termamount')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                <label for="recruitedBy" class="block text-sm font-medium text-gray-700 mb-2">FSA <span class="text-gray-400 font-normal text-xs">(Optional)</span></label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="recruitedBy" name="recruitedby">
                                    @php
                                        $selectedRecruitedBy = old('recruitedby');
                                    @endphp
                                    <option value="">-- None / N/A --</option>
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->Id }}" {{ $selectedRecruitedBy == $staff->Id ? 'selected' : '' }}>
                                            {{ $staff->LastName . ", " . $staff->FirstName }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="defRecruitedBy" value="{{ $selectedRecruitedBy }}" />
                                @error('recruitedby')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                </div>
            
            <!-- PAYMENT Section -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-xl">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                        </svg>
                        PAYMENT
                    </h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="downpaymentType" class="block text-sm font-medium text-gray-700 mb-2">Downpayment Type</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="downpaymentType" name="downpaymenttype">
                                    @php
                                        $selectedDownpaymentType = old('downpaymenttype');
                                    @endphp
                                    <option value="Partial" {{ $selectedDownpaymentType === 'Partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="Standard" {{ $selectedDownpaymentType === 'Standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="Transfer" {{ $selectedDownpaymentType === 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="Reinstatement" {{ $selectedDownpaymentType === 'Reinstatement' ? 'selected' : '' }}>Reinstatement</option>
                                    <option value="Change Mode" {{ $selectedDownpaymentType === 'Change Mode' ? 'selected' : '' }}>Change Mode</option>
                                    <option value="Custom" {{ $selectedDownpaymentType === 'Custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                                @error('downpaymenttype')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $prevDownpaymentAmount = old('paymentamount');
                                @endphp
                                <label for="paymentAmount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                                <input type="hidden" id="defDownpaymentAmount" value="{{ $prevDownpaymentAmount }}" />
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount">
                                </select>
                                @error('paymentamount')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php 
                                    $prevOrSeriesCode = old('orseriescode'); 
                                @endphp
                                <label for="orSeriesCode" class="block text-sm font-medium text-gray-700 mb-2">O.R Series Code</label>
                                <div class="searchable-dropdown" id="orSeriesWrapper">
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" 
                                           id="orSeriesSearch" placeholder="Type or select O.R Series Code" autocomplete="off" />
                                    <input type="hidden" id="orSeriesCode" name="orseriescode" value="{{ $prevOrSeriesCode }}" />
                                    <div class="dropdown-list" id="orSeriesList"></div>
                                </div>
                                <input type="hidden" id="prevOrSeriesCode" value="{{ $prevOrSeriesCode }}" />
                                    @error('orseriescode')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevOrNumber = old('ornumber'); 
                                @endphp
                                <label for="orNumber" class="block text-sm font-medium text-gray-700 mb-2">O.R No.</label>
                                <div class="searchable-dropdown" id="orNumberWrapper">
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" 
                                           id="orNumberSearch" placeholder="Type or select O.R Number" autocomplete="off" />
                                    <input type="hidden" id="orNumber" name="ornumber" value="{{ $prevOrNumber }}" />
                                    <div class="dropdown-list" id="orNumberList"></div>
                                </div>
                                <input type="hidden" id="prevOrNumber" value="{{ $prevOrNumber }}" />
                                    @error('ornumber')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                            <div>
                                <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <select class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 hover:border-gray-400 shadow-sm" id="paymentMethod" name="paymentmethod">
                                    @php
                                        $selectedPaymentMethod = old('paymentmethod');
                                    @endphp
                                    <option value=""> Select Payment Method</option>
                                    <option value="Cash" {{ $selectedPaymentMethod === 'Cash' ? 'selected' : '' }}> Cash</option>
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
                                <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentDate" name="paymentdate" value="{{ $prevPaymentDate }}" />
                                    @error('paymentdate')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                </div>
            </div>
            
            <!-- PERSONAL Section -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-xl">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        PERSONAL
                    </h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                @php
                                    $prevLastName = old('lastname');
                                @endphp
                                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="lastName" name="lastname" value="{{ $prevLastName }}" maxlength="30"/>
                                    @error('lastname')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevFirstName = old('firstname');
                                @endphp
                                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="firstName" name="firstname" value="{{ $prevFirstName }}" maxlength="30"/>
                                    @error('firstname')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevMiddleName = old('middlename');
                                @endphp
                                <label for="middleName" class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="middleName" name="middlename" value="{{ $prevMiddleName }}" maxlength="30"/>
                            </div>
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="gender" name="gender">
                                    @php
                                        $selectedGender = old('gender');
                                    @endphp
                                    <option value="Male" {{ $selectedGender === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $selectedGender === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                @php
                                    $prevBirthDate = old('birthdate');
                                    // Max date allowed = today - 18 years (client must be 18+)
                                    $maxBirthDate = \Carbon\Carbon::now()->subYears(18)->format('Y-m-d');
                                @endphp
                                <label for="birthDate" class="block text-sm font-medium text-gray-700 mb-2">Birth Date <span class="text-xs text-gray-400 font-normal">(Must be 18+)</span></label>
                                <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="birthDate" name="birthdate" value="{{ $prevBirthDate }}" max="{{ $maxBirthDate }}" />
                                    @error('birthdate')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                <p id="birthDateError" class="text-red-600 text-sm mt-1 hidden">Client must be at least 18 years old.</p>
                            </div>
                            <div>
                                @php
                                    $prevAge = old('age');
                                @endphp
                                <label for="age" class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                                <input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="age" name="age" value="{{ $prevAge }}" readonly />
                                    @error('age')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevBirthPlace = old('birthplace');
                                @endphp
                                <label for="birthPlace" class="block text-sm font-medium text-gray-700 mb-2">Birth Place</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="birthPlace" name="birthplace" value="{{ $prevBirthPlace }}" />
                            </div>
                            <div>
                                @php
                                    $selectedCivilStatus = old('civilstatus');
                                @endphp
                                <label for="civilStatus" class="block text-sm font-medium text-gray-700 mb-2">Civil Status</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="civilStatus" name="civilstatus">
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Separated">Separated</option>
                                    <option value="Widowed">Widowed</option>
                                    @if($selectedCivilStatus != null)
                                        <option hidden selected value="{{ $selectedCivilStatus }}">{{ $selectedCivilStatus }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                            <div>
                                @php
                                    $prevReligion = old('religion');
                                    $prevReligionOther = old('religion_other');
                                @endphp
                                <label for="religion" class="block text-sm font-medium text-gray-700 mb-2">Religion</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="religion" name="religion">
                                    <option value="">Select Religion</option>
                                    <option value="Roman Catholic" {{ $prevReligion === 'Roman Catholic' ? 'selected' : '' }}>Roman Catholic</option>
                                    <option value="Islam" {{ $prevReligion === 'Islam' ? 'selected' : '' }}>Islam</option>
                                    <option value="Iglesia ni Cristo" {{ $prevReligion === 'Iglesia ni Cristo' ? 'selected' : '' }}>Iglesia ni Cristo</option>
                                    <option value="Protestant" {{ $prevReligion === 'Protestant' ? 'selected' : '' }}>Protestant</option>
                                    <option value="Born Again" {{ $prevReligion === 'Born Again' ? 'selected' : '' }}>Born Again</option>
                                    <option value="Seventh-day Adventist" {{ $prevReligion === 'Seventh-day Adventist' ? 'selected' : '' }}>Seventh-day Adventist</option>
                                    <option value="Jehovah's Witness" {{ $prevReligion === 'Jehovah\'s Witness' ? 'selected' : '' }}>Jehovah's Witness</option>
                                    <option value="Buddhist" {{ $prevReligion === 'Buddhist' ? 'selected' : '' }}>Buddhist</option>
                                    <option value="Hindu" {{ $prevReligion === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                    <option value="Other" {{ $prevReligion === 'Other' ? 'selected' : '' }}>Other (please specify)</option>
                                </select>
                                @error('religion')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                
                                <!-- Custom religion input (shown when "Other" is selected) -->
                                <div id="religionOtherDiv" class="mt-2 {{ $prevReligion !== 'Other' ? 'hidden' : '' }}">
                                    <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="religionOther" name="religion_other" placeholder="Please specify your religion" value="{{ $prevReligionOther }}" />
                                    @error('religion_other')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                @php
                                    $prevOccupation = old('occupation');
                                @endphp
                                <label for="occupation" class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="occupation" name="occupation" value="{{ $prevOccupation }}" />
                            </div>
                            <div>
                                @php
                                    $prevPlaceToCollect = old('bestplacetocollect');
                                @endphp
                                <label for="bestPlaceToCollect" class="block text-sm font-medium text-gray-700 mb-2">Best Place to Collect</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="bestPlaceToCollect" name="bestplacetocollect" value="{{ $prevPlaceToCollect }}" />
                                    @error('bestplacetocollect')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevTimeToCollect = old('besttimetocollect');
                                @endphp
                                <label for="bestTimeToCollect" class="block text-sm font-medium text-gray-700 mb-2">Best Time to Collect</label>
                                <input type="time" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="bestTimeToCollect" name="besttimetocollect" value="{{ $prevTimeToCollect }}" />
                                    @error('besttimetocollect')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                        <!-- Address Fields -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-md font-semibold text-gray-700 mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                Address Information
                            </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Address Region -->
                            <div>
                                @php
                                    $selectedAddressRegion = old('address_region');
                                @endphp
                                <label for="addressRegion" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="addressRegion" name="address_region">
                                    <option value="">Select Region</option>
                                    @foreach($addressRegions as $region)
                                        <option value="{{ $region->code }}" {{ $selectedAddressRegion == $region->code ? 'selected' : '' }}>
                                            {{ $region->description }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('address_region')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Address Province -->
                            <div>
                                @php
                                    $selectedAddressProvince = old('province');
                                @endphp
                                <label for="addressProvince" class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="addressProvince" name="province">
                                    <option value="">Select Province</option>
                                </select>
                                @error('province')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Address City -->
                            <div>
                                @php
                                    $selectedAddressCity = old('city');
                                @endphp
                                <label for="addressCity" class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="addressCity" name="city">
                                    <option value="">Select City/Municipality</option>
                                </select>
                                @error('city')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Address Barangay -->
                            <div>
                                @php
                                    $selectedAddressBarangay = old('barangay');
                                @endphp
                                <label for="addressBarangay" class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="addressBarangay" name="barangay">
                                    <option value="">Select Barangay</option>
                                </select>
                                @error('barangay')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                            <div>
                                @php
                                    $prevZipcode = old('zipcode');
                                @endphp
                                <label for="zipcode" class="block text-sm font-medium text-gray-700 mb-2">ZIP code</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="zipcode" name="zipcode" maxlength="10" value="{{ $prevZipcode }}" placeholder="Select city first" title="Select a city to auto-fill zipcode or enable manual input" />
                            </div>
                            <div>
                                @php
                                    $prevStreet = old('street');
                                @endphp
                                <label for="street" class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="street" name="street" value="{{ $prevStreet }}" />
                            </div>
                        </div>
                        
                        <!-- Hidden inputs for JavaScript old value access -->
                        <input type="hidden" id="oldAddressRegion" value="{{ old('address_region') }}" />
                        <input type="hidden" id="oldProvince" value="{{ old('province') }}" />
                        <input type="hidden" id="oldCity" value="{{ old('city') }}" />
                        <input type="hidden" id="oldBarangay" value="{{ old('barangay') }}" />
                        </div>
                    </div>
                </div>

            <!-- HOME ADDRESS Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            HOME ADDRESS
                        </h3>
                        <div class="flex items-center">
                            <input type="checkbox" id="sameAsCurrentAddress" name="same_as_current_address" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer" />
                            <label for="sameAsCurrentAddress" class="ml-2 text-sm font-medium text-gray-700 cursor-pointer">Same as Current Address</label>
                        </div>
                    </div>
                </div>
                <div class="p-6" id="homeAddressFields">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                @php
                                    $selectedHomeRegion = old('home_region');
                                @endphp
                                <label for="homeRegion" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="homeRegion" name="home_region">
                                    <option value="">Select Region</option>
                                    @foreach($addressRegions as $region)
                                        <option value="{{ $region->code }}" {{ $selectedHomeRegion == $region->code ? 'selected' : '' }}>
                                            {{ $region->description }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('home_region')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $selectedHomeProvince = old('home_province');
                                @endphp
                                <label for="homeProvince" class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="homeProvince" name="home_province">
                                    <option value="">Select Province</option>
                                </select>
                                @error('home_province')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $selectedHomeCity = old('home_city');
                                @endphp
                                <label for="homeCity" class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="homeCity" name="home_city">
                                    <option value="">Select City/Municipality</option>
                                </select>
                                @error('home_city')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $selectedHomeBarangay = old('home_barangay');
                                @endphp
                                <label for="homeBarangay" class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="homeBarangay" name="home_barangay">
                                    <option value="">Select Barangay</option>
                                </select>
                                @error('home_barangay')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                            <div>
                                @php
                                    $prevHomeZipcode = old('home_zipcode');
                                @endphp
                                <label for="homeZipcode" class="block text-sm font-medium text-gray-700 mb-2">ZIP code</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="homeZipcode" name="home_zipcode" maxlength="10" value="{{ $prevHomeZipcode }}" placeholder="Select city first" />
                            </div>
                            <div>
                                @php
                                    $prevHomeStreet = old('home_street');
                                @endphp
                                <label for="homeStreet" class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="homeStreet" name="home_street" maxlength="30" value="{{ $prevHomeStreet }}" />
                            </div>
                        </div>
                </div>
            </div>

            <!-- Hidden inputs for home address cascading system -->
            <input type="hidden" id="oldHomeRegion" value="{{ old('home_region') }}" />
            <input type="hidden" id="oldHomeProvince" value="{{ old('home_province') }}" />
            <input type="hidden" id="oldHomeCity" value="{{ old('home_city') }}" />
            <input type="hidden" id="oldHomeBarangay" value="{{ old('home_barangay') }}" />
            
            <!-- CONTACT Section -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-xl">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        CONTACT
                    </h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                @php
                                    $prevTelephone = old('telephone');
                                @endphp
                                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">Telephone</label>
                                <input type="tel" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="telephone" name="telephone" maxlength="15" value="{{ $prevTelephone }}" placeholder="02 8123 4567" inputmode="numeric" onkeypress="var c=event.charCode;return c===0||(c>=48&&c<=57);" oninput="formatTelephone(this);" />
                            </div>
                            <div>
                                <label for="mobileNumber" class="block text-sm font-medium text-gray-700 mb-2">Mobile (+63)</label>
                                @php 
                                    $fullMobileNumber = old('mobilenumber');
                                    // Remove leading 0 if present (convert 09123456789 to 9123456789)
                                    if (strlen($fullMobileNumber) == 11 && substr($fullMobileNumber, 0, 1) == '0') {
                                        $fullMobileNumber = substr($fullMobileNumber, 1);
                                    }
                                @endphp
                                <input type="tel" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="mobileNumber" name="mobilenumber" placeholder="912 345 6789" maxlength="12" value="{{ $fullMobileNumber }}" inputmode="numeric" onkeypress="var c=event.charCode;return c===0||(c>=48&&c<=57);" oninput="formatMobile(this);" />
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <div class="flex flex-wrap items-center gap-2">
                                    @php 
                                        $selectedEmail = old('email'); 
                                        $selectedEmailAddress = old('emailaddress');
                                        $customEmailDomain = old('customemaildomain');
                                    @endphp
                                    <input type="text" class="flex-1 min-w-[120px] px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="email" name="email" maxlength="30" value="{{ $selectedEmail }}" placeholder="juan.santos" />
                                    <span class="flex items-center px-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-600 whitespace-nowrap text-sm">@</span>
                                    <select class="flex-1 min-w-[100px] px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" id="emailDomainSelect" name="emailaddress" onchange="toggleCustomEmailDomain()">
                                        @foreach($emails as $email)
                                            <option value="{{ $email->Email }}" {{ $selectedEmailAddress == $email->Email ? 'selected' : '' }}>
                                                {{ $email->Email }}
                                            </option>
                                        @endforeach
                                        <option value="others" {{ $selectedEmailAddress == 'others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 mt-2" id="customEmailDomain" name="customemaildomain" placeholder="Enter custom domain (e.g., company.com)" maxlength="50" value="{{ $customEmailDomain }}" style="display: {{ $selectedEmailAddress == 'others' ? 'block' : 'none' }};" />
                                <div class="space-y-1 mt-1">
                                    @error('email')
                                    <p class="text-red-600 text-sm">{{ $message }}</p>
                                    @enderror
                                    @error('customemaildomain')
                                    <p class="text-red-600 text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            
            <!-- BENEFICIARIES Section -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-xl">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v2h8v-2zM16 11a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                        BENEFICIARIES
                    </h3>
                </div>
                <div class="p-6">
                        <div class="mb-6 bg-blue-50/50 p-6 rounded-xl border border-blue-100">
                            <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Principal Beneficiary Details
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    @php
                                        $prevPrincipalBeneficiary = old('principalbeneficiary');
                                        $prevPrincipalBeneficiaryAge = old('principalbeneficiaryage');
                                        $prevPrincipalBeneficiaryRelation = old('principalbeneficiaryrelation');
                                    @endphp
                                    <label for="principalBeneficiary" class="block text-xs font-medium text-gray-700 mb-1">Full Name & Age</label>
                                    <div class="flex gap-3">
                                        <input type="text" class="flex-1 px-4 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-white" id="principalBeneficiary" name="principalbeneficiary" value="{{ $prevPrincipalBeneficiary }}" placeholder="Full Name" />
                                        <input type="number" class="w-20 px-4 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-white" id="principalBeneficiaryAge" name="principalbeneficiaryage" maxlength="3" value="{{ $prevPrincipalBeneficiaryAge }}" placeholder="Age" />
                                    </div>
                                </div>
                                <div>
                                    <label for="principalBeneficiaryRelation" class="block text-xs font-medium text-gray-700 mb-1">Relationship</label>
                                    <select class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-white" id="principalBeneficiaryRelation" name="principalbeneficiaryrelation">
                                        <option value="">Select Relationship</option>
                                        <option value="Spouse" {{ $prevPrincipalBeneficiaryRelation == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                                        <option value="Parent" {{ $prevPrincipalBeneficiaryRelation == 'Parent' ? 'selected' : '' }}>Parent</option>
                                        <option value="Child" {{ $prevPrincipalBeneficiaryRelation == 'Child' ? 'selected' : '' }}>Child</option>
                                        <option value="Sibling" {{ $prevPrincipalBeneficiaryRelation == 'Sibling' ? 'selected' : '' }}>Sibling</option>
                                        <option value="Other Relative" {{ $prevPrincipalBeneficiaryRelation == 'Other Relative' ? 'selected' : '' }}>Other Relative</option>
                                        <option value="Non-Relative" {{ $prevPrincipalBeneficiaryRelation == 'Non-Relative' ? 'selected' : '' }}>Non-Relative</option>
                                    </select>
                                </div>
                                <div class="col-span-1 md:col-span-2">
                                    <label for="principalBeneficiaryId" class="block text-xs font-medium text-gray-700 mb-1">Upload Valid ID (Optional)</label>
                                    <input type="file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition duration-200" id="principalBeneficiaryId" name="principalbeneficiaryid" accept="image/jpeg,image/png,application/pdf" />
                                    <p class="text-xs text-gray-400 mt-1">Accepted formats: JPG, PNG, PDF. Max size: 2MB.</p>
                                    @error('principalbeneficiaryid')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                            <div>
                                @php
                                    $prevBeneficiary1 = old('beneficiary1');
                                    $prevBeneficiary1Age = old('beneficiary1age');
                                @endphp
                                <label for="beneficiary1" class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 1 (Age)</label>
                                <div class="flex gap-3">
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="beneficiary1" name="beneficiary1" value="{{ $prevBeneficiary1 }}" />
                                    <input type="number" class="w-20 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="beneficiary1age" name="beneficiary1age" maxlength="3" value="{{ $prevBeneficiary1Age }}" placeholder="Age" />
                                </div>
                            </div>
                            <div>
                                @php
                                    $prevBeneficiary2 = old('beneficiary2');
                                    $prevBeneficiary2Age = old('beneficiary2age');
                                @endphp
                                <label for="beneficiary2" class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 2 (Age)</label>
                                <div class="flex gap-3">
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="beneficiary2" name="beneficiary2" value="{{ $prevBeneficiary2 }}" />
                                    <input type="number" class="w-20 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="beneficiary2age" name="beneficiary2age" maxlength="3" value="{{ $prevBeneficiary2Age }}" placeholder="Age" />
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mt-6">
                            <div>
                                @php
                                    $prevBeneficiary3 = old('beneficiary3');
                                    $prevBeneficiary3Age = old('beneficiary3age');
                                @endphp
                                <label for="beneficiary3" class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 3 (Age)</label>
                                <div class="flex gap-3">
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="beneficiary3" name="beneficiary3" value="{{ $prevBeneficiary3 }}" />
                                    <input type="number" class="w-20 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="beneficiary3age" name="beneficiary3age" maxlength="3" value="{{ $prevBeneficiary3Age }}" placeholder="Age" />
                                </div>
                            </div>
                            <div>
                                @php
                                    $prevBeneficiary4 = old('beneficiary4');
                                    $prevBeneficiary4Age = old('beneficiary4age');
                                @endphp
                                <label for="beneficiary4" class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 4 (Age)</label>
                                <div class="flex gap-3">
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="beneficiary4" name="beneficiary4" value="{{ $prevBeneficiary4 }}" />
                                    <input type="number" class="w-20 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="beneficiary4age" name="beneficiary4age" maxlength="3" value="{{ $prevBeneficiary4Age }}" placeholder="Age" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            <!-- Submit Button -->
            <!-- Submit Button / Footer -->
            <div class="bg-white rounded-xl shadow-lg p-6 mt-6 flex justify-end">
                <button type="submit" id="submitBtn" class="w-full md:w-auto px-12 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Submit Client Information
                    </span>
                </button>
            </div>
        </form>
    </div>

    <style>
        .searchable-dropdown {
            position: relative;
        }
        .searchable-dropdown .dropdown-list {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 50;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
            max-height: 220px;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .searchable-dropdown.open .dropdown-list {
            display: block;
        }
        .searchable-dropdown.open input[type="text"] {
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99,102,241,0.15);
        }
        .dropdown-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            cursor: pointer;
            font-size: 0.875rem;
            color: #374151;
            transition: background 0.15s;
        }
        .dropdown-item:hover, .dropdown-item.highlighted {
            background: #f0fdf4;
        }
        .dropdown-item .item-label {
            font-weight: 500;
        }
        .dropdown-item .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            background: #dcfce7;
            color: #16a34a;
            white-space: nowrap;
        }
        .dropdown-item-loading, .dropdown-item-empty {
            padding: 12px 16px;
            text-align: center;
            color: #9ca3af;
            font-size: 0.875rem;
        }
    </style>

    <script>
        console.log('✅ SCRIPT TAG LOADED - BEFORE DOMContentLoaded');
        
        // Validation script - runs BEFORE external JS to ensure it captures submit event
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Validation script loaded - DOMContentLoaded fired');
            console.log('Current time:', new Date().toISOString());
            
            const form = document.getElementById('clientForm');
            const submitBtn = document.getElementById('submitBtn');
            
            console.log('Form element:', form);
            console.log('Submit button:', submitBtn);
            
            if (!form) {
                console.error('❌ FORM NOT FOUND!');
                return;
            }
            
            if (!submitBtn) {
                console.error('❌ SUBMIT BUTTON NOT FOUND!');
                return;
            }

            // Required fields configuration
            const requiredFields = [
                { id: 'contractno', name: 'Contract No.', section: 'CONTRACT' },
                { id: 'package', name: 'Package', section: 'CONTRACT' },
                { id: 'paymentTerm', name: 'Payment Term', section: 'CONTRACT' },
                { id: 'region', name: 'Region', section: 'CONTRACT' },
                { id: 'branch', name: 'Branch', section: 'CONTRACT' },
                // FSA (recruitedBy) is optional - removed from required fields
                { id: 'downpaymentType', name: 'Downpayment Type', section: 'PAYMENT' },
                { id: 'paymentAmount', name: 'Payment Amount', section: 'PAYMENT' },
                { id: 'orSeriesCode', name: 'O.R Series Code', section: 'PAYMENT' },
                { id: 'orNumber', name: 'O.R Number', section: 'PAYMENT' },
                { id: 'paymentMethod', name: 'Payment Method', section: 'PAYMENT' },
                { id: 'paymentDate', name: 'Payment Date', section: 'PAYMENT' },
                { id: 'lastName', name: 'Last Name', section: 'PERSONAL' },
                { id: 'firstName', name: 'First Name', section: 'PERSONAL' },
                { id: 'gender', name: 'Gender', section: 'PERSONAL' },
                { id: 'birthDate', name: 'Birth Date', section: 'PERSONAL' },
                { id: 'addressProvince', name: 'Province', section: 'PERSONAL' },
                { id: 'addressCity', name: 'City', section: 'PERSONAL' },
                { id: 'addressBarangay', name: 'Barangay', section: 'PERSONAL' }
                // mobileNumber removed from required fields - handled by contact validation
            ];

            // Form submission handler
            form.addEventListener('submit', function(e) {
                console.log('=== FORM SUBMISSION STARTED ===');
                console.log('Timestamp:', new Date().toISOString());
                console.log('Form element:', form);
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
                
                // Validate contact information first
                const contactValidation = validateContactInfo();
                if (!contactValidation.isValid) {
                    e.preventDefault();
                    showSwiftModal('Contact Information Required', contactValidation.errorMessage, 'warning');
                    return false;
                }
                
                const errors = [];
                const formData = new FormData(form);
                
                console.log('\n--- ALL FORM FIELDS (Raw) ---');
                const allInputs = form.querySelectorAll('input, select, textarea');
                console.log(`Total form elements found: ${allInputs.length}`);
                allInputs.forEach(input => {
                    const value = input.value || '';
                    const type = input.type || input.tagName.toLowerCase();
                    console.log(`[${type}] ${input.name || input.id || 'unnamed'}: "${value}" ${input.disabled ? '(DISABLED)' : ''} ${input.readOnly ? '(READONLY)' : ''}`);
                });

                console.log('\n--- OR VALIDATION FIELDS ANALYSIS ---');
                const contractNo = document.getElementById('contractno')?.value;
                const orSeriesCode = document.getElementById('orSeriesCode')?.value;
                const orNumber = document.getElementById('orNumber')?.value;
                const paymentType = document.querySelector('input[name="downpaymenttype"]:checked')?.value;
                const region = document.getElementById('region')?.value;
                const branch = document.getElementById('branch')?.value;
                
                console.log('🔍 OR VALIDATION INPUT DATA:');
                console.log(`  - Contract Number: "${contractNo}"`);
                console.log(`  - OR Series Code: "${orSeriesCode}"`);
                console.log(`  - OR Number: "${orNumber}"`);
                console.log(`  - Payment Type: "${paymentType}"`);
                console.log(`  - Region: "${region}"`);
                console.log(`  - Branch: "${branch}"`);
                console.log('');
                console.log('📋 OR VALIDATION REQUIREMENTS:');
                console.log(`  - OR must exist in tblorbatch with SeriesCode="${orSeriesCode}" AND ORNumber="${orNumber}"`);
                console.log(`  - OR must have Status="1" (available)`);
                console.log(`  - OR must have same RegionId="${region}" and BranchId="${branch}"`);
                console.log(`  - OR Type must match Payment Type: ${paymentType === 'Partial' ? '2' : '1'}`);
                console.log(`  - Contract "${contractNo}" must exist and be available in same region/branch`);

                console.log('\n--- ADDRESS FIELDS DETAILED ANALYSIS ---');
                const addressFields = ['addressRegion', 'addressProvince', 'addressCity', 'addressBarangay'];
                addressFields.forEach(fieldId => {
                    const element = document.getElementById(fieldId);
                    if (element) {
                        console.log(`${fieldId}:`);
                        console.log(`  - Element found: YES`);
                        console.log(`  - Name attribute: "${element.name}"`);
                        console.log(`  - Value: "${element.value}"`);
                        console.log(`  - Selected option text: "${element.options ? element.options[element.selectedIndex]?.text : 'N/A'}"`);
                        console.log(`  - Options count: ${element.options ? element.options.length : 'N/A'}`);
                    } else {
                        console.log(`${fieldId}: ELEMENT NOT FOUND`);
                    }
                });
                
                console.log('\n--- Checking Required Fields ---');
                
                // Validate required fields
                requiredFields.forEach(field => {
                    // Try to get element by ID, or by name if ID doesn't exist (for dynamic elements)
                    let element = document.getElementById(field.id);
                    if (!element) {
                        element = document.querySelector(`[name="${field.id}"]`) || 
                                  document.querySelector(`[name="${field.id.toLowerCase()}"]`);
                    }
                    
                    if (!element) {
                        console.log(`⚠️ Field: ${field.name} (${field.id})`);
                        console.log(`  Element NOT FOUND in DOM!`);
                        errors.push({
                            field: field.name,
                            section: field.section,
                            element: null,
                            message: 'Field element not found in form'
                        });
                        return;
                    }
                    
                    const value = element.value.trim();
                    const elementType = element.type || element.tagName.toLowerCase();
                    
                    console.log(`Field: ${field.name} (${field.id})`);
                    console.log(`  Type: ${elementType}`);
                    console.log(`  Value: "${value}"`);
                    console.log(`  Value Length: ${value.length}`);
                    console.log(`  Is Empty: ${!value}`);
                    console.log(`  Is Zero: ${value === '0'}`);
                    console.log(`  Element Disabled: ${element.disabled}`);
                    console.log(`  Element ReadOnly: ${element.readOnly}`);
                    
                    // Remove previous error styling
                    element.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                    
                    // Skip validation for readonly fields (they're auto-calculated)
                    if (element.readOnly) {
                        console.log(`  ⏭️ SKIPPED - Field is readonly`);
                        return;
                    }
                    
                    if (!value || value === '0') {
                        errors.push({
                            field: field.name,
                            section: field.section,
                            element: element,
                            value: value
                        });
                        console.log(`  ❌ VALIDATION FAILED - Empty or zero value`);
                        
                        // Add error styling
                        element.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                    } else {
                        console.log(`  VALIDATION PASSED`);
                    }
                });

                // Validate mobile number format
                const mobileNumber = document.getElementById('mobileNumber');
                if (mobileNumber) {
                    const mobileValue = mobileNumber.value.trim();
                    console.log(`\n--- Mobile Number Validation ---`);
                    console.log(`  Value: "${mobileValue}"`);
                    console.log(`  Length: ${mobileValue.length}`);
                    
                    // Remove spaces for validation
                    const cleanMobileValue = mobileValue.replace(/\s/g, '');
                    console.log(`  Cleaned Value: "${cleanMobileValue}"`);
                    console.log(`  Cleaned Length: ${cleanMobileValue.length}`);
                    console.log(`  Starts with 9: ${cleanMobileValue.charAt(0) === '9'}`);
                    console.log(`  Is all digits: ${/^\d+$/.test(cleanMobileValue)}`);
                    console.log(`  Regex test (^9\\d{9}$): ${/^9\d{9}$/.test(cleanMobileValue)}`);
                    
                    if (mobileValue && (cleanMobileValue.length !== 10 || !/^9\d{9}$/.test(cleanMobileValue))) {
                        errors.push({
                            field: 'Mobile Number',
                            section: 'CONTACT',
                            element: mobileNumber,
                            message: 'Must be 10 digits starting with 9 (e.g., 9123456789)',
                            value: mobileValue
                        });
                        mobileNumber.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                        console.log(`  INVALID FORMAT`);
                    } else if (mobileValue) {
                        console.log(`  VALID FORMAT`);
                    }
                }

                console.log('\n--- Validation Summary ---');
                console.log(`Total Errors Found: ${errors.length}`);
                
                if (errors.length > 0) {
                    e.preventDefault();
                    console.log('\n❌ FORM SUBMISSION BLOCKED');
                    console.log('\n--- DETAILED ERROR LIST ---');
                    errors.forEach((err, index) => {
                        console.log(`${index + 1}. ${err.section} > ${err.field}`);
                        console.log(`   Value: "${err.value || '(empty)'}"`);
                        console.log(`   Message: ${err.message || 'This field is required'}`);
                    });
                    
                    // Scroll to first error
                    if (errors[0].element) {
                        console.log(`\nScrolling to first error: ${errors[0].field}`);
                        errors[0].element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        setTimeout(() => {
                            errors[0].element.focus();
                        }, 500);
                    }
                } else {
                    console.log('\n✓✓✓ ALL VALIDATIONS PASSED ✓✓✓');
                    console.log('Form is being submitted to:', form.action);
                    
                    // Show loading state
                    const submitBtn = document.getElementById('submitBtn');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="flex items-center justify-center"><svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Submitting...</span>';

                    // Normalize key fields before building FormData for submission
                    const mobileNumberEl = document.getElementById('mobileNumber');
                    if (mobileNumberEl && mobileNumberEl.value) {
                        mobileNumberEl.value = mobileNumberEl.value.replace(/\s/g, '');
                    }
                    if (typeof normalizeCustomEmail === 'function') {
                        normalizeCustomEmail();
                    }
                    
                    // Convert FormData to JSON object
                    const formDataObj = {};
                    const submitFormData = new FormData(form);
                    for (let [key, value] of submitFormData.entries()) {
                        formDataObj[key] = value;
                    }
                    
                    console.log('\n=== FORM DATA (JSON FORMAT) ===');
                    console.log(JSON.stringify(formDataObj, null, 2));
                    
                    console.log('\n=== FORM DATA (LIST FORMAT) ===');
                    let fieldCount = 0;
                    for (let [key, value] of submitFormData.entries()) {
                        fieldCount++;
                        console.log(`${fieldCount}. ${key}: "${value}"`);
                    }
                    console.log(`\nTotal fields being submitted: ${fieldCount}`);
                    console.log('=== FORM WILL NOW SUBMIT ===\n');
                    
                    // Handle form submission with fetch for better error handling
                    e.preventDefault();
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: submitFormData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value
                        }
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers.get('content-type'));
                        
                        // Check content-type before parsing
                        const contentType = response.headers.get('content-type');
                        
                        if (contentType && contentType.includes('application/json')) {
                            return response.json().then(data => {
                                if (!response.ok) {
                                    // Handle validation errors from JSON response
                                    throw { data: data, status: response.status };
                                }
                                return data;
                            });
                        } else {
                            // Server returned HTML (likely validation redirect or error)
                            return response.text().then(text => {
                                console.log('Server returned HTML:', text.substring(0, 500));
                                throw new Error('Server returned HTML instead of JSON. This usually means a validation error or session issue.');
                            });
                        }
                    })
                    .then(data => {
                        // Success
                        clearFormData();
                        showSwiftModal('Success!', 'Client information submitted successfully!\n\nYour submission has been received and is now pending approval.', 'success');
                        
                        // Optionally redirect to a success page
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            // Reset form or redirect as needed
                            form.reset();
                        }
                    })
                    .catch(error => {
                        console.error('Submission error:', error);
                        
                        let errorMessage = 'An unexpected error occurred.';
                        let firstErrorField = null;
                        
                        // Handle different error types
                        if (error.data) {
                            // Server returned JSON with validation errors
                            if (error.data.message) {
                                errorMessage = error.data.message;
                            } else if (error.data.errors) {
                                // Laravel validation errors
                                const fieldLabels = {
                                    contractno: 'Contract No',
                                    package: 'Package',
                                    packageprice: 'Package Price',
                                    paymentterm: 'Payment Term',
                                    termamount: 'Term Amount',
                                    region: 'Region',
                                    branch: 'Branch',
                                    recruitedby: 'Recruited By',
                                    downpaymenttype: 'Downpayment Type',
                                    paymentamount: 'Payment Amount',
                                    orseriescode: 'OR Series Code',
                                    ornumber: 'OR Number',
                                    paymentdate: 'Payment Date',
                                    lastname: 'Last Name',
                                    firstname: 'First Name',
                                    gender: 'Gender',
                                    birthdate: 'Birth Date',
                                    age: 'Age',
                                    bestplacetocollect: 'Best Place To Collect',
                                    besttimetocollect: 'Best Time To Collect',
                                    province: 'Province',
                                    city: 'City',
                                    barangay: 'Barangay',
                                    mobilenumber: 'Mobile Number',
                                    mobilenetwork: 'Mobile Network',
                                    mobileno: 'Mobile No',
                                    email: 'Email',
                                    emailaddress: 'Email Domain',
                                    customemaildomain: 'Custom Email Domain',
                                };

                                const lines = [];
                                Object.entries(error.data.errors).forEach(([field, messages]) => {
                                    if (!firstErrorField) firstErrorField = field;
                                    const label = fieldLabels[field] || field;
                                    (messages || []).forEach(msg => {
                                        lines.push(`${label}: ${msg}`);
                                    });
                                });

                                errorMessage = lines.length
                                    ? 'Validation errors:\n' + lines.join('\n')
                                    : 'Validation failed.';
                            }
                        } else if (error.message) {
                            if (error.message.includes('Unexpected token') || error.message.includes('is not valid JSON')) {
                                errorMessage = 'The server returned an unexpected response. Please try again or contact support.';
                            } else if (error.message.includes('HTML instead of JSON')) {
                                errorMessage = 'Session expired or validation failed. Please refresh the page and try again.';
                            } else {
                                errorMessage = error.message;
                            }
                        }
                        
                        showSwiftModal('Submission Failed', 
                            'Please check your input and try again.\n\n' + errorMessage, 
                            'error');

                        if (firstErrorField) {
                            const fieldSelectors = {
                                contractno: '#contractno',
                                package: '#package',
                                packageprice: '#packagePrice',
                                paymentterm: '#paymentTerm',
                                termamount: '#termAmount',
                                region: '#region',
                                branch: '#branch',
                                recruitedby: '#recruitedBy',
                                downpaymenttype: '#downpaymentType',
                                paymentamount: '#paymentAmount',
                                orseriescode: '#orSeriesCode',
                                ornumber: '#orNumber',
                                paymentdate: '#paymentDate',
                                lastname: '#lastName',
                                firstname: '#firstName',
                                gender: '#gender',
                                birthdate: '#birthDate',
                                age: '#age',
                                bestplacetocollect: '#bestPlaceToCollect',
                                besttimetocollect: '#bestTimeToCollect',
                                province: '#addressProvince',
                                city: '#addressCity',
                                barangay: '#addressBarangay',
                                mobilenumber: '#mobileNumber',
                                email: '#email',
                                emailaddress: '#emailDomainSelect',
                                customemaildomain: '#customEmailDomain',
                            };

                            const selector = fieldSelectors[firstErrorField];
                            const el = selector ? document.querySelector(selector) : null;
                            if (el) {
                                el.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                setTimeout(() => {
                                    try { el.focus(); } catch (e) {}
                                }, 500);
                            }
                        }
                        
                        // Restore button state
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    })
                    .finally(() => {
                        // Restore button state if not redirected
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }, 2000);
                    });
                }
            });

            // Remove error styling on input
            document.querySelectorAll('input, select, textarea').forEach(element => {
                element.addEventListener('input', function() {
                    this.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                });
                
                element.addEventListener('change', function() {
                    this.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                });
            });
            
            // Add click listener as backup
            submitBtn.addEventListener('click', function(e) {
                console.log('🖱️ Submit button CLICKED');
                console.log('Event:', e);
            });

            console.log('✓ Client form validation initialized');
            console.log(`Monitoring ${requiredFields.length} required fields`);
            
            // Initialize email domain toggle on page load
            toggleCustomEmailDomain();
        });

        // Email domain toggle functionality (based on staff form pattern)
        function toggleCustomEmailDomain() {
            const emailDomainSelect = document.getElementById('emailDomainSelect');
            const customEmailDomain = document.getElementById('customEmailDomain');
            if (!emailDomainSelect || !customEmailDomain) {
                return;
            }

            if (emailDomainSelect.value === 'others') {
                customEmailDomain.style.display = 'block';
            } else {
                customEmailDomain.style.display = 'none';
                customEmailDomain.value = '';
            }
        }

        function normalizeCustomEmail() {
            const emailInput = document.getElementById('email');
            const emailDomainSelect = document.getElementById('emailDomainSelect');
            const customDomainInput = document.getElementById('customEmailDomain');

            if (!emailInput || !emailDomainSelect || !customDomainInput) {
                return;
            }

            if (emailDomainSelect.value !== 'others') {
                return;
            }

            const rawCustomValue = customDomainInput.value.trim();

            if (!rawCustomValue) {
                return;
            }

            if (!rawCustomValue.includes('@')) {
                return;
            }

            const parts = rawCustomValue.split('@').map(part => part.trim()).filter(Boolean);

            if (parts.length !== 2) {
                return;
            }

            const [localPart, domainPart] = parts;

            if (!localPart || !domainPart) {
                return;
            }

            emailInput.value = localPart;
            customDomainInput.value = domainPart;
        }

        // Swift-Style Modal Functions
        function showSwiftModal(title, message, type = 'error', buttons = []) {
            const modal = document.getElementById('swiftModal');
            const iconDiv = document.getElementById('swiftModalIcon');
            const titleEl = document.getElementById('swiftModalTitle');
            const messageEl = document.getElementById('swiftModalMessage');
            const actionsEl = document.getElementById('swiftModalActions');

            // Set icon based on type
            const icons = {
                error: `<svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>`,
                success: `<svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>`,
                warning: `<svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>`,
                info: `<svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>`
            };

            iconDiv.innerHTML = icons[type] || icons.error;
            titleEl.textContent = title;
            messageEl.textContent = message;

            // Default OK button if no custom buttons provided
            if (buttons.length === 0) {
                actionsEl.innerHTML = `
                    <button onclick="hideSwiftModal()" class="w-full py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition duration-200">
                        OK
                    </button>
                `;
            } else {
                actionsEl.innerHTML = buttons.map(btn => `
                    <button onclick="${btn.action}; hideSwiftModal();" class="w-full py-3 px-6 ${btn.class} font-semibold rounded-xl transition duration-200">
                        ${btn.text}
                    </button>
                `).join('');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideSwiftModal() {
            const modal = document.getElementById('swiftModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Close modal on backdrop click
        document.getElementById('swiftModal')?.addEventListener('click', function(e) {
            if (e.target === this) hideSwiftModal();
        });

        // Enhanced email domain event listeners
        document.addEventListener('DOMContentLoaded', function () {
            const emailDomainSelect = document.getElementById('emailDomainSelect');
            const customDomainInput = document.getElementById('customEmailDomain');
            
            if (emailDomainSelect) {
                emailDomainSelect.addEventListener('change', toggleCustomEmailDomain);
            }

            if (customDomainInput) {
                ['blur', 'change'].forEach(evt => {
                    customDomainInput.addEventListener(evt, normalizeCustomEmail);
                });
                customDomainInput.addEventListener('input', function () {
                    if (this.value.includes('@')) {
                        normalizeCustomEmail();
                    }
                });
            }

            // === Searchable Dropdown Component ===
            class SearchableDropdown {
                constructor(wrapperId, searchId, hiddenId, listId, placeholder) {
                    this.wrapper = document.getElementById(wrapperId);
                    this.searchInput = document.getElementById(searchId);
                    this.hiddenInput = document.getElementById(hiddenId);
                    this.list = document.getElementById(listId);
                    this.placeholder = placeholder || 'Select...';
                    this.items = [];
                    this.onSelect = null;
                    this._setupEvents();
                }

                _setupEvents() {
                    // Guard against null elements
                    if (!this.searchInput || !this.hiddenInput || !this.wrapper || !this.list) {
                        console.warn('⚠️ [SearchableDropdown] Required elements not found, skipping event setup');
                        return;
                    }
                    
                    // Open on focus - clear the search text so all items show
                    this.searchInput.addEventListener('focus', () => {
                        console.log(`🔍 [Dropdown Focus] ${this.hiddenInput?.id || 'unknown'} | Current Value: ${this.hiddenInput?.value || ''}`);
                        // If a value was previously selected, clear the text to show all options
                        if (this.hiddenInput?.value) {
                            this.searchInput.value = '';
                        }
                        this.wrapper.classList.add('open');
                        this._render(this.searchInput.value);
                    });

                    // On blur, restore selected value text if user didn't pick a new one
                    this.searchInput.addEventListener('blur', () => {
                        setTimeout(() => {
                            if (!this.wrapper?.classList?.contains('open')) {
                                const currentValue = this.hiddenInput?.value;
                                if (currentValue) {
                                    const item = this.items.find(i => String(i.value) === String(currentValue));
                                    if (item) {
                                        this.searchInput.value = item.label;
                                        console.log(`🔄 [Dropdown Blur] Restored label for ${this.hiddenInput?.id || 'unknown'}: ${item.label}`);
                                    }
                                } else {
                                    this.searchInput.value = '';
                                }
                            }
                        }, 200);
                    });

                    // Filter on type - DO NOT clear hiddenInput.value here
                    // Only clear it if the user explicitly clears the search input (optional)
                    // The hidden value should only change when an item is CLICKED
                    this.searchInput.addEventListener('input', () => {
                        console.log(`⌨️ [Dropdown Input] ${this.hiddenInput?.id || 'unknown'} | Search: ${this.searchInput.value}`);
                        this._render(this.searchInput.value);
                    });

                    // Close on outside click
                    document.addEventListener('click', (e) => {
                        if (!this.wrapper.contains(e.target)) {
                            this.wrapper.classList.remove('open');
                        }
                    });
                }

                setItems(items) {
                    // items: [{value, label, badge?}]
                    this.items = items;
                    this._render('');
                }

                setLoading() {
                    if (this.list) this.list.innerHTML = '<div class="dropdown-item-loading">Loading...</div>';
                    if (this.wrapper) this.wrapper.classList.add('open');
                }

                setValue(val) {
                    if (!this.hiddenInput || !this.searchInput) return;
                    this.hiddenInput.value = val || '';
                    const item = this.items.find(i => String(i.value) === String(val));
                    if (item) {
                        this.searchInput.value = item.label;
                        console.log(`✅ [Dropdown] Set value for ${this.hiddenInput?.id || 'unknown'}: "${item.label}" -> hidden field: "${val}"`);
                    } else if (val && this.items.length > 0) {
                        // Items are loaded but value not found — clear to avoid stale display
                        console.warn(`⚠️ [Dropdown] Value "${val}" not found in items list for ${this.hiddenInput?.id || 'unknown'}.`);
                        this.searchInput.value = '';
                        this.hiddenInput.value = '';
                    }
                    // If items.length === 0 (not yet loaded), do nothing — keep current text visible
                }

                clear() {
                    if (this.hiddenInput) this.hiddenInput.value = '';
                    if (this.searchInput) this.searchInput.value = '';
                    this.items = [];
                    if (this.list) this.list.innerHTML = '';
                }

                _render(filter) {
                    if (!this.list) return;
                    const q = (filter || '').toLowerCase();
                    const filtered = this.items.filter(i => i.label.toLowerCase().includes(q));
                    this.list.innerHTML = '';

                    if (filtered.length === 0) {
                        this.list.innerHTML = '<div class="dropdown-item-empty">No results found</div>';
                        return;
                    }

                    filtered.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'dropdown-item';
                        
                        let html = `<span class="item-label">${item.label}</span>`;
                        if (item.badge) {
                            html += `<span class="badge">${item.badge}</span>`;
                        }
                        div.innerHTML = html;

                        div.addEventListener('click', () => {
                            if (this.searchInput) this.searchInput.value = item.label;
                            if (this.hiddenInput) this.hiddenInput.value = item.value;
                            if (this.wrapper) this.wrapper.classList.remove('open');
                            console.log(`✅ [Dropdown] Selected: "${item.label}" -> hidden field "${this.hiddenInput?.id || 'unknown'}" = "${item.value}"`);
                            if (this.onSelect) this.onSelect(item.value, item);
                        });

                        this.list.appendChild(div);
                    });
                }
            }

            // === Initialize searchable dropdowns ===
            const regionSelect = document.getElementById('region');
            const branchSelect = document.getElementById('branch');
            const downpaymentTypeSelect = document.getElementById('downpaymentType');
            const prevContractNo = document.getElementById('prevContractNo').value;
            const prevOrSeriesCode = document.getElementById('prevOrSeriesCode').value;
            const prevOrNumber = document.getElementById('prevOrNumber').value;

            const contractDropdown = new SearchableDropdown('contractNoWrapper', 'contractNoSearch', 'contractno', 'contractNoList', 'Type or select Contract No.');
            const orSeriesDropdown = new SearchableDropdown('orSeriesWrapper', 'orSeriesSearch', 'orSeriesCode', 'orSeriesList', 'Type or select O.R Series Code');
            const orNumberDropdown = new SearchableDropdown('orNumberWrapper', 'orNumberSearch', 'orNumber', 'orNumberList', 'Type or select O.R Number');

            // Set initial values from old() if any
            if (prevContractNo) contractDropdown.setValue(prevContractNo);
            if (prevOrSeriesCode) orSeriesDropdown.setValue(prevOrSeriesCode);
            if (prevOrNumber) orNumberDropdown.setValue(prevOrNumber);

            // === Contract fetch ===
            // Store raw contract data for lookup when selecting
            let contractRawData = [];

            function fetchContracts(regionId, branchId = null, selectedContract = null) {
                const selectedRegionText = regionSelect && regionSelect.options[regionSelect.selectedIndex] ? regionSelect.options[regionSelect.selectedIndex].text : 'N/A';
                const selectedBranchText = branchSelect && branchSelect.options[branchSelect.selectedIndex] ? branchSelect.options[branchSelect.selectedIndex].text : 'All Branches';
                console.log('📋 [fetchContracts] === Contract Fetch ===');
                console.log(`📋 [fetchContracts] Region: ${selectedRegionText} (ID: ${regionId})`);
                console.log(`📋 [fetchContracts] Branch: ${selectedBranchText} (ID: ${branchId || 'N/A'})`);
                contractDropdown.setLoading();

                let url = `/get-available-contracts?region_id=${regionId}`;
                if (branchId) url += `&branch_id=${branchId}`;

                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        console.log('📋 [fetchContracts] Raw API response:', data);
                        console.log(`📋 [fetchContracts] Total contracts found: ${data.length}`);
                        contractRawData = data; // Store for lookup
                        
                        const items = data.map(c => {
                            let badge = 'available';
                            // If contract belongs to a different branch than selected, show branch name in badge
                            if (branchId && String(c.BranchId) !== String(branchId) && c.BranchName) {
                                badge = `${c.BranchName}`;
                            }
                            return {
                                value: c.ContractNumber,
                                label: String(c.ContractNumber),
                                badge: badge
                            };
                        });
                        console.log(`📋 [fetchContracts] Setting ${items.length} items to dropdown`);
                        contractDropdown.setItems(items);

                        // Sync logic: Force validate the current or requested value
                        const valueToValidate = selectedContract || (contractDropdown.hiddenInput?.value || '');
                        if (valueToValidate) {
                            const found = data.find(c => String(c.ContractNumber) === String(valueToValidate));
                            if (found) {
                                contractDropdown.setValue(valueToValidate);
                            } else {
                                // Contract not found in available list — keep text visible so user
                                // can see what they had, but clear the hidden value so it's not submitted
                                if (contractDropdown.hiddenInput) contractDropdown.hiddenInput.value = '';
                                // Keep searchInput text as-is (already set from old() value)
                                console.warn('⚠️ [fetchContracts] Previously selected contract not found in available list:', valueToValidate);
                            }
                        } else if (items.length > 0) {
                            // Auto-select first contract if none selected
                            const firstContract = items[0].value;
                            contractDropdown.setValue(firstContract);
                            console.log(`✅ [fetchContracts] Auto-selected first contract: ${firstContract}`);
                        }
                    })
                    .catch(err => {
                        console.error('❌ [fetchContracts] Error:', err);
                        contractDropdown.setItems([]);
                    });
            }

            // === OR Series fetch ===
            function fetchOrSeries(selectedSeries = null) {
                const regionId = regionSelect ? regionSelect.value : '';
                const branchId = branchSelect ? branchSelect.value : '';
                const paymentType = downpaymentTypeSelect ? downpaymentTypeSelect.value : 'Partial';
                const selectedRegionText = regionSelect ? regionSelect.options[regionSelect.selectedIndex].text : 'N/A';
                const selectedBranchText = branchSelect ? branchSelect.options[branchSelect.selectedIndex].text : 'N/A';
                const contractNoElement = document.getElementById('contractno');
                const contractNo = contractNoElement ? contractNoElement.value : '';

                console.log('🔖 [fetchOrSeries] === O.R. Series Fetch ===');
                console.log(`🔖 [fetchOrSeries] Region: ${selectedRegionText} (ID: ${regionId})`);
                console.log(`🔖 [fetchOrSeries] Branch: ${selectedBranchText} (ID: ${branchId})`);
                console.log(`🔖 [fetchOrSeries] Payment Type: ${paymentType}`);
                console.log(`🔖 [fetchOrSeries] Contract No: ${contractNo}`);
                console.log(`🔖 [fetchOrSeries] API URL: /get-or-series-by-branch?regionId=${regionId}&branchId=${branchId}&paymentType=${paymentType}`);

                if (!regionId) {
                    orSeriesDropdown.setItems([]);
                    return;
                }

                orSeriesDropdown.setLoading();
                orNumberDropdown.clear();

                fetch(`/get-or-series-by-branch?regionId=${regionId}&branchId=${branchId}&paymentType=${paymentType}`)
                    .then(r => r.json())
                    .then(data => {
                        console.log('🔖 [fetchOrSeries] Raw API response:', data);
                        console.log(`🔖 [fetchOrSeries] Total OR Series found: ${data.length}`);
                        data.forEach(s => {
                            console.log(`🔖 [fetchOrSeries]   Series: ${s.SeriesCode} | Available: ${s.available_count} | Total: ${s.total_count}`);
                        });
                        const items = data.map(s => {
                            const typeLabel = s.Type == '1' ? 'Std' : 'Part';
                            return {
                                value: s.SeriesCode,
                                label: String(s.SeriesCode),
                                badge: `${s.available_count} avail (${typeLabel})`
                            };
                        });
                        orSeriesDropdown.setItems(items);

                        // Sync logic: Force validate the current or requested value
                        const valueToValidate = selectedSeries || orSeriesDropdown.hiddenInput.value;
                        if (valueToValidate) {
                            const found = data.find(s => String(s.SeriesCode) === String(valueToValidate));
                            if (found) {
                                orSeriesDropdown.setValue(valueToValidate);
                                fetchOrNumbers(valueToValidate, prevOrNumber);
                                console.log(`✅ [Sync] O.R. Series ${valueToValidate} validated.`);
                            } else {
                                console.log(`⚠️ [Sync] O.R. Series ${valueToValidate} IS NOT in this branch list. Clearing.`);
                                orSeriesDropdown.clear();
                                orNumberDropdown.clear();
                            }
                        }
                    })
                    .catch(err => {
                        console.error('❌ [fetchOrSeries] Error:', err);
                        orSeriesDropdown.setItems([]);
                    });
            }

            // === OR Numbers fetch ===
            function fetchOrNumbers(seriesCode, selectedNumber = null) {
                const regionId = regionSelect ? regionSelect.value : '';
                const branchId = branchSelect ? branchSelect.value : '';
                const paymentType = downpaymentTypeSelect ? downpaymentTypeSelect.value : 'Partial';

                console.log('🔢 [fetchOrNumbers] === O.R. Numbers Fetch ===');
                console.log(`🔢 [fetchOrNumbers] Series Code: ${seriesCode}`);
                console.log(`🔢 [fetchOrNumbers] selectedNumber: "${selectedNumber}" | Type: ${typeof selectedNumber} | Empty: ${selectedNumber === ''}`);
                console.log(`🔢 [fetchOrNumbers] RegionId: ${regionId}, BranchId: ${branchId}, PaymentType: ${paymentType}`);

                if (!seriesCode) {
                    orNumberDropdown.setItems([]);
                    return;
                }

                orNumberDropdown.setLoading();

                fetch(`/get-or-numbers?seriesCode=${seriesCode}&regionId=${regionId}&branchId=${branchId}&paymentType=${paymentType}`)
                    .then(r => r.json())
                    .then(data => {
                        console.log('🔢 [fetchOrNumbers] Raw API response:', data);
                        console.log(`🔢 [fetchOrNumbers] Total OR Numbers available: ${data.length}`);
                        data.forEach(o => {
                            console.log(`🔢 [fetchOrNumbers]   ORNumber: ${o.ORNumber} (ID: ${o.id})`);
                        });
                        const items = data.map(o => ({
                            value: o.ORNumber,
                            label: String(o.ORNumber),
                            badge: 'available'
                        }));
                        orNumberDropdown.setItems(items);
                        if (selectedNumber) {
                            orNumberDropdown.setValue(selectedNumber);
                        } else if (items.length > 0) {
                            // Auto-select first O.R. Number if none selected
                            const firstNumber = items[0].value;
                            orNumberDropdown.setValue(firstNumber);
                            console.log(`✅ [fetchOrNumbers] Auto-selected first O.R. Number: ${firstNumber}`);
                        }
                    })
                    .catch(err => {
                        console.error('❌ [fetchOrNumbers] Error:', err);
                        orNumberDropdown.setItems([]);
                    });
            }

            // === Event listeners for cascading ===
            // Contract No -> refresh OR Series (compatibility filter)
            contractDropdown.onSelect = (value) => {
                // Log full contract details when selected
                const contractInfo = contractRawData.find(c => String(c.ContractNumber) === String(value));
                console.log('\n🎯 [CONTRACT SELECTED] ========================');
                console.log(`🎯 Contract Number: ${value}`);
                if (contractInfo) {
                    console.log(`🎯 Batch Code: ${contractInfo.BatchCode}`);
                    console.log(`🎯 Region ID: ${contractInfo.RegionId}`);
                    console.log(`🎯 Branch ID: ${contractInfo.BranchId}`);
                    console.log(`🎯 Status: ${contractInfo.ContractStatus}`);
                } else {
                    console.log('🎯 ⚠️ Contract details not found in raw data!');
                }
                console.log('🎯 → Now refreshing O.R. Series...');
                console.log('🎯 ============================================\n');
                // When a contract is selected, refresh OR Series to show compatible ones
                fetchOrSeries();
            };

            // OR Series -> OR Numbers
            orSeriesDropdown.onSelect = (value) => {
                console.log(`\n🎯 [OR SERIES SELECTED] Series Code: ${value}`);
                console.log('🎯 → Now fetching O.R. Numbers...\n');
                fetchOrNumbers(value);
            };

            // Region change -> refresh contracts + OR series
            if (regionSelect) {
                regionSelect.addEventListener('change', function() {
                    const regionId = this.value;
                    const branchId = branchSelect ? branchSelect.value : '';
                    if (regionId) {
                        fetchContracts(regionId, branchId);
                        fetchOrSeries();
                    } else {
                        contractDropdown.clear();
                        orSeriesDropdown.clear();
                        orNumberDropdown.clear();
                    }
                });
            }

            // Branch change -> refresh contracts + OR series
            if (branchSelect) {
                branchSelect.addEventListener('change', function() {
                    const regionId = regionSelect ? regionSelect.value : '';
                    const branchId = this.value;
                    if (regionId) {
                        fetchContracts(regionId, branchId);
                    }
                    fetchOrSeries();
                });
            }

            // Downpayment Type change -> refresh OR series (type determines OR batch type)
            if (downpaymentTypeSelect) {
                downpaymentTypeSelect.addEventListener('change', function() {
                    fetchOrSeries();
                });
            }

            // === Initial load ===
            if (regionSelect && regionSelect.value) {
                const branchId = branchSelect ? branchSelect.value : '';
                fetchContracts(regionSelect.value, branchId, prevContractNo);
                fetchOrSeries(prevOrSeriesCode);
            }
        });
    </script>
    <script src="{{ asset('js/client-create.js') }}"></script>
    <script src="{{ asset('js/client-address-cascading.js') }}"></script>

    <script>
        // Home Address - Same as Current Address functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sameAsCurrentCheckbox = document.getElementById('sameAsCurrentAddress');
            const homeAddressFields = document.getElementById('homeAddressFields');
            
            // Home address field selects
            const homeRegionSelect = document.getElementById('homeRegion');
            const homeProvinceSelect = document.getElementById('homeProvince');
            const homeCitySelect = document.getElementById('homeCity');
            const homeBarangaySelect = document.getElementById('homeBarangay');
            const homeZipcodeInput = document.getElementById('homeZipcode');
            const homeStreetInput = document.getElementById('homeStreet');
            
            // Current address field selects
            const addressRegionSelect = document.getElementById('addressRegion');
            const addressProvinceSelect = document.getElementById('addressProvince');
            const addressCitySelect = document.getElementById('addressCity');
            const addressBarangaySelect = document.getElementById('addressBarangay');
            const zipcodeInput = document.getElementById('zipcode');
            const streetInput = document.getElementById('street');
            
            function copyCurrentToHome() {
                // Copy region
                homeRegionSelect.innerHTML = addressRegionSelect.innerHTML;
                homeRegionSelect.value = addressRegionSelect.value;
                
                // Copy province
                homeProvinceSelect.innerHTML = addressProvinceSelect.innerHTML;
                homeProvinceSelect.value = addressProvinceSelect.value;
                
                // Copy city
                homeCitySelect.innerHTML = addressCitySelect.innerHTML;
                homeCitySelect.value = addressCitySelect.value;
                
                // Copy barangay
                homeBarangaySelect.innerHTML = addressBarangaySelect.innerHTML;
                homeBarangaySelect.value = addressBarangaySelect.value;
                
                // Copy zipcode and street
                homeZipcodeInput.value = zipcodeInput.value;
                homeStreetInput.value = streetInput.value;
            }
            
            function toggleHomeAddressFields(disabled) {
                const fieldsToToggle = [homeRegionSelect, homeProvinceSelect, homeCitySelect, homeBarangaySelect, homeZipcodeInput, homeStreetInput];
                fieldsToToggle.forEach(field => {
                    if (field) {
                        field.disabled = disabled;
                        if (disabled) {
                            field.classList.add('bg-gray-100', 'cursor-not-allowed');
                        } else {
                            field.classList.remove('bg-gray-100', 'cursor-not-allowed');
                        }
                    }
                });
            }
            
            if (sameAsCurrentCheckbox) {
                sameAsCurrentCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        copyCurrentToHome();
                        toggleHomeAddressFields(true);
                    } else {
                        toggleHomeAddressFields(false);
                    }
                });
                
                // Also listen for changes on current address fields to sync when checkbox is checked
                [addressRegionSelect, addressProvinceSelect, addressCitySelect, addressBarangaySelect, zipcodeInput, streetInput].forEach(field => {
                    if (field) {
                        field.addEventListener('change', function() {
                            if (sameAsCurrentCheckbox.checked) {
                                // Re-copy after a short delay to allow cascading to complete
                                setTimeout(copyCurrentToHome, 500);
                            }
                        });
                    }
                });
            }
            
            // Initialize home address cascading dropdown
            initHomeAddressCascading();
        });
        
        // Home Address Cascading Functionality
        function initHomeAddressCascading() {
            const homeRegionSelect = document.getElementById('homeRegion');
            const homeProvinceSelect = document.getElementById('homeProvince');
            const homeCitySelect = document.getElementById('homeCity');
            const homeBarangaySelect = document.getElementById('homeBarangay');
            const homeZipcodeInput = document.getElementById('homeZipcode');
            
            const oldHomeRegion = document.getElementById('oldHomeRegion')?.value || '';
            const oldHomeProvince = document.getElementById('oldHomeProvince')?.value || '';
            const oldHomeCity = document.getElementById('oldHomeCity')?.value || '';
            const oldHomeBarangay = document.getElementById('oldHomeBarangay')?.value || '';

            console.log('--- Home Address Debug ---');
            console.log('Old Home Region:', oldHomeRegion);
            console.log('Old Home Province:', oldHomeProvince);
            console.log('Old Home City:', oldHomeCity);
            console.log('Old Home Barangay:', oldHomeBarangay);
            
            // On region change
            if (homeRegionSelect) {
                homeRegionSelect.addEventListener('change', function() {
                    const regionCode = this.value;
                    homeProvinceSelect.innerHTML = '<option value="">Select Province</option>';
                    homeCitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                    homeBarangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                    homeZipcodeInput.value = '';
                    
                    if (regionCode) {
                        fetchHomeProvinces(regionCode);
                    }
                });
                
                // Initial load if region exists (for old() values on validation error)
                if (oldHomeRegion) {
                    homeRegionSelect.value = oldHomeRegion;
                    fetchHomeProvinces(oldHomeRegion, oldHomeProvince, oldHomeCity, oldHomeBarangay);
                }
            }
            
            // On province change
            if (homeProvinceSelect) {
                homeProvinceSelect.addEventListener('change', function() {
                    const provinceCode = this.value;
                    homeCitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                    homeBarangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                    homeZipcodeInput.value = '';
                    
                    if (provinceCode) {
                        fetchHomeCities(provinceCode);
                    }
                });
            }
            
            // On city change
            if (homeCitySelect) {
                homeCitySelect.addEventListener('change', function() {
                    const cityCode = this.value;
                    const cityName = this.options[this.selectedIndex]?.text || '';
                    homeBarangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                    
                    if (cityCode) {
                        fetchHomeBarangays(cityCode);
                        fetchHomeZipcode(cityName);
                    }
                });
            }
        }
        
        function fetchHomeProvinces(regionCode, preselectedProvince = '', preselectedCity = '', preselectedBarangay = '') {
            const homeProvinceSelect = document.getElementById('homeProvince');
            
            $.ajax({
                url: '/get-address-provinces',
                method: 'GET',
                data: { regionCode: regionCode },
                success: function(provinces) {
                    homeProvinceSelect.innerHTML = '<option value="">Select Province</option>';
                    let bestMatch = null;
                    let bestMatchScore = 0;

                    provinces.forEach(function(province) {
                        const option = new Option(province.name, province.code);
                        const provinceName = String(province.name).toLowerCase().trim();
                        const selectedName = String(preselectedProvince).toLowerCase().trim();

                        if (preselectedProvince) {
                            let score = 0;
                            if (provinceName === selectedName) score = 100;
                            else if (provinceName.includes(selectedName)) score = 80;
                            else if (selectedName.includes(provinceName)) score = 60;
                            else if (selectedName.length >= 3 && provinceName.includes(selectedName.substring(0, 3))) score = 40;
                            
                            if (String(province.code) == String(preselectedProvince)) score = 100;

                            if (score > bestMatchScore) {
                                bestMatchScore = score;
                                bestMatch = option;
                            }
                        }
                        
                        homeProvinceSelect.appendChild(option);
                    });

                    if (bestMatch && bestMatchScore >= 40) {
                        bestMatch.selected = true;
                    }
                    
                    if (preselectedProvince && homeProvinceSelect.value) {
                        const selectedValue = homeProvinceSelect.value;
                        fetchHomeCities(selectedValue, preselectedCity, preselectedBarangay);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching home provinces:', error);
                }
            });
        }
        
        function fetchHomeCities(provinceCode, preselectedCity = '', preselectedBarangay = '') {
            const homeCitySelect = document.getElementById('homeCity');
            
            $.ajax({
                url: '/get-address-cities',
                method: 'GET',
                data: { provinceCode: provinceCode },
                success: function(cities) {
                    homeCitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                    let bestMatch = null;
                    let bestMatchScore = 0;

                    cities.forEach(function(city) {
                        const option = new Option(city.name, city.code);
                        const cityName = String(city.name).toLowerCase().trim();
                        const selectedName = String(preselectedCity).toLowerCase().trim();

                        if (preselectedCity) {
                            let score = 0;
                            if (cityName === selectedName) score = 100;
                            else if (cityName.includes(selectedName)) score = 80;
                            else if (selectedName.includes(cityName)) score = 60;
                            else if (selectedName.length >= 3 && cityName.includes(selectedName.substring(0, 3))) score = 40;
                            
                            if (String(city.code) == String(preselectedCity)) score = 100;

                            if (score > bestMatchScore) {
                                bestMatchScore = score;
                                bestMatch = option;
                            }
                        }

                        homeCitySelect.appendChild(option);
                    });

                     if (bestMatch && bestMatchScore >= 40) {
                        bestMatch.selected = true;
                    }
                    
                    if (preselectedCity && homeCitySelect.value) {
                        const selectedValue = homeCitySelect.value;
                        const selectedText = homeCitySelect.options[homeCitySelect.selectedIndex].text;
                        fetchHomeBarangays(selectedValue, preselectedBarangay);
                        fetchHomeZipcode(selectedText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching home cities:', error);
                }
            });
        }
        
        function fetchHomeBarangays(cityCode, preselectedBarangay = '') {
            const homeBarangaySelect = document.getElementById('homeBarangay');
            
            $.ajax({
                url: '/get-address-barangays',
                method: 'GET',
                data: { cityCode: cityCode },
                success: function(barangays) {
                    homeBarangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                    let bestMatch = null;
                    let bestMatchScore = 0;
                    
                    barangays.forEach(function(barangay) {
                        const option = new Option(barangay.name, barangay.code);
                        const barangayName = String(barangay.name).toLowerCase().trim();
                        const selectedName = String(preselectedBarangay).toLowerCase().trim();

                        if (preselectedBarangay) {
                            let score = 0;
                            if (barangayName === selectedName) score = 100;
                            else if (barangayName.includes(selectedName)) score = 80;
                            else if (selectedName.includes(barangayName)) score = 60;
                            else if (selectedName.length >= 3 && barangayName.includes(selectedName.substring(0, 3))) score = 40;
                            
                            if (String(barangay.code) == String(preselectedBarangay)) score = 100;

                            if (score > bestMatchScore) {
                                bestMatchScore = score;
                                bestMatch = option;
                            }
                        }

                        homeBarangaySelect.appendChild(option);
                    });
                    
                    if (bestMatch && bestMatchScore >= 40) {
                        bestMatch.selected = true;
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching home barangays:', error);
                }
            });
        }
        
        function fetchHomeZipcode(cityName) {
            const homeZipcodeInput = document.getElementById('homeZipcode');
            
            $.ajax({
                url: '/get-cities-zipcode',
                method: 'GET',
                data: { cityName: cityName },
                success: function(zipcode) {
                    if (zipcode) {
                        homeZipcodeInput.value = zipcode;
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching home zipcode:', error);
                }
            });
        }
        
        // Currency formatting function
        function formatCurrency(amount) {
            if (!amount || amount === '') return '';
            
            // Convert to number and format with commas and 2 decimal places
            const num = parseFloat(amount);
            if (isNaN(num)) return amount;
            
            return num.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Function to update price and term amount with formatting
        function updateContractPrice(price) {
            const priceField = document.getElementById('packagePrice');
            if (priceField) {
                priceField.value = formatCurrency(price);
            }
        }
        
        function updateTermAmount(amount) {
            const termField = document.getElementById('termAmount');
            if (termField) {
                termField.value = formatCurrency(amount);
            }
        }
        
        // Religion dropdown toggle function
        function toggleReligionOther() {
            const religionSelect = document.getElementById('religion');
            const religionOtherDiv = document.getElementById('religionOtherDiv');
            
            if (religionSelect.value === 'Other') {
                religionOtherDiv.classList.remove('hidden');
            } else {
                religionOtherDiv.classList.add('hidden');
                // Clear the custom input when hiding
                document.getElementById('religionOther').value = '';
            }
        }

        // Add event listeners for auto-capitalization
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-capitalize name fields
            const lastNameInput = document.getElementById('lastName');
            const firstNameInput = document.getElementById('firstName');
            const middleNameInput = document.getElementById('middleName');
            const birthPlaceInput = document.getElementById('birthPlace');
            const occupationInput = document.getElementById('occupation');
            const bestPlaceToCollectInput = document.getElementById('bestPlaceToCollect');
            
            if (lastNameInput) {
                lastNameInput.addEventListener('input', function() {
                    capitalizeName(this);
                });
                lastNameInput.addEventListener('blur', function() {
                    capitalizeName(this);
                });
            }
            
            if (firstNameInput) {
                firstNameInput.addEventListener('input', function() {
                    capitalizeName(this);
                });
                firstNameInput.addEventListener('blur', function() {
                    capitalizeName(this);
                });
            }
            
            if (middleNameInput) {
                middleNameInput.addEventListener('input', function() {
                    capitalizeName(this);
                });
                middleNameInput.addEventListener('blur', function() {
                    capitalizeName(this);
                });
            }
            
            if (birthPlaceInput) {
                birthPlaceInput.addEventListener('input', function() {
                    capitalizeName(this);
                });
                birthPlaceInput.addEventListener('blur', function() {
                    capitalizeName(this);
                });
            }
            
            if (occupationInput) {
                occupationInput.addEventListener('input', function() {
                    capitalizeName(this);
                });
                occupationInput.addEventListener('blur', function() {
                    capitalizeName(this);
                });
            }
            
            if (bestPlaceToCollectInput) {
                bestPlaceToCollectInput.addEventListener('input', function() {
                    capitalizeName(this);
                });
                bestPlaceToCollectInput.addEventListener('blur', function() {
                    capitalizeName(this);
                });
            }
            
            // Religion dropdown toggle
            const religionSelect = document.getElementById('religion');
            if (religionSelect) {
                religionSelect.addEventListener('change', toggleReligionOther);
                // Initialize on page load
                toggleReligionOther();
            }
        });

        // Form persistence functions
        function saveFormData() {
            const form = document.getElementById('clientForm');
            if (!form) return;
            
            const formData = new FormData(form);
            const data = {};
            
            // Save all form fields except sensitive data
            for (let [key, value] of formData.entries()) {
                // Skip sensitive fields and file inputs
                if (key !== '_token' && key !== 'principalbeneficiaryid') {
                    data[key] = value;
                }
            }
            
            localStorage.setItem('clientFormData', JSON.stringify(data));
            console.log('Form data saved to localStorage');
        }
        
        function loadFormData() {
            const savedData = localStorage.getItem('clientFormData');
            if (!savedData) return;
            
            try {
                const data = JSON.parse(savedData);
                const form = document.getElementById('clientForm');
                if (!form) return;
                
                // Restore form fields
                Object.keys(data).forEach(key => {
                    const element = form.querySelector(`[name="${key}"]`);
                    if (element) {
                        if (element.type === 'checkbox') {
                            element.checked = data[key] === 'on';
                        } else if (element.type === 'radio') {
                            const radio = form.querySelector(`[name="${key}"][value="${data[key]}"]`);
                            if (radio) radio.checked = true;
                        } else {
                            element.value = data[key];
                        }
                        
                        // Trigger change events for dropdowns
                        if (element.tagName === 'SELECT') {
                            element.dispatchEvent(new Event('change'));
                        }
                    }
                });
                
                console.log('Form data restored from localStorage');
            } catch (error) {
                console.error('Error loading form data:', error);
            }
        }
        
        function clearFormData() {
            localStorage.removeItem('clientFormData');
            console.log('Form data cleared from localStorage');
        }

        // Auto-save form data on input
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('clientForm');
            if (form) {
                // Load saved data on page load
                loadFormData();
                
                // Auto-save on any input change
                form.addEventListener('input', function() {
                    saveFormData();
                });
                
                form.addEventListener('change', function() {
                    saveFormData();
                });
            }
        });

        // Contact validation function
        function validateContactInfo() {
            const mobileNumber = document.getElementById('mobileNumber');
            const telephone = document.getElementById('telephone');
            const email = document.getElementById('email');
            
            let isValid = true;
            let errorMessage = '';
            
            // Check if at least one phone number is provided
            const mobileValue = mobileNumber ? mobileNumber.value.trim() : '';
            const telephoneValue = telephone ? telephone.value.trim() : '';
            
            if (!mobileValue && !telephoneValue) {
                errorMessage = 'Please provide either a mobile number or telephone number';
                isValid = false;
                
                // Add error styling
                if (mobileNumber) mobileNumber.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                if (telephone) telephone.classList.add('border-red-500', 'ring-2', 'ring-red-500');
            } else {
                // Remove error styling if validation passes
                if (mobileNumber) mobileNumber.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                if (telephone) telephone.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                
                // Show "N/A" for empty fields
                if (mobileNumber && !mobileValue) {
                    mobileNumber.placeholder = 'N/A';
                }
                if (telephone && !telephoneValue) {
                    telephone.placeholder = 'N/A';
                }
            }
            
            // Validate email format if provided
            if (email && email.value.trim()) {
                const emailValue = email.value.trim();
                const emailDomainSelect = document.getElementById('emailDomainSelect');
                const customEmailDomain = document.getElementById('customEmailDomain');
                
                let fullEmail = emailValue;
                if (emailDomainSelect) {
                    if (emailDomainSelect.value === 'others' && customEmailDomain) {
                        fullEmail += '@' + customEmailDomain.value.trim();
                    } else if (emailDomainSelect.value !== 'others') {
                        fullEmail += '@' + emailDomainSelect.value;
                    }
                }
                
                // Basic email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(fullEmail)) {
                    errorMessage += (errorMessage ? '\n' : '') + 'Please enter a valid email address';
                    isValid = false;
                    if (email) email.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                } else {
                    if (email) email.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                }
            } else {
                // Show "N/A" for empty email field
                if (email && !email.value.trim()) {
                    email.placeholder = 'N/A';
                    if (email) email.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                }
            }
            
            return { isValid, errorMessage };
        }

        // Initialize N/A placeholders on page load
        document.addEventListener('DOMContentLoaded', function() {
            const mobileNumber = document.getElementById('mobileNumber');
            const telephone = document.getElementById('telephone');
            const email = document.getElementById('email');
            
            // Set initial N/A placeholders for empty fields
            if (mobileNumber && !mobileNumber.value.trim()) {
                mobileNumber.placeholder = 'N/A';
            }
            if (telephone && !telephone.value.trim()) {
                telephone.placeholder = 'N/A';
            }
            if (email && !email.value.trim()) {
                email.placeholder = 'N/A';
            }
            
            // Clear N/A placeholder when user starts typing
            [mobileNumber, telephone, email].forEach(element => {
                if (element) {
                    element.addEventListener('focus', function() {
                        if (this.placeholder === 'N/A') {
                            this.placeholder = '';
                        }
                    });
                    
                    element.addEventListener('blur', function() {
                        if (!this.value.trim()) {
                            this.placeholder = 'N/A';
                        }
                    });
                }
            });
        });

        // Add event listeners for contact validation
        document.addEventListener('DOMContentLoaded', function() {
            const mobileNumber = document.getElementById('mobileNumber');
            const telephone = document.getElementById('telephone');
            
            if (mobileNumber) {
                mobileNumber.addEventListener('blur', function() {
                    validateContactInfo();
                });
            }
            
            if (telephone) {
                telephone.addEventListener('blur', function() {
                    validateContactInfo();
                });
            }
        });

        // Phone number formatting functions
        function formatMobile(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 4 && value.length <= 7) {
                value = value.slice(0, 3) + ' ' + value.slice(3);
            } else if (value.length >= 8) {
                value = value.slice(0, 3) + ' ' + value.slice(3, 7) + ' ' + value.slice(7, 11);
            }
            input.value = value;
        }

        function formatTelephone(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 3 && value.length <= 6) {
                value = value.slice(0, 2) + ' ' + value.slice(2);
            } else if (value.length >= 7) {
                value = value.slice(0, 2) + ' ' + value.slice(2, 6) + ' ' + value.slice(6);
            }
            input.value = value;
        }
    </script>
@endsection