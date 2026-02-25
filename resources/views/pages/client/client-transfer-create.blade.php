<!-- 2024 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-6">
        <!-- Header Section -->
        <div class="bg-white border-2 border-green-200 rounded-2xl shadow-2xl p-8 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-green-800 mb-2 flex items-center">
                        <svg class="w-8 h-8 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        New Client Transfer
                    </h1>
                    <p class="text-green-600 text-sm">Transfer ownership from: <span class="font-semibold">{{$client->LastName}}, {{ $client->FirstName}} {{ $client->MiddleName }}</span></p>
                </div>
                <div>
                    <a href="/client" class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-200" role="button">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Return
                    </a>
                </div>
            </div>
            
            @if(session('duplicate'))
                <div class="mt-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-center" role="alert">
                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('duplicate') }}</span>
                </div>
            @endif
        </div>
        <form action="/submit-client-transfer-insert/{{ $client->Id }}" method="POST">
            @csrf
            <!-- CONTRACT SECTION -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 hover:shadow-xl transition-shadow duration-300">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-purple-800 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        CONTRACT
                    </h2>
                </div>
                <div>
                        <div class="row">
                            <div class="col-sm-3">
                                @php
                                    $prevContractNo = old('contractno');
                                @endphp
                                <label for="contractNo" class="block text-sm font-semibold text-gray-700 mb-2">Contract No.</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="contractNo" name="contractno" value="{{ $prevContractNo }}" />
                                    @error('contractno')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                <label for="package" class="block text-sm font-semibold text-gray-700 mb-2">Package</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="package" disabled>
                                    @php
                                        $selectedPackage = $client->PackageID;
                                    @endphp
                                    @foreach($packages as $package)
                                        <option value="{{ $package->Id }}" {{ $selectedPackage == $package->Id ? 'selected' : '' }}>
                                            {{ $package->Package }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" value="{{ $selectedPackage}}" name="package" readonly />
                                @error('package')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevPackagePrice = old('packageprice');
                                @endphp
                                <label for="packagePrice" class="block text-sm font-semibold text-gray-700 mb-2">Price</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="packagePrice" name="packageprice" value="{{ $prevPackagePrice }}" readonly />
                                    @error('packageprice')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevPaymentTerm = $client->PaymentTermId;
                                @endphp
                                <label for="paymentTerm" class="block text-sm font-semibold text-gray-700 mb-2">Term</label>
                                <input type="hidden" id="defPaymentTerm" value="{{ $prevPaymentTerm }}" />
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="paymentTerm" disabled>
                                </select>
                                <input type="hidden" value="{{ $prevPaymentTerm}}" name="paymentterm" readonly />
                                @error('paymentterm')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-sm-3">
                                @php
                                    $prevTermAmount = old('termamount');
                                @endphp
                                <label for="termAmount" class="block text-sm font-semibold text-gray-700 mb-2">Term Amount</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="termAmount" name="termamount" value="{{ $prevTermAmount }}" readonly />
                                    @error('termamount')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                <label for="region" class="block text-sm font-semibold text-gray-700 mb-2">Region</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="region" name="region">
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
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-3">
                                <label for="branch" class="block text-sm font-semibold text-gray-700 mb-2">Branch</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="branch" name="branch">
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
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-3">
                                <label for="recruitedBy" class="block text-sm font-semibold text-gray-700 mb-2">FSA</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="recruitedBy" name="recruitedby">
                                    @php
                                        $selectedRecruitedBy = old('recruitedby');
                                    @endphp
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->Id }}" {{ $selectedRecruitedBy == $staff->Id ? 'selected' : '' }}>
                                            {{ $staff->LastName . ", " . $staff->FirstName }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="defRecruitedBy" value="{{ $selectedRecruitedBy }}" />
                                @error('recruitedby')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                </div>
            </div>

            <!-- PAYMENT SECTION -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 hover:shadow-xl transition-shadow duration-300">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-purple-800 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        PAYMENT
                    </h2>
                </div>
                <div>
                        <div class="row">
                            <div class="col-sm-3">
                                <label for="downpaymentType" class="block text-sm font-semibold text-gray-700 mb-2">Downpayment Type</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="downpaymentType" name="downpaymenttype">
                                    @php
                                        $selectedDownpaymentType = old('downpaymenttype');
                                    @endphp
                                    <option value="Partial" {{ $selectedDownpaymentType === 'Partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="Standard" {{ $selectedDownpaymentType === 'Standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="Change mode" {{ $selectedDownpaymentType === 'Change mode' ? 'selected' : '' }}>Change mode</option>
                                    <option value="Custom" {{ $selectedDownpaymentType === 'Custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                                @error('downpaymenttype')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevDownpaymentAmount = old('paymentamount');
                                @endphp
                                <label for="paymentAmount" class="block text-sm font-semibold text-gray-700 mb-2">Payment Amount</label>
                                <input type="hidden" id="defDownpaymentAmount" value="{{ $prevDownpaymentAmount }}" />
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount">
                                </select>
                                @error('paymentamount')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-3">
                                @php 
                                    $prevOrSeriesCode = old('orseriescode'); 
                                @endphp
                                <label for="orSeriesCode" class="block text-sm font-semibold text-gray-700 mb-2">O.R Series Code</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="orSeriesCode" name="orseriescode" maxlength="30" value="{{ $prevOrSeriesCode }}" />
                                    @error('orseriescode')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevOrNumber = old('ornumber'); 
                                @endphp
                                <label for="orNumber" class="block text-sm font-semibold text-gray-700 mb-2">O.R Number</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="orNumber" name="ornumber" maxlength="30" value="{{ $prevOrNumber }}" />
                                    @error('ornumber')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-3">
                                <label for="paymentMethod" class="block text-sm font-semibold text-gray-700 mb-2">Payment Method</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="paymentMethod" name="paymentmethod">
                                    @php
                                        $selectedPaymentMethod = old('paymentmethod');
                                    @endphp
                                    <option value="Cash" {{ $selectedPaymentMethod === 'Cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                                @error('paymentmethod')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevPaymentDate = old('paymentdate');
                                @endphp
                                <label for="paymentDate" class="block text-sm font-semibold text-gray-700 mb-2">Payment Date</label>
                                <input type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="paymentDate" name="paymentdate" maxlength="30" value="{{ $prevPaymentDate }}" />
                                    @error('paymentdate')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                </div>
            </div>

            <!-- PERSONAL SECTION -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 hover:shadow-xl transition-shadow duration-300">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-purple-800 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        PERSONAL INFORMATION
                    </h2>
                </div>
                <div>
                        <div class="row">
                            <div class="col-sm-3">
                                @php
                                    $prevLastName = old('lastname');
                                @endphp
                                <label for="lastName" class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="lastName" name="lastname" value="{{ $prevLastName }}" maxlength="30"/>
                                    @error('lastname')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevFirstName = old('firstname');
                                @endphp
                                <label for="firstName" class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="firstName" name="firstname" value="{{ $prevFirstName }}" maxlength="30"/>
                                    @error('firstname')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevMiddleName = old('middlename');
                                @endphp
                                <label for="middleName" class="block text-sm font-semibold text-gray-700 mb-2">Middle Name</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="middleName" name="middlename" value="{{ $prevMiddleName }}" maxlength="30"/>
                            </div>
                            <div class="col-sm-3">
                                <label for="gender" class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="gender" name="gender">
                                    @php
                                        $selectedGender = old('gender');
                                    @endphp
                                    <option value="Male" {{ $selectedGender === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $selectedGender === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-3">
                                @php
                                    $prevBirthDate = old('birthdate');
                                @endphp
                                <label for="birthDate" class="block text-sm font-semibold text-gray-700 mb-2">Birth Date</label>
                                <input type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="birthDate" name="birthdate" maxlength="30" value="{{ $prevBirthDate }}" />
                                    @error('birthdate')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevAge = old('age');
                                @endphp
                                <label for="age" class="block text-sm font-semibold text-gray-700 mb-2">Age</label>
                                <input type="number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="age" name="age" maxlength="30" value="{{ $prevAge }}" readonly />
                                    @error('age')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevBirthPlace = old('birthplace');
                                @endphp
                                <label for="birthPlace" class="block text-sm font-semibold text-gray-700 mb-2">Birth Place</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="birthPlace" name="birthplace" value="{{ $prevBirthPlace }}" />
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $selectedCivilStatus = old('civilstatus');
                                @endphp
                                <label for="civilStatus" class="block text-sm font-semibold text-gray-700 mb-2">Civil Status</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="civilStatus" name="civilstatus">
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
                        <div class="row mt-3">
                            <div class="col-sm-3">
                                @php
                                    $prevReligion = old('religion');
                                @endphp
                                <label for="religion" class="block text-sm font-semibold text-gray-700 mb-2">Religion</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="religion" name="religion" value="{{ $prevReligion }}" />
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevOccupation = old('occupation');
                                @endphp
                                <label for="occupation" class="block text-sm font-semibold text-gray-700 mb-2">Occupation</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="occupation" name="occupation" value="{{ $prevOccupation }}" />
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevPlaceToCollect = old('bestplacetocollect');
                                @endphp
                                <label for="bestPlaceToCollect" class="block text-sm font-semibold text-gray-700 mb-2">Best Place to Collect</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="bestPlaceToCollect" name="bestplacetocollect" maxlength="30" value="{{ $prevPlaceToCollect }}" />
                                    @error('bestplacetocollect')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevTimeToCollect = old('besttimetocollect');
                                @endphp
                                <label for="bestTimeToCollect" class="block text-sm font-semibold text-gray-700 mb-2">Best Time to Collect</label>
                                <input type="time" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="bestTimeToCollect" name="besttimetocollect" maxlength="30" value="{{ $prevTimeToCollect }}" />
                                    @error('besttimetocollect')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                </div>
            </div>

            <!-- ADDRESS SECTION -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 hover:shadow-xl transition-shadow duration-300">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-purple-800 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        ADDRESS
                    </h2>
                </div>
                <div>
                        <div class="row">
                            <div class="col-sm-3">
                                @php
                                    $selectedProvince = old('province');
                                @endphp
                                <label for="province" class="block text-sm font-semibold text-gray-700 mb-2">Province</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="province" name="province">
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
                            <div class="col-sm-3">
                                @php
                                    $selectedCity = old('city');
                                @endphp
                                <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                                <input type="hidden" id="prevCity" value="{{ $selectedCity }}" />
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="city" name="city">
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
                            <div class="col-sm-3">
                                @php
                                    $selectedBarangay = old('barangay');
                                @endphp
                                <label for="barangay" class="block text-sm font-semibold text-gray-700 mb-2">Barangay</label>
                                <input type="hidden" id="prevBarangay" value="{{ $selectedBarangay }}" />
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="barangay" name="barangay">
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
                        </div>
                        <div class="row mt-4">
                            <div class="col-sm-3">
                                @php
                                    $prevZipcode = old('zipcode');
                                @endphp
                                <label for="zipcode" class="block text-sm font-semibold text-gray-700 mb-2">ZIP code</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="zipcode" name="zipcode" maxlength="30" value="{{ $prevZipcode }}" readonly />
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStreet = old('street');
                                @endphp
                                <label for="street" class="block text-sm font-semibold text-gray-700 mb-2">Street</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="street" name="street" value="{{ $prevStreet }}" />
                            </div>
                        </div>
                </div>
            </div>

            <!-- CONTACT SECTION -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 hover:shadow-xl transition-shadow duration-300">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-purple-800 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        CONTACT INFORMATION
                    </h2>
                </div>
                <div>
                        <div class="row">
                            <div class="col-sm-3">
                                @php
                                    $prevTelephone = old('telephone');
                                @endphp
                                <label for="telephone" class="block text-sm font-semibold text-gray-700 mb-2">Telephone</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="telephone" name="telephone" maxlength="30" value="{{ $prevTelephone }}" />
                            </div>
                            <div class="col-sm-3">
                                <label for="mobileNumber" class="block text-sm font-semibold text-gray-700 mb-2">Mobile (+63)</label>
                                @php 
                                    $fullMobileNumber = old('mobileno');
                                    // Remove leading 0 if present (convert 09123456789 to 9123456789)
                                    if (strlen($fullMobileNumber) == 11 && substr($fullMobileNumber, 0, 1) == '0') {
                                        $fullMobileNumber = substr($fullMobileNumber, 1);
                                    }
                                @endphp
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="mobileNumber" name="mobileno" placeholder="9123456789" maxlength="10" value="{{ $fullMobileNumber }}" />
                                @error('mobileno')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-4">
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <div class="input-group">
                                    @php 
                                        $selectedEmail = old('email'); 
                                    @endphp
                                    <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="email" name="email" maxlength="30" value="{{ $selectedEmail }}" />
                                    <span class="input-group-text" id="basic-addon1">@</span>
                                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="emailAddress" name="emailaddress" onchange="toggleCustomEmailDomainTransfer()">
                                        @php 
                                            $selectedEmailAddress = old('emailaddress');
                                            $customEmailDomain = old('customemaildomain');
                                        @endphp
                                        @foreach($emails as $email)
                                            <option value="{{ $email->Email }}" {{ $selectedEmailAddress == $email->Email ? 'selected' : '' }}>
                                                {{ $email->Email }}
                                            </option>
                                        @endforeach
                                        <option value="others" {{ $selectedEmailAddress == 'others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 mt-2" id="customEmailDomain" name="customemaildomain" placeholder="Enter custom domain (e.g., company.com)" maxlength="50" value="{{ $customEmailDomain }}" style="display: {{ $selectedEmailAddress == 'others' ? 'block' : 'none' }};" />
                                <div>
                                    @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                </div>
            </div>

            <!-- BENEFICIARIES SECTION -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 hover:shadow-xl transition-shadow duration-300">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-purple-800 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        BENEFICIARIES
                    </h2>
                </div>
                <div>
                        <div class="row">
                            <div class="col-sm-4">
                                @php
                                    $prevPrincipalBeneficiary = old('principalbeneficiary');
                                    $prevPrincipalBeneficiaryAge = old('principalbeneficiaryage');
                                @endphp
                                <label for="principalBeneficiary" class="block text-sm font-semibold text-gray-700 mb-2">Principal Beneficiary (Age)</label>
                                <div class="input-group">
                                    <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="principalBeneficiary" name="principalbeneficiary" value="{{ $prevPrincipalBeneficiary }}" />
                                    <label for="principalBeneficiaryAge" class="block text-sm font-semibold text-gray-700 mb-2"></label>
                                    <input type="number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="principalBeneficiaryAge" name="principalbeneficiaryage" maxlength="3" value="{{ $prevPrincipalBeneficiaryAge }}" />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                @php
                                    $prevBeneficiary1 = old('beneficiary1');
                                    $prevBeneficiary1Age = old('beneficiary1age');
                                @endphp
                                <label for="beneficiary1" class="block text-sm font-semibold text-gray-700 mb-2">Beneficiary 1 (Age)</label>
                                <div class="input-group">
                                    <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="beneficiary1" name="beneficiary1" value="{{ $prevBeneficiary1 }}" />
                                    <label for="beneficiary1age" class="block text-sm font-semibold text-gray-700 mb-2"></label>
                                    <input type="number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="beneficiary1age" name="beneficiary1age" maxlength="3" value="{{ $prevBeneficiary1Age }}" />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                @php
                                    $prevBeneficiary2 = old('beneficiary2');
                                    $prevBeneficiary2Age = old('beneficiary2age');
                                @endphp
                                <label for="beneficiary2" class="block text-sm font-semibold text-gray-700 mb-2">Beneficiary 2 (Age)</label>
                                <div class="input-group">
                                    <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="beneficiary2" name="beneficiary2" value="{{ $prevBeneficiary2 }}" />
                                    <label for="beneficiary2age" class="block text-sm font-semibold text-gray-700 mb-2"></label>
                                    <input type="number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="beneficiary2age" name="beneficiary2age" maxlength="3" value="{{ $prevBeneficiary2Age }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-sm-4">
                                @php
                                    $prevBeneficiary3 = old('beneficiary3');
                                    $prevBeneficiary3Age = old('beneficiary3age');
                                @endphp
                                <label for="beneficiary3" class="block text-sm font-semibold text-gray-700 mb-2">Beneficiary 3 (Age)</label>
                                <div class="input-group">
                                    <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="beneficiary3" name="beneficiary3" value="{{ $prevBeneficiary3 }}" />
                                    <label for="beneficiary3age" class="block text-sm font-semibold text-gray-700 mb-2"></label>    
                                    <input type="number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="beneficiary3age" name="beneficiary3age" maxlength="3" value="{{ $prevBeneficiary3Age }}" />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                @php
                                    $prevBeneficiary4 = old('beneficiary4');
                                    $prevBeneficiary4Age = old('beneficiary4age');
                                @endphp
                                <label for="beneficiary4" class="block text-sm font-semibold text-gray-700 mb-2">Beneficiary 4 (Age)</label>
                                <div class="input-group">
                                    <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="beneficiary4" name="beneficiary4" value="{{ $prevBeneficiary4 }}" />
                                    <label for="beneficiary4age" class="block text-sm font-semibold text-gray-700 mb-2"></label>
                                    <input type="number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="beneficiary4age" name="beneficiary4age" maxlength="3" value="{{ $prevBeneficiary4Age }}" />
                                </div>
                            </div>
                        </div>
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="flex justify-center mt-8">
                <button type="submit" class="inline-flex items-center px-12 py-4 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold text-lg rounded-xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition duration-300">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit Transfer
                </button>
            </div>
        </form>
    </div>
    <script src="{{ asset('js/client-create.js') }}"></script>
    <script>
    // Toggle custom email domain input for transfer form
    function toggleCustomEmailDomainTransfer() {
        const emailDomainSelect = document.getElementById('emailAddress');
        const customEmailDomain = document.getElementById('customEmailDomain');
        
        if (emailDomainSelect.value === 'others') {
            customEmailDomain.style.display = 'block';
            customEmailDomain.focus();
        } else {
            customEmailDomain.style.display = 'none';
            customEmailDomain.value = '';
        }
    }
    </script>
@endsection