<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-blue-300 p-6 mb-6">
            <h1 class="text-3xl font-bold text-blue-800 mb-2">Update Client</h1>
            <p class="text-blue-600 text-sm">Update client information for selected client</p>
        </div>

        <!-- Alert Messages -->
        @if(session('duplicate'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <p class="text-red-700 font-medium">{{ session('duplicate') }}</p>
            </div>
        @endif

        <!-- Return Button -->
        <div class="mb-6">
            <a href="/client-view/{{ $clients->Id }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-200 ease-in-out">Return</a>
        </div>
        @php
            $canEditContractNumber = $canEditContractNumber ?? false;
            // Debug: Log the permission value
            \Log::info('Client Update View - canEditContractNumber: ' . ($canEditContractNumber ? 'true' : 'false'));
            
            // Get user role details for debugging
            $userRole = \App\Models\Role::query()->where('id', session('user_roleid'))->first();
        @endphp
        
        <script>
            console.log('=== USER ROLE DEBUG ===');
            console.log('User Role ID:', '{{ session('user_roleid') }}');
            console.log('User Role Name:', '{{ $userRole->Role ?? "Unknown" }}');
            console.log('User Role Level:', '{{ $userRole->Level ?? "Unknown" }}');
            console.log('Can Edit Contract Number:', {{ $canEditContractNumber ? 'true' : 'false' }});
            console.log('Role Found:', {{ $userRole ? 'true' : 'false' }});
            @if($userRole)
                console.log('Role Check:', '"{{ $userRole->Role }}" is Administrator or Approver?', {{ (strtolower($userRole->Role) == 'administrator' || strtolower($userRole->Role) == 'approver') ? 'true' : 'false' }});
            @else
                console.log('Role Check: Role not found');
            @endif
            console.log('========================');
            
            // Check contract number field state
            document.addEventListener('DOMContentLoaded', function() {
                const contractField = document.getElementById('contractNo');
                if (contractField) {
                    console.log('Contract Number Field Debug:');
                    console.log('- Field exists:', true);
                    console.log('- Is readonly:', contractField.readOnly);
                    console.log('- Has readonly attribute:', contractField.hasAttribute('readonly'));
                    console.log('- Data can-edit attribute:', contractField.getAttribute('data-can-edit'));
                    console.log('- Current value:', contractField.value);
                }
            });
        </script>
        <form action="/submit-client-update/{{ $clients->Id }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- CONTRACT Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">CONTRACT</h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                @php
                                    $prevContractNo = old('contractno', $clients->ContractNumber);
                                @endphp                
                                <label for="contractNo" class="block text-sm font-medium text-gray-700 mb-2">Contract No.</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="contractNo" name="contractno" value="{{ $prevContractNo }}" {{ $canEditContractNumber ? '' : 'readonly' }} data-can-edit="{{ $canEditContractNumber ? 'true' : 'false' }}" />
                                @unless($canEditContractNumber)
                                    <small class="text-gray-500 text-xs mt-1 block">
                                        <i class="fas fa-lock"></i>
                                        Contract number updates are restricted to administrators and approvers for approved contracts.
                                    </small>
                                @endunless
                                @error('contractno')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="package" class="block text-sm font-medium text-gray-700 mb-2">Package</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="package" name="package">
                                    @php
                                        $selectedPackage = old('package', $clients->PackageID);
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
                            <div>
                                @php
                                    $prevPackagePrice = old('packageprice');
                                @endphp
                                <label for="packagePrice" class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="packagePrice" name="packageprice" value="{{ $prevPackagePrice }}" readonly />
                                @error('packageprice')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php   
                                    $prevPaymentTerm = old('paymentterm', $clients->PaymentTermId);
                                @endphp
                                <label for="paymentTerm" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                                <input type="hidden" id="defPaymentTerm" value="{{ $prevPaymentTerm }}" />

                                @if($clients->AppliedChangeMode == 1 || $clients->Status != 3)
                                    <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="paymentTerm" name="paymentterm">
                                    </select>
                                    @error('paymentterm')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                @else
                                    <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="paymentTerm" name="paymentterm" disabled>
                                    </select>
                                    <input type="hidden" value="{{ $prevPaymentTerm }}" name="paymentterm" readonly />
                                    @error('paymentterm')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                            <div>
                                @php
                                    $prevTermAmount = old('termamount', $clients->PaymentTermAmount);
                                @endphp
                                <label for="termAmount" class="block text-sm font-medium text-gray-700 mb-2">Term Amount</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="termAmount" name="termamount" value="{{ $prevTermAmount }}" readonly />
                                @error('termamount')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="region" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="region" name="region">
                                    @php
                                        $selectedRegion = old('region', $clients->RegionId);
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
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="branch" name="branch">
                                    @php
                                        $selectedBranch = old('branch', $clients->BranchId);
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
                            </div>
                            <div>
                                <label for="recruitedBy" class="block text-sm font-medium text-gray-700 mb-2">FSA</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="recruitedBy" name="recruitedby">
                                    @php
                                        $selectedRecruitedBy = old('recruitedby', $clients->RecruitedBy);
                                    @endphp
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
            </div>
            @if($clients->Status != '3')
            <!-- PAYMENT Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
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
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="downpaymentType" name="downpaymenttype">
                                    @php
                                        if($orbatchinfo->Type == null){
                                            $downpaymentType = 'Standard';
                                        }
                                        else{
                                            if($orbatchinfo->Type == '1'){
                                                $downpaymentType = 'Standard';
                                            }
                                            else if($orbatchinfo->Type == '2'){
                                                $downpaymentType = 'Partial';
                                            }
                                        }

                                        $selectedDownpaymentType = old('downpaymenttype', $downpaymentType);
                                    @endphp
                                    <option value="Partial" {{ $selectedDownpaymentType === 'Partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="Standard" {{ $selectedDownpaymentType === 'Standard' ? 'selected' : '' }}>Standard</option>
                                </select>
                                @error('downpaymenttype')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $prevDownpaymentAmount = old('paymentamount', $paymentinfo->AmountPaid);
                                @endphp
                                <input type="hidden" id="defAmountPaid" value="{{ $prevDownpaymentAmount }}" />
                                <label for="paymentAmount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount">
                                </select>
                                @error('paymentamount')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php 
                                    $prevOrSeriesCode = old('orseriescode', $orbatchinfo->SeriesCode); 
                                @endphp
                                <label for="orSeriesCode" class="block text-sm font-medium text-gray-700 mb-2">O.R Series Code</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="orSeriesCode" name="orseriescode" maxlength="30" value="{{ $prevOrSeriesCode }}" />
                                @error('orseriescode')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevOrNumber = old('ornumber', $paymentinfo->ORNo); 
                                @endphp
                                <label for="orNumber" class="block text-sm font-medium text-gray-700 mb-2">O.R Number</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="orNumber" name="ornumber" maxlength="30" value="{{ $prevOrNumber }}" />
                                @error('ornumber')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                            <div>
                                <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="paymentMethod" name="paymentmethod">
                                    @php
                                        $selectedPaymentMethod = old('paymentmethod');

                                        if($paymentinfo->PaymentType == '1'){
                                            $selectedPaymentMethod = 'Cash';
                                        }
                                    @endphp
                                    <option value="Cash" {{ $selectedPaymentMethod === 'Cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                                @error('paymentmethod')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $prevPaymentDate = old('paymentdate', $paymentinfo->Date);
                                @endphp
                                <label for="paymentDate" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                                <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="paymentDate" name="paymentdate" value="{{ $prevPaymentDate }}" />
                                @error('paymentdate')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                </div>
            </div>
            @endif
            
            <!-- PERSONAL Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
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
                                    $prevLastName = old('lastname', $clients->LastName);
                                @endphp
                                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="lastName" name="lastname" value="{{ $prevLastName }}" maxlength="30"/>
                                @error('lastname')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $prevFirstName = old('firstname', $clients->FirstName);
                                @endphp
                                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="firstName" name="firstname" value="{{ $prevFirstName }}" maxlength="30"/>
                                @error('firstname')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $prevMiddleName = old('middlename', $clients->MiddleName);
                                @endphp
                                <label for="middleName" class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="middleName" name="middlename" value="{{ $prevMiddleName }}" maxlength="30"/>
                                @error('middlename')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="gender" name="gender">
                                    @php
                                        $selectedGender = old('gender', $clients->Gender);
                                    @endphp
                                    <option value="Male" {{ $selectedGender === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $selectedGender === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-sm-3">
                                @php
                                    $prevBirthDate = old('birthdate', $clients->BirthDate);
                                @endphp
                                <label for="birthDate" class="form-label">Birth Date</label>
                                <input type="date" class="form-control font-sm" id="birthDate" name="birthdate" maxlength="30" value="{{ $prevBirthDate }}" />
                                    @error('birthdate')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevAge = old('age', $clients->Age);
                                @endphp
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control font-sm" id="age" name="age" maxlength="30" value="{{ $prevAge }}" readonly />
                                    @error('age')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevBirthPlace = old('birthplace', $clients->BirthPlace);
                                @endphp
                                <label for="birthPlace" class="form-label">Birth Place</label>
                                <input type="text" class="form-control font-sm" id="birthPlace" name="birthplace" maxlength="30" value="{{ $prevBirthPlace }}" />
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $selectedCivilStatus = old('civilstatus', $clients->CivilStatus);
                                @endphp
                                <label for="civilStatus" class="form-label">Civil Status</label>
                                <select class="form-control font-sm" id="civilStatus" name="civilstatus">
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
                        <div class="row mt-4">
                            <div class="col-sm-3">
                                @php
                                    $prevReligion = old('religion', $clients->Religion);
                                @endphp
                                <label for="religion" class="form-label">Religion</label>
                                <input type="text" class="form-control font-sm" id="religion" name="religion" maxlength="30" value="{{ $prevReligion }}" />
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevOccupation = old('occupation', $clients->Occupation);
                                @endphp
                                <label for="occupation" class="form-label">Occupation</label>
                                <input type="text" class="form-control font-sm" id="occupation" name="occupation" maxlength="30" value="{{ $prevOccupation }}" />
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevPlaceToCollect = old('bestplacetocollect', $clients->BestPlaceToCollect);
                                @endphp
                                <label for="bestPlaceToCollect" class="form-label">Best Place to Collect</label>
                                <input type="text" class="form-control font-sm" id="bestPlaceToCollect" name="bestplacetocollect" maxlength="30" value="{{ $prevPlaceToCollect }}" />
                                    @error('bestplacetocollect')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevTimeToCollect = old('besttimetocollect', $clients->BestTimeToCollect);
                                @endphp
                                <label for="bestTimeToCollect" class="form-label">Best Time to Collect</label>
                                <input type="time" class="form-control font-sm" id="bestTimeToCollect" name="besttimetocollect" maxlength="30" value="{{ $prevTimeToCollect }}" />
                                    @error('besttimetocollect')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                    </div>
                </div>
            <!-- ADDRESS Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        ADDRESS
                    </h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                @php
                                    $clientRegionId = $clients->RegionId ?? '';
                                    $mappedRegionCode = $regionMapping[$clientRegionId] ?? '';
                                    $selectedAddressRegion = old('address_region', $mappedRegionCode);
                                @endphp
                                <label for="addressRegion" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="addressRegion" name="address_region">
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
                            <div>
                                @php
                                    $selectedAddressProvince = old('address_province', $clients->Province ?? '');
                                @endphp
                                <label for="addressProvince" class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="addressProvince" name="address_province">
                                    <option value="">Select Province</option>
                                </select>
                                @error('address_province')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $selectedAddressCity = old('address_city', $clients->City ?? '');
                                @endphp
                                <label for="addressCity" class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="addressCity" name="address_city">
                                    <option value="">Select City/Municipality</option>
                                </select>
                                @error('address_city')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $selectedAddressBarangay = old('address_barangay', $clients->Barangay ?? '');
                                @endphp
                                <label for="addressBarangay" class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="addressBarangay" name="address_barangay">
                                    <option value="">Select Barangay</option>
                                </select>
                                @error('address_barangay')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                            <div>
                                @php
                                    $prevZipcode = old('zipcode', $clients->ZipCode);
                                @endphp
                                <label for="zipcode" class="block text-sm font-medium text-gray-700 mb-2">ZIP code</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="zipcode" name="zipcode" maxlength="10" value="{{ $prevZipcode }}" placeholder="Select city first" title="Select a city to auto-fill zipcode or enable manual input" />
                            </div>
                            <div>
                                @php
                                    $prevStreet = old('street', $clients->Street);
                                @endphp
                                <label for="street" class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="street" name="street" maxlength="30" value="{{ $prevStreet }}" />
                            </div>
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
                                    $selectedHomeRegion = old('home_region', $clients->HomeRegion ?? '');
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
                                    $selectedHomeProvince = old('home_province', $clients->HomeProvince ?? '');
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
                                    $selectedHomeCity = old('home_city', $clients->HomeCity ?? '');
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
                                    $selectedHomeBarangay = old('home_barangay', $clients->HomeBarangay ?? '');
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
                                    $prevHomeZipcode = old('home_zipcode', $clients->HomeZipCode ?? '');
                                @endphp
                                <label for="homeZipcode" class="block text-sm font-medium text-gray-700 mb-2">ZIP code</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="homeZipcode" name="home_zipcode" maxlength="10" value="{{ $prevHomeZipcode }}" placeholder="Select city first" />
                            </div>
                            <div>
                                @php
                                    $prevHomeStreet = old('home_street', $clients->HomeStreet ?? '');
                                @endphp
                                <label for="homeStreet" class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="homeStreet" name="home_street" maxlength="30" value="{{ $prevHomeStreet }}" />
                            </div>
                        </div>
                </div>
            </div>
            
            <!-- CONTACT Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                        CONTACT
                    </h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                @php
                                    $prevTelephone = old('telephone', $clients->HomeNumber);
                                @endphp
                                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">Telephone</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="telephone" name="telephone" maxlength="30" value="{{ $prevTelephone }}" />
                            </div>
                            <div>
                                <label for="mobileNumber" class="block text-sm font-medium text-gray-700 mb-2">Mobile (+63)</label>
                                @php 
                                    $fullMobileNumber = old('mobilenumber', $clients->MobileNumber);
                                    // Remove leading 0 if present (convert 09123456789 to 9123456789)
                                    if (strlen($fullMobileNumber) == 11 && substr($fullMobileNumber, 0, 1) == '0') {
                                        $fullMobileNumber = substr($fullMobileNumber, 1);
                                    }
                                @endphp
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-600 font-medium">+63</span>
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="mobileNumber" name="mobilenumber" placeholder="9123456789" maxlength="10" value="{{ $fullMobileNumber }}" />
                                </div>
                                @error('mobilenumber')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <div class="flex gap-2">
                                    @php 
                                        $selectedEmail = old('email', substr($clients->EmailAddress, 0, strpos($clients->EmailAddress, '@')));
                                        $selectedEmailAddress = old('emailaddress', substr($clients->EmailAddress, strpos($clients->EmailAddress, '@') + 1));
                                        
                                        // Check if the current email domain is in the predefined list
                                        $isCustomDomain = true;
                                        foreach($emails as $emailOption) {
                                            if($selectedEmailAddress == $emailOption->Email) {
                                                $isCustomDomain = false;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="email" name="email" maxlength="30" value="{{ $selectedEmail }}" />
                                    <span class="flex items-center px-3 border border-gray-300 bg-gray-50 rounded-lg text-gray-600">@</span>
                                    <select class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="emailDomainSelect" name="emailaddress" onchange="toggleCustomEmailDomain()">
                                        @foreach($emails as $email)
                                            <option value="{{ $email->Email }}" {{ $selectedEmailAddress == $email->Email ? 'selected' : '' }}>
                                                {{ $email->Email }}
                                            </option>
                                        @endforeach
                                        <option value="others" {{ $isCustomDomain ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 mt-2" id="customEmailDomain" name="customemaildomain" placeholder="Enter custom domain (e.g., company.com)" maxlength="50" value="{{ $isCustomDomain ? $selectedEmailAddress : '' }}" style="display: {{ $isCustomDomain ? 'block' : 'none' }};" />
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
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        BENEFICIARIES
                    </h3>
                </div>
                <div class="p-6">
                        <div class="mb-6 bg-blue-50/50 p-5 rounded-xl border border-blue-100">
                            <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Principal Beneficiary Details
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    @php
                                        $prevPrincipalBeneficiary = old('principalbeneficiary', $clients->PrincipalBeneficiaryName);
                                        $prevPrincipalBeneficiaryAge = old('principalbeneficiaryage', $clients->PrincipalBeneficiaryAge);
                                        $prevPrincipalBeneficiaryRelation = old('principalbeneficiaryrelation', $clients->principalbeneficiaryrelation);
                                    @endphp
                                    <label for="principalBeneficiary" class="block text-xs font-medium text-gray-700 mb-1">Full Name & Age</label>
                                    <div class="flex gap-2">
                                        <input type="text" class="flex-1 px-4 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-white" id="principalBeneficiary" name="principalbeneficiary" maxlength="30" value="{{ $prevPrincipalBeneficiary }}" placeholder="Full Name" />
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
                                    <label for="principalBeneficiaryId" class="block text-xs font-medium text-gray-700 mb-1">Upload New Valid ID (Optional)</label>
                                    <input type="file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition duration-200" id="principalBeneficiaryId" name="principalbeneficiaryid" accept="image/jpeg,image/png,application/pdf" />
                                    <p class="text-xs text-gray-400 mt-1">Accepted formats: JPG, PNG, PDF. Max size: 2MB.</p>
                                    @error('principalbeneficiaryid')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    
                                    @if($clients->principalbeneficiaryid_path)
                                        <div class="mt-2 text-sm">
                                            <span class="text-gray-600">Current ID: </span>
                                            <a href="{{ asset('storage/' . $clients->principalbeneficiaryid_path) }}" target="_blank" class="text-blue-600 hover:underline">View Uploaded ID</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                @php
                                    $prevBeneficiary1 = old('beneficiary1', $clients->Secondary1Name);
                                    $prevBeneficiary1Age = old('beneficiary1age', $clients->Secondary1Age);
                                @endphp
                                <label for="beneficiary1" class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 1 (Age)</label>
                                <div class="flex gap-2">
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="beneficiary1" name="beneficiary1" maxlength="30" value="{{ $prevBeneficiary1 }}" />
                                    <input type="number" class="w-20 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="beneficiary1age" name="beneficiary1age" maxlength="3" value="{{ $prevBeneficiary1Age }}" />
                                </div>
                            </div>
                            <div>
                                @php
                                    $prevBeneficiary2 = old('beneficiary2', $clients->Secondary2Name);
                                    $prevBeneficiary2Age = old('beneficiary2age', $clients->Secondary2Age);
                                @endphp
                                <label for="beneficiary2" class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 2 (Age)</label>
                                <div class="flex gap-2">
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="beneficiary2" name="beneficiary2" maxlength="30" value="{{ $prevBeneficiary2 }}" />
                                    <input type="number" class="w-20 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="beneficiary2age" name="beneficiary2age" maxlength="3" value="{{ $prevBeneficiary2Age }}" />
                                </div>
                            </div>
                            
                            <div>
                                @php
                                    $prevBeneficiary3 = old('beneficiary3', $clients->Secondary3Name);
                                    $prevBeneficiary3Age = old('beneficiary3age', $clients->Secondary3Age);
                                @endphp
                                <label for="beneficiary3" class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 3 (Age)</label>
                                <div class="flex gap-2">
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="beneficiary3" name="beneficiary3" maxlength="30" value="{{ $prevBeneficiary3 }}" />
                                    <input type="number" class="w-20 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="beneficiary3age" name="beneficiary3age" maxlength="3" value="{{ $prevBeneficiary3Age }}" />
                                </div>
                            </div>
                            
                            <div>
                                @php
                                    $prevBeneficiary4 = old('beneficiary4', $clients->Secondary4Name);
                                    $prevBeneficiary4Age = old('beneficiary4age', $clients->Secondary4Age);
                                @endphp
                                <label for="beneficiary4" class="block text-sm font-medium text-gray-700 mb-2">Beneficiary 4 (Age)</label>
                                <div class="flex gap-2">
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="beneficiary4" name="beneficiary4" maxlength="30" value="{{ $prevBeneficiary4 }}" />
                                    <input type="number" class="w-20 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" id="beneficiary4age" name="beneficiary4age" maxlength="3" value="{{ $prevBeneficiary4Age }}" />
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-center mt-8">
                <button type="submit" class="px-12 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Client Information
                    </span>
                </button>
            </div>
        </form>
    </div>
    
    <script>
        // Email domain toggle functionality (based on client create form pattern)
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

        // Enhanced email domain event listeners
        document.addEventListener('DOMContentLoaded', function () {
            const emailDomainSelect = document.getElementById('emailDomainSelect');
            const customDomainInput = document.getElementById('customEmailDomain');
            
            // Initialize on page load
            toggleCustomEmailDomain();
            
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
        });
    </script>
    
    <!-- Hidden inputs for address cascading system -->
    @php
        $clientRegionId = $clients->RegionId ?? '';
        $mappedRegionCode = $regionMapping[$clientRegionId] ?? '';
    @endphp
    <input type="hidden" id="oldAddressRegion" value="{{ $mappedRegionCode }}" />
    <input type="hidden" id="oldProvince" value="{{ $clients->Province ?? '' }}" />
    <input type="hidden" id="oldCity" value="{{ $clients->City ?? '' }}" />
    <input type="hidden" id="oldBarangay" value="{{ $clients->Barangay ?? '' }}" />
    
    <!-- Hidden inputs for home address cascading system -->
    <input type="hidden" id="oldHomeRegion" value="{{ $clients->HomeRegion ?? '' }}" />
    <input type="hidden" id="oldHomeProvince" value="{{ $clients->HomeProvinceDisplay ?? $clients->HomeProvince ?? '' }}" />
    <input type="hidden" id="oldHomeCity" value="{{ $clients->HomeCityDisplay ?? $clients->HomeCity ?? '' }}" />
    <input type="hidden" id="oldHomeBarangay" value="{{ $clients->HomeBarangayDisplay ?? $clients->HomeBarangay ?? '' }}" />
    
    <script src="{{ asset('js/client-update.js') }}"></script>
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
                
                // Initial load if region exists
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
                            
                            // Fallback: match by code
                            if (String(province.code) == String(preselectedProvince)) score = 100;

                            if (score > bestMatchScore) {
                                bestMatchScore = score;
                                bestMatch = option;
                            }
                        }
                        
                        homeProvinceSelect.appendChild(option);
                    });

                    // Select best match if score is sufficient
                    if (bestMatch && bestMatchScore >= 40) {
                        bestMatch.selected = true;
                    }
                    
                    // If preselected (and we seemingly found a match or proceeded anyway), trigger city fetch
                    // If preselected (and we seemingly found a match or proceeded anyway), trigger city fetch
                    // improved condition: check if we have a value OR if we have a preselected string that drove the match
                    if (preselectedProvince && homeProvinceSelect.value) {
                         // Pass the *value* (code) of the selected option
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
                            
                            // Fallback: match by code
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
                    
                    // If preselected, trigger barangay fetch
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
                            
                            // Fallback: match by code
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
    </script>
@endsection