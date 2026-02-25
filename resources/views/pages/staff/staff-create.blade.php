<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-300 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">New Staff</h1>
                    <p class="text-green-600 text-sm">Create a new staff to the selected branch</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
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
            <a href="/staff" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-200 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Return to Staff List
            </a>
        </div>
        <form id="staffCreateForm" action="/submit-staff-insert" method="POST" novalidate>
            @csrf
            
            <!-- IDENTIFICATION Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-1">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        IDENTIFICATION
                    </h3>
                </div>
                <div class="p-6 staff-address-information">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                @php
                                    $prevStaffUserName = old('staffusername');
                                @endphp
                                <label for="staffUserName" class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffUserName" name="staffusername" maxlength="30" value="{{ $prevStaffUserName }}" required />
                                    @error('staffusername')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                <label for="staffPosition" class="block text-sm font-medium text-gray-700 mb-2">Position <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffPosition" name="staffposition" required>
                                    @php
                                        $selectedPosition = old('staffposition');
                                    @endphp
                                    @foreach($roles as $role)
                                        <option value="{{ $role->Id }}" {{ $selectedPosition == $role->Id ? 'selected' : '' }}>
                                            {{ $role->Role }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="staffRegion" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffRegion" name="staffregion">
                                    @php
                                        $selectedRegion = old('staffregion');
                                    @endphp
                                    @foreach($regions as $region)
                                        <option value="{{ $region->Id }}" {{ $selectedRegion == $region->Id ? 'selected' : '' }}>
                                            {{ $region->RegionName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="staffBranch" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                                <input type="hidden" id="prevBranch" value="{{ old('staffbranch') }}" />
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffBranch" name="staffbranch">
                                    @php
                                        $selectedBranch = old('staffbranch');
                                    @endphp
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="staffScheme" class="block text-sm font-medium text-gray-700 mb-2">Scheme</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffScheme" name="staffscheme">
                                    @php
                                        $selectedScheme = old('staffscheme');
                                    @endphp
                                    <option value="Old">Old</option>
                                    <option value="New">New</option>
                                    @if($selectedScheme != null)
                                        <option hidden selected value="{{ $selectedScheme }}">{{ $selectedScheme }}</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label for="staffRecruitedBy" class="block text-sm font-medium text-gray-700 mb-2">Recruited By</label>
                                <input type="hidden" id="prevRecruitedBy" value="{{ old('staffrecruitedby') }}" />
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffRecruitedBy" name="staffrecruitedby">
                                    @php
                                        $selectedRecruitedBy = old('staffrecruitedby');
                                    @endphp
                                </select>
                            </div>
                            <div>
                                @php
                                    $prevStaffDateAccomplished = old('staffdateaccomplished');
                                @endphp
                                <label for="staffDateAccomplished" class="block text-sm font-medium text-gray-700 mb-2">Date Accomplished</label>
                                <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffDateAccomplished" name="staffdateaccomplished" maxlength="30" value="{{ $prevStaffDateAccomplished }}" />
                                    @error('staffdateaccomplished')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                </div>
            </div>
            
            <!-- PERSONAL Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-1">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        PERSONAL
                    </h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                @php
                                    $prevStaffFirstName = old('stafffirstname');
                                @endphp
                                <label for="staffFirstName" class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffFirstName" name="stafffirstname" maxlength="30" value="{{ $prevStaffFirstName }}" required />
                                @error('stafffirstname')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffLastName = old('stafflastname');
                                @endphp
                                <label for="staffLastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffLastName" name="stafflastname" maxlength="30" value="{{ $prevStaffLastName }}" required />
                                    @error('stafflastname')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffMiddleName = old('staffmiddlename');
                                @endphp
                                <label for="staffMiddleName" class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffMiddleName" name="staffmiddlename" maxlength="30" value="{{ $prevStaffMiddleName }}" />
                                    @error('staffmiddlename')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                <label for="staffGender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffGender" name="staffgender">
                                    @php
                                        $selectedGender = old('staffgender');
                                    @endphp
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    @if($selectedGender != null)
                                        <option hidden selected value="{{ $selectedGender }}">{{ $selectedGender }}</option>
                                    @endif
                                </select>        
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                            <div>
                                @php
                                    $prevStaffBirthDate = old('staffbirthdate');
                                @endphp
                                <label for="staffBirthDate" class="block text-sm font-medium text-gray-700 mb-2">Birth Date</label>
                                <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffBirthDate" name="staffbirthdate" maxlength="30" value="{{ $prevStaffBirthDate }}" />
                                    @error('staffbirthdate')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffAge = old('staffage');
                                @endphp
                                <label for="staffAge" class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffAge" name="staffage" maxlength="30" readonly />
                                    @error('staffage')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffBirthPlace = old('staffbirthplace');
                                @endphp
                                <label for="staffBirthPlace" class="block text-sm font-medium text-gray-700 mb-2">Birth Place</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffBirthPlace" name="staffbirthplace" maxlength="30" value="{{ $prevStaffBirthPlace }}" />
                                    @error('staffbirthplace')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffNationality = old('staffnationality');
                                @endphp
                                <label for="staffNationality" class="block text-sm font-medium text-gray-700 mb-2">Nationality</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffNationality" name="staffnationality" maxlength="30" value="{{ $prevStaffNationality }}" />
                                    @error('staffnationality')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                            <div>
                                @php
                                    $selectedCivilStatus = old('staffcivilstatus');
                                @endphp
                                <label for="staffCivilStatus" class="block text-sm font-medium text-gray-700 mb-2">Civil Status</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffCivilStatus" name="staffcivilstatus">
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
                            <div>
                                @php
                                    $prevStaffSss = old('staffsss');
                                @endphp
                                <label for="staffSss" class="block text-sm font-medium text-gray-700 mb-2">SSS</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffSss" name="staffsss" maxlength="30" value="{{ $prevStaffSss }}" />
                                    @error('staffsss')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffTin = old('stafftin');
                                @endphp
                                <label for="staffTin" class="block text-sm font-medium text-gray-700 mb-2">TIN</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffTin" name="stafftin" maxlength="30" value="{{ $prevStaffTin }}" />
                                    @error('stafftin')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffGsis = old('staffgsis');
                                @endphp
                                <label for="staffGsis" class="block text-sm font-medium text-gray-700 mb-2">GSIS</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffGsis" name="staffgsis" maxlength="30" value="{{ $prevStaffGsis }}" />
                                    @error('staffgsis')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                            <div>
                                @php
                                    $prevStaffSpouse = old('staffspouse');
                                @endphp
                                <label for="staffSpouse" class="block text-sm font-medium text-gray-700 mb-2">Spouse</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffSpouse" name="staffspouse" maxlength="30" value="{{ $prevStaffSpouse }}" />
                                    @error('staffspouse')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffSpouseOccu = old('staffspouseoccu');
                                @endphp
                                <label for="staffSpouseOccu" class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffSpouseOccu" name="staffspouseoccu" maxlength="30" value="{{ $prevStaffSpouseOccu }}" />
                                    @error('staffspouseoccu')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffNoOfDependencies = old('staffnoofdependencies');
                                @endphp
                                <label for="staffNoOfDependencies" class="block text-sm font-medium text-gray-700 mb-2">No. of Dependencies</label>
                                <input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffNoOfDependencies" name="staffnoofdependencies" maxlength="30" value="{{ $prevStaffNoOfDependencies }}" />
                                    @error('staffnoofdependencies')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                </div>
            </div>
            
            <!-- ADDRESS Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-1">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        ADDRESS
                    </h3>
                </div>
                <div class="p-6">
                        <!-- Address Fields -->
                        <div class="mt-0 pt-0">
                            <h4 class="text-md font-semibold text-gray-700 mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                Address Information
                            </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Staff Address Region -->
                            <div>
                                @php
                                    $selectedStaffAddressRegion = old('staff_address_region');
                                @endphp
                                <label for="staffAddressRegion" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffAddressRegion" name="staff_address_region">
                                    <option value="">Select Region</option>
                                    @foreach($addressRegions as $region)
                                        <option value="{{ $region->code }}" {{ $selectedStaffAddressRegion == $region->code ? 'selected' : '' }}>
                                            {{ $region->description }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('staff_address_region')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Staff Address Province -->
                            <div>
                                @php
                                    $selectedStaffAddressProvince = old('staff_address_province');
                                @endphp
                                <label for="staffAddressProvince" class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffAddressProvince" name="staff_address_province">
                                    <option value="">Select Province</option>
                                </select>
                                @error('staff_address_province')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Staff Address City -->
                            <div>
                                @php
                                    $selectedStaffAddressCity = old('staff_address_city');
                                @endphp
                                <label for="staffAddressCity" class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffAddressCity" name="staff_address_city">
                                    <option value="">Select City/Municipality</option>
                                </select>
                                @error('staff_address_city')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Staff Address Barangay -->
                            <div>
                                @php
                                    $selectedStaffAddressBarangay = old('staff_address_barangay');
                                @endphp
                                <label for="staffAddressBarangay" class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffAddressBarangay" name="staff_address_barangay">
                                    <option value="">Select Barangay</option>
                                </select>
                                @error('staff_address_barangay')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                            <div>
                                @php
                                    $prevStaffZipcode = old('staff_zipcode');
                                @endphp
                                <label for="staffZipcode" class="block text-sm font-medium text-gray-700 mb-2">ZIP code</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffZipcode" name="staff_zipcode" maxlength="10" value="{{ $prevStaffZipcode }}" placeholder="Select city first" title="Select a city to auto-fill zipcode or enable manual input" />
                            </div>
                            <div>
                                @php
                                    $prevStaffStreet = old('staff_street');
                                @endphp
                                <label for="staffStreet" class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffStreet" name="staff_street" value="{{ $prevStaffStreet }}" />
                            </div>
                        </div>
                </div>
                </div>
            </div>
            
            <!-- CONTACT Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-1">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                        CONTACT
                    </h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div>
                                @php
                                    $prevStaffTelephone = old('stafftelephone');
                                @endphp
                                <label for="staffTelephone" class="block text-sm font-medium text-gray-700 mb-2">Telephone</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffTelephone" name="stafftelephone" maxlength="30" value="{{ $prevStaffTelephone }}" />
                                    @error('stafftelephone')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                <label for="staffMobileNo" class="block text-sm font-medium text-gray-700 mb-2">Mobile (+63)</label>
                                @php 
                                    $selectedMobileNo = old('staffmobileno'); 
                                @endphp
                                <div class="flex gap-2">
                                    <span class="inline-flex items-center px-3 rounded-lg border border-gray-300 bg-gray-50 text-gray-600 font-medium">+63</span>
                                    <input type="text" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffMobileNo" name="staffmobileno" inputmode="numeric" pattern="\d{10}" maxlength="10" placeholder="9123456789" value="{{ $selectedMobileNo }}" />
                                </div>
                                <div>
                                    @error('staffmobileno')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label for="staffEmail" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <div class="flex gap-2">
                                    @php 
                                        $selectedEmail = old('staffemail'); 
                                        $selectedEmailAddress = old('staffemailaddress'); 
                                        $customEmailDomain = old('staffcustomemaildomain');
                                    @endphp
                                    <input type="text" class="w-1/2 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffEmail" name="staffemail" maxlength="30" value="{{ $selectedEmail }}" />
                                    <span class="flex items-center px-3 text-gray-600 font-medium">@</span>
                                    <select class="w-1/2 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffEmailDomain" name="staffemailaddress" onchange="toggleStaffCustomEmailDomain()">
                                        @foreach($emails as $email)
                                            <option value="{{ $email->Email }}" {{ $selectedEmailAddress == $email->Email ? 'selected' : '' }}>
                                                {{ $email->Email }}
                                            </option>
                                        @endforeach
                                        <option value="others" {{ $selectedEmailAddress == 'others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 mt-2" id="staffCustomEmailDomain" name="staffcustomemaildomain" placeholder="Enter custom domain (e.g., company.com)" maxlength="50" value="{{ $customEmailDomain }}" style="display: {{ $selectedEmailAddress == 'others' ? 'block' : 'none' }};" />
                                <div class="space-y-1 mt-1">
                                    @error('staffemail')
                                    <p class="text-red-600 text-sm">{{ $message }}</p>
                                    @enderror
                                    @error('staffcustomemaildomain')
                                    <p class="text-red-600 text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            
            <!-- EDUCATIONAL BACKGROUND Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-1">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                        </svg>
                        EDUCATIONAL BACKGROUND
                    </h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                @php
                                    $prevStaffLastSchool = old('stafflastschool');
                                @endphp
                                <label for="staffLastSchool" class="block text-sm font-medium text-gray-700 mb-2">Last School Attended</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffLastSchool" name="stafflastschool" maxlength="30" value="{{ $prevStaffLastSchool }}" />
                                    @error('stafflastschool')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffEducationalAttainment = old('staffleducationalattainment');
                                @endphp
                                <label for="staffEducationalAttainment" class="block text-sm font-medium text-gray-700 mb-2">Educational Attainment</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffEducationalAttainment" name="staffleducationalattainment" maxlength="30" value="{{ $prevStaffEducationalAttainment }}" />
                                    @error('staffleducationalattainment')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                </div>
            </div>
            
            <!-- WORK HISTORY Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-1">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.951 22.951 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                        </svg>
                        WORK HISTORY
                    </h3>
                </div>
                <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                @php
                                    $prevStaffCompany = old('staffcompany');
                                @endphp
                                <label for="staffCompany" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffCompany" name="staffcompany" maxlength="30" value="{{ $prevStaffCompany }}" />
                                    @error('staffcompany')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffWorkNature = old('staffworknature');
                                @endphp
                                <label for="staffWorkNature" class="block text-sm font-medium text-gray-700 mb-2">Nature of Work</label>
                                <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffWorkNature" name="staffworknature" maxlength="30" value="{{ $prevStaffWorkNature }}" />
                                    @error('staffworknature')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffWorkStartDate = old('staffworkstartdate');
                                @endphp
                                <label for="staffWorkStartDate" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffWorkStartDate" name="staffworkstartdate" maxlength="30" value="{{ $prevStaffWorkStartDate }}" />
                                    @error('staffworkstartdate')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div>
                                @php
                                    $prevStaffWorkEndDate = old('staffworkenddate');
                                @endphp
                                <label for="staffWorkEndDate" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" id="staffWorkEndDate" name="staffworkenddate" maxlength="30" value="{{ $prevStaffWorkEndDate }}" />
                                    @error('staffworkenddate')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                </div>
            </div>
            <!-- Submit Button -->
            <div class="flex justify-center mt-8">
                <button type="submit" class="px-12 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Submit Staff Information
                    </span>
                </button>
            </div>
        </form>
    </div>
    <script src="{{ asset('js/staff-create.js') }}"></script>
    <script src="{{ asset('js/staff-address-cascading.js') }}"></script>
    <script>
        function toggleStaffCustomEmailDomain() {
            const emailDomainSelect = document.getElementById('staffEmailDomain');
            const customEmailDomain = document.getElementById('staffCustomEmailDomain');
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

        function normalizeStaffCustomEmail() {
            const emailInput = document.getElementById('staffEmail');
            const emailDomainSelect = document.getElementById('staffEmailDomain');
            const customDomainInput = document.getElementById('staffCustomEmailDomain');

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

        document.addEventListener('DOMContentLoaded', function () {
            toggleStaffCustomEmailDomain();
            const emailDomainSelect = document.getElementById('staffEmailDomain');
            const customDomainInput = document.getElementById('staffCustomEmailDomain');
            if (emailDomainSelect) {
                emailDomainSelect.addEventListener('change', toggleStaffCustomEmailDomain);
            }

            if (customDomainInput) {
                ['blur', 'change'].forEach(evt => {
                    customDomainInput.addEventListener(evt, normalizeStaffCustomEmail);
                });
                customDomainInput.addEventListener('input', function () {
                    if (this.value.includes('@')) {
                        normalizeStaffCustomEmail();
                    }
                });
            }

            const staffForm = document.getElementById('staffCreateForm');
            if (staffForm) {
                let allowSubmit = false;
                
                // Prevent browser validation UI on all form fields
                const allInputs = staffForm.querySelectorAll('input, select, textarea');
                allInputs.forEach(field => {
                    field.addEventListener('invalid', function (e) {
                        e.preventDefault();
                    }, true);
                });
                
                // Add validation handler in capture phase FIRST
                staffForm.addEventListener('submit', function (e) {
                    // If we're allowing submission, let it through
                    if (allowSubmit) {
                        allowSubmit = false;
                        return true;
                    }
                    
                    // Prevent default browser validation
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Check for HTML5 validation
                    if (!staffForm.checkValidity()) {
                        // Validation failed - find first invalid field and focus on it
                        const requiredFields = staffForm.querySelectorAll('[required]');
                        for (let field of requiredFields) {
                            if (!field.value || !field.value.trim()) {
                                // Scroll to the field
                                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                // Focus on the field
                                field.focus();
                                break;
                            }
                        }
                        return false;
                    } else {
                        // Validation passed, allow form submission
                        allowSubmit = true;
                        staffForm.submit();
                    }
                }, true);
                
                // Add debugging handler in bubble phase (after validation)
                staffForm.addEventListener('submit', function () {
                    const emailInput = document.getElementById('staffEmail');
                    const emailDomainSelectEl = document.getElementById('staffEmailDomain');
                    const customDomainInput = document.getElementById('staffCustomEmailDomain');
                    const regionSelect = document.getElementById('staffAddressRegion');
                    const provinceSelect = document.getElementById('staffAddressProvince');
                    const citySelect = document.getElementById('staffAddressCity');
                    const barangaySelect = document.getElementById('staffAddressBarangay');

                    if (emailInput) {
                        emailInput.value = emailInput.value.trim();
                    }

                    if (customDomainInput) {
                        customDomainInput.value = customDomainInput.value.trim();
                    }

                    normalizeStaffCustomEmail();

                    console.log('Staff Email Debug -> email:', emailInput ? emailInput.value : null);
                    console.log('Staff Email Debug -> email (trimmed):', emailInput ? emailInput.value.trim() : null);
                    console.log('Staff Email Debug -> selected domain:', emailDomainSelectEl ? emailDomainSelectEl.value : null);
                    console.log('Staff Email Debug -> custom domain:', customDomainInput ? customDomainInput.value : null);
                    console.log('Staff Address Debug -> region:', regionSelect ? regionSelect.value : null);
                    console.log('Staff Address Debug -> province:', provinceSelect ? provinceSelect.value : null);
                    console.log('Staff Address Debug -> city:', citySelect ? citySelect.value : null);
                    console.log('Staff Address Debug -> barangay:', barangaySelect ? barangaySelect.value : null);

                    try {
                        const formData = new FormData(staffForm);
                        const debugPayload = Array.from(formData.entries());
                        console.log('Staff Form Payload Preview:', debugPayload);
                    } catch (err) {
                        console.error('Staff Form Debug -> error while inspecting FormData:', err);
                    }
                }, false);
            }
        });

    </script>
@endsection