<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Update Staff</h1>
                <p class="text-gray-600 text-sm mb-4">Update selected staff information</p>
                
                @if(session('duplicate'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">{{ session('duplicate') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <a href="/staff-view/{{ $selstaff->Id }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Return
                </a>
            </div>
            <!-- Form Start -->
            <form action="/submit-staff-update/{{ $selstaff->Id }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- IDENTIFICATION Section -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-1">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4 pb-3 border-b border-gray-200">IDENTIFICATION</h2>
                        <div class="row">
                            <div class="col-sm-3">
                                @php
                                    $selectedPosition = old('staffposition', $selstaff->Position);
                                @endphp
                                <label for="staffPosition" class="form-label">Position</label>
                                <select class="form-control font-sm" id="staffPosition" name="staffposition">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->Id }}" {{ $selectedPosition == $role->Id ? 'selected' : '' }}>
                                            {{ $role->Role }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $selectedRegion = old('staffregion', $selstaff->RegionId);
                                @endphp
                                <label for="staffRegion" class="form-label">Region</label>
                                <select class="form-control font-sm" id="staffRegion" name="staffregion">
                                    @foreach($regions as $region)
                                        <option value="{{ $region->Id }}" {{ $selectedRegion == $region->Id ? 'selected' : '' }}>
                                            {{ $region->RegionName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $selectedBranch = old('staffbranch', $selstaff->BranchId);
                                @endphp
                                <label for="staffBranch" class="form-label">Branch</label>
                                <select class="form-control font-sm" id="staffBranch" name="staffbranch">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->Id }}" {{ $selectedBranch == $branch->Id ? 'selected' : '' }}>
                                            {{ $branch->BranchName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>     
                        <div class="row mt-4">
                            <div class="col-sm-3">
                                @php
                                    $selectedScheme = old('staffscheme', $selstaff->Scheme);
                                @endphp
                                <label for="staffScheme" class="form-label">Scheme</label>
                                <select class="form-control font-sm" id="staffScheme" name="staffscheme">
                                    <option value="Old">Old</option>
                                    <option value="New">New</option>
                                    @if($selectedScheme != null)
                                        <option hidden selected value="{{ $selectedScheme }}">{{ $selectedScheme }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $selectedRecruitedBy = old('staffrecruitedby', $selstaff->RecruitedBy);
                                @endphp
                                <label for="staffRecruitedBy" class="form-label">Recruited By</label>
                                <select class="form-control font-sm" id="staffRecruitedBy" name="staffrecruitedby">
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->Id }}" {{ $selectedRecruitedBy == $staff->Id ? 'selected' : '' }}>
                                            {{ $staff->LastName . ", " . $staff->FirstName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffDateAccomplished = old('staffdateaccomplished', $selstaff->DateAccomplished);
                                @endphp
                                <label for="staffDateAccomplished" class="form-label">Date Accomplished</label>
                                <input type="date" class="form-control font-sm" id="staffDateAccomplished" name="staffdateaccomplished" maxlength="30" value="{{ $prevStaffDateAccomplished }}" />
                                    @error('staffdateaccomplished')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 mt-1">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4 pb-3 border-b border-gray-200">PERSONAL</h2>
                        <div class="row">
                            <div class="col-sm-3">
                                @php
                                    $prevStaffFirstName = old('stafffirstname', $selstaff->FirstName);
                                @endphp
                                <label for="staffFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control font-sm" id="staffFirstName" name="stafffirstname" maxlength="30" value="{{ $prevStaffFirstName }}" />
                                    @error('stafffirstname')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffLastName = old('stafflastname', $selstaff->LastName);
                                @endphp
                                <label for="staffLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control font-sm" id="staffLastName" name="stafflastname" maxlength="30" value="{{ $prevStaffLastName }}" />
                                    @error('stafflastname')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffMiddleName = old('staffmiddlename', $selstaff->MiddleName);
                                @endphp
                                <label for="staffMiddleName" class="form-label">Middle Name</label>
                                <input type="text" class="form-control font-sm" id="staffMiddleName" name="staffmiddlename" maxlength="30" value="{{ $prevStaffMiddleName }}" />
                                    @error('staffmiddlename')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $selectedGender = old('staffgender', $selstaff->Gender);
                                @endphp
                                <label for="staffGender" class="form-label">Gender</label>
                                <select class="form-control font-sm" id="staffGender" name="staffgender">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    @if($selectedGender != null)
                                        <option hidden selected value="{{ $selectedGender }}">{{ $selectedGender }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-sm-3">
                                @php
                                    $prevStaffBirthDate = old('staffbirthdate', $selstaff->BirthDate);
                                @endphp
                                <label for="staffBirthDate" class="form-label">Birth Date</label>
                                <input type="date" class="form-control font-sm" id="staffBirthDate" name="staffbirthdate" maxlength="30" value="{{ $prevStaffBirthDate }}" />
                                    @error('staffbirthdate')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffAge = old('staffage', $selstaff->Age);
                                @endphp
                                <label for="staffAge" class="form-label">Age</label>
                                <input type="text" class="form-control font-sm" id="staffAge" name="staffage" maxlength="30" value="{{ $prevStaffAge }}" readonly />
                                    @error('staffage')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffBirthPlace = old('staffbirthplace', $selstaff->BirthPlace);
                                @endphp
                                <label for="staffBirthPlace" class="form-label">Birth Place</label>
                                <input type="text" class="form-control font-sm" id="staffBirthPlace" name="staffbirthplace" maxlength="30" value="{{ $prevStaffBirthPlace }}" />
                                    @error('staffbirthplace')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffNationality = old('staffnationality', $selstaff->Nationality);
                                @endphp
                                <label for="staffNationality" class="form-label">Nationality</label>
                                <input type="text" class="form-control font-sm" id="staffNationality" name="staffnationality" maxlength="30" value="{{ $prevStaffNationality }}" />
                                    @error('staffnationality')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-sm-3">
                                @php
                                    $selectedCivilStatus = old('staffcivilstatus', $selstaff->CivilStatus);
                                @endphp
                                <label for="staffCivilStatus" class="form-label">Civil Status</label>
                                <select class="form-control font-sm" id="staffCivilStatus" name="staffcivilstatus">
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
                            <div class="col-sm-3">
                                @php
                                    $prevStaffSss = old('staffsss', $selstaff->SSS);
                                @endphp
                                <label for="staffSss" class="form-label">SSS</label>
                                <input type="text" class="form-control font-sm" id="staffSss" name="staffsss" maxlength="30" value="{{ $prevStaffSss }}" />
                                    @error('staffsss')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffTin = old('stafftin', $selstaff->TIN);
                                @endphp
                                <label for="staffTin" class="form-label">TIN</label>
                                <input type="text" class="form-control font-sm" id="staffTin" name="stafftin" maxlength="30" value="{{ $prevStaffTin }}" />
                                    @error('stafftin')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffGsis = old('staffgsis', $selstaff->GSIS);
                                @endphp
                                <label for="staffGsis" class="form-label">GSIS</label>
                                <input type="text" class="form-control font-sm" id="staffGsis" name="staffgsis" maxlength="30" value="{{ $prevStaffGsis }}" />
                                    @error('staffgsis')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-sm-3">
                                @php
                                    $prevStaffSpouse = old('staffspouse', $selstaff->Spouse);
                                @endphp
                                <label for="staffSpouse" class="form-label">Spouse</label>
                                <input type="text" class="form-control font-sm" id="staffSpouse" name="staffspouse" maxlength="30" value="{{ $prevStaffSpouse }}" />
                                    @error('staffspouse')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffSpouseOccu = old('staffspouseoccu', $selstaff->Occupation);
                                @endphp
                                <label for="staffSpouseOccu" class="form-label">Occupation</label>
                                <input type="text" class="form-control font-sm" id="staffSpouseOccu" name="staffspouseoccu" maxlength="30" value="{{ $prevStaffSpouseOccu }}" />
                                    @error('staffspouseoccu')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffNoOfDependencies = old('staffnoofdependencies', $selstaff->NoOfDependents);
                                @endphp
                                <label for="staffNoOfDependencies" class="form-label">No. of Dependencies</label>
                                <input type="number" class="form-control font-sm" id="staffNoOfDependencies" name="staffnoofdependencies" maxlength="30" value="{{ $prevStaffNoOfDependencies }}" />
                                    @error('staffnoofdependencies')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 mt-1">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4 pb-3 border-b border-gray-200">ADDRESS</h2>
                        <div class="row">
                            @php
                                // For staff addresses, we need to extract region from municipality since RegionId is for branch assignment
                                $staffMunicipality = $selstaff->Municipality ?? '';
                                $mappedRegionCode = '';
                                
                                // Try to extract region from municipality name or code
                                if (!empty($staffMunicipality)) {
                                    // First try name-based detection (enhanced for old format)
                                    if (stripos($staffMunicipality, 'Cebu') !== false || stripos($staffMunicipality, 'Lapu-Lapu') !== false || stripos($staffMunicipality, 'Mandaue') !== false) {
                                        $mappedRegionCode = '07'; // REGION VII (CENTRAL VISAYAS)
                                    } elseif (stripos($staffMunicipality, 'Bohol') !== false) {
                                        $mappedRegionCode = '07'; // REGION VII (CENTRAL VISAYAS)
                                    } elseif (stripos($staffMunicipality, 'Negros') !== false) {
                                        if (stripos($staffMunicipality, 'Occidental') !== false) {
                                            $mappedRegionCode = '06'; // REGION VI (WESTERN VISAYAS)
                                        } else {
                                            $mappedRegionCode = '07'; // REGION VII (CENTRAL VISAYAS)
                                        }
                                    } elseif (stripos($staffMunicipality, 'Bacolod') !== false) {
                                        $mappedRegionCode = '06'; // REGION VI (WESTERN VISAYAS) - Bacolod is in Negros Occidental
                                    } elseif (stripos($staffMunicipality, 'Ubay') !== false || stripos($staffMunicipality, 'Bohol') !== false) {
                                        $mappedRegionCode = '07'; // REGION VII (CENTRAL VISAYAS) - Ubay is in Bohol
                                    } elseif (stripos($staffMunicipality, 'Leyte') !== false || stripos($staffMunicipality, 'Samar') !== false || stripos($staffMunicipality, 'Maslog') !== false) {
                                        $mappedRegionCode = '08'; // REGION VIII (EASTERN VISAYAS)
                                    } elseif (stripos($staffMunicipality, 'Davao') !== false) {
                                        $mappedRegionCode = '11'; // REGION XI (DAVAO REGION)
                                    } elseif (stripos($staffMunicipality, 'Cagayan') !== false) {
                                        $mappedRegionCode = '02'; // REGION II (CAGAYAN VALLEY)
                                    } elseif (stripos($staffMunicipality, 'Manila') !== false || stripos($staffMunicipality, 'Quezon') !== false || stripos($staffMunicipality, 'Caloocan') !== false) {
                                        $mappedRegionCode = '13'; // NCR
                                    } elseif (is_numeric($staffMunicipality)) {
                                        // Code-based detection - lookup the city and get its region
                                        try {
                                            $cityInfo = DB::table('tbladdress')
                                                ->where('code', $staffMunicipality)
                                                ->where('address_type', 'citymun')
                                                ->first();
                                            
                                            if ($cityInfo) {
                                                // Get province info
                                                $provinceInfo = DB::table('tbladdress')
                                                    ->where('code', $cityInfo->parent_code)
                                                    ->where('address_type', 'province')
                                                    ->first();
                                                
                                                if ($provinceInfo) {
                                                    // Get region info
                                                    $regionInfo = DB::table('tbladdress')
                                                        ->where('code', $provinceInfo->parent_code)
                                                        ->where('address_type', 'region')
                                                        ->first();
                                                    
                                                    if ($regionInfo) {
                                                        $mappedRegionCode = $regionInfo->code;
                                                    }
                                                }
                                            }
                                        } catch (Exception $e) {
                                            // Fallback: leave empty
                                        }
                                    }
                                }
                                
                                $selectedAddressRegion = old('staff_address_region', $mappedRegionCode);
                            @endphp
                            <div class="col-sm-3">
                                <label for="staffAddressRegion" class="form-label">Region</label>
                                <select class="form-control font-sm" id="staffAddressRegion" name="staff_address_region">
                                    <option value="">Select Region</option>
                                    @foreach($addressRegions as $region)
                                        <option value="{{ $region->code }}" {{ $selectedAddressRegion == $region->code ? 'selected' : '' }}>
                                            {{ $region->description }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('staff_address_region')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $selectedAddressProvince = old('staff_address_province', '');
                                @endphp
                                <label for="staffAddressProvince" class="form-label">Province</label>
                                <select class="form-control font-sm" id="staffAddressProvince" name="staff_address_province">
                                    <option value="">Select Province</option>
                                </select>
                                @error('staff_address_province')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $selectedAddressCity = old('staff_address_city', $selstaff->Municipality ?? '');
                                @endphp
                                <label for="staffAddressCity" class="form-label">City/Municipality</label>
                                <select class="form-control font-sm" id="staffAddressCity" name="staff_address_city">
                                    <option value="">Select City/Municipality</option>
                                </select>
                                @error('staff_address_city')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $selectedAddressBarangay = old('staff_address_barangay', $selstaff->Barangay ?? '');
                                @endphp
                                <label for="staffAddressBarangay" class="form-label">Barangay</label>
                                <select class="form-control font-sm" id="staffAddressBarangay" name="staff_address_barangay">
                                    <option value="">Select Barangay</option>
                                </select>
                                @error('staff_address_barangay')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-sm-3">
                                @php
                                    $prevStaffZipcode = old('staff_zipcode', $selstaff->ZipCode);
                                @endphp
                                <label for="staffZipcode" class="form-label">ZIP code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control font-sm" id="staffZipcode" name="staff_zipcode" maxlength="30" value="{{ $prevStaffZipcode }}" />
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="editZipcodeBtn" title="Edit zipcode manually" style="display: none;">
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.324z"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('staff_zipcode')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-6">
                                @php
                                    $prevStaffStreet = old('staff_street', $selstaff->Street);
                                @endphp
                                <label for="staffStreet" class="form-label">Street</label>
                                <input type="text" class="form-control font-sm" id="staffStreet" name="staff_street" maxlength="30" value="{{ $prevStaffStreet }}" />
                                @error('staff_street')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                                    @error('staffsubdiv')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 mt-1">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4 pb-3 border-b border-gray-200">CONTACT</h2>
                        <div class="row">
                            <div class="col-sm-3">
                                @php
                                    $prevStaffTelephone = old('stafftelephone', $selstaff->TelephoneNumber);
                                @endphp
                                <label for="staffTelephone" class="form-label">Telephone</label>
                                <input type="text" class="form-control font-sm" id="staffTelephone" name="stafftelephone" maxlength="30" value="{{ $prevStaffTelephone }}" />
                                    @error('stafftelephone')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                <label for="staffMobileNo" class="form-label">Mobile (+63)</label>
                                @php 
                                    $rawMobile = $selstaff->MobileNumber ?? '';
                                    $mobileDigits = preg_replace('/\D/', '', $rawMobile);
                                    if (strlen($mobileDigits) >= 11) {
                                        $mobileDigits = substr($mobileDigits, 1);
                                    }
                                    $selectedMobileNo = old('staffmobileno', $mobileDigits);
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text">+63</span>
                                    <input type="text" class="form-control font-sm" id="staffMobileNo" name="staffmobileno" inputmode="numeric" pattern="\d{10}" maxlength="10" placeholder="9123456789" value="{{ $selectedMobileNo }}" />
                                </div>
                                <div>
                                    @error('staffmobileno')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="staffEmail" class="form-label">Email</label>
                                <div class="input-group">
                                    @php 
                                        $origEmail = $selstaff->EmailAddress;
                                        $emailLocal = '';
                                        $emailDomain = '';
                                        if(!empty($origEmail) && strpos($origEmail, '@') !== false){
                                            [$emailLocal, $emailDomain] = explode('@', $origEmail, 2);
                                        }
                                        $availableDomains = $emails->pluck('Email')->toArray();
                                        $selectedEmail = old('staffemail', $emailLocal);
                                        $selectedEmailAddress = old('staffemailaddress', $emailDomain);
                                        $customEmailDomain = old('staffcustomemaildomain');
                                        if (old('staffemailaddress') === null && $emailDomain && !in_array($emailDomain, $availableDomains)) {
                                            $selectedEmailAddress = 'others';
                                            $customEmailDomain = $emailDomain;
                                        }
                                    @endphp
                                    <input type="text" class="form-control font-sm" id="staffEmail" name="staffemail" maxlength="30" value="{{ $selectedEmail }}" />
                                    <span class="input-group-text" id="staffEmailDomainAddon">@</span>
                                    <select class="form-control font-sm" id="staffEmailDomain" name="staffemailaddress" onchange="toggleStaffCustomEmailDomain()">
                                        @foreach($emails as $email)
                                            <option value="{{ $email->Email }}" {{ $selectedEmailAddress == $email->Email ? 'selected' : '' }}>
                                                {{ $email->Email }}
                                            </option>
                                        @endforeach
                                        <option value="others" {{ $selectedEmailAddress == 'others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <input type="text" class="form-control font-sm mt-2" id="staffCustomEmailDomain" name="staffcustomemaildomain" placeholder="Enter custom domain (e.g., company.com)" maxlength="50" value="{{ $customEmailDomain }}" style="display: {{ $selectedEmailAddress == 'others' ? 'block' : 'none' }};" />
                                <div class="mt-1">
                                    @error('staffemail')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                    @error('staffcustomemaildomain')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 mt-1">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4 pb-3 border-b border-gray-200">EDUCATIONAL BACKGROUND</h2>
                        <div class="row">
                            <div class="col-sm-3">
                                @php
                                    $prevStaffLastSchool = old('stafflastschool', $selstaff->LastSchoolAttended);
                                @endphp
                                <label for="staffLastSchool" class="form-label">Last School Attended</label>
                                <input type="text" class="form-control font-sm" id="staffLastSchool" name="stafflastschool" maxlength="30" value="{{ $prevStaffLastSchool }}" />
                                    @error('stafflastschool')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffEducationalAttainment = old('staffleducationalattainment', $selstaff->EducationalAttainment);
                                @endphp
                                <label for="staffEducationalAttainment" class="form-label">Educational Attainment</label>
                                <input type="text" class="form-control font-sm" id="staffEducationalAttainment" name="staffleducationalattainment" maxlength="30" value="{{ $prevStaffEducationalAttainment }}" />
                                    @error('staffleducationalattainment')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 mt-1">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4 pb-3 border-b border-gray-200">WORK HISTORY</h2>
                        <div class="row">
                            <div class="col-sm-3">
                                @php
                                    $prevStaffCompany = old('staffcompany', $selstaff->CompanyName);
                                @endphp
                                <label for="staffCompany" class="form-label">Company</label>
                                <input type="text" class="form-control font-sm" id="staffCompany" name="staffcompany" maxlength="30" value="{{ $prevStaffCompany }}" />
                                    @error('staffcompany')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffWorkNature = old('staffworknature', $selstaff->WorkNature);
                                @endphp
                                <label for="staffWorkNature" class="form-label">Nature of Work</label>
                                <input type="text" class="form-control font-sm" id="staffWorkNature" name="staffworknature" maxlength="30" value="{{ $prevStaffWorkNature }}" />
                                    @error('staffworknature')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffWorkStartDate = old('staffworkstartdate', $selstaff->StartDateC);
                                @endphp
                                <label for="staffWorkStartDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control font-sm" id="staffWorkStartDate" name="staffworkstartdate" maxlength="30" value="{{ $prevStaffWorkStartDate }}" />
                                    @error('staffworkstartdate')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col-sm-3">
                                @php
                                    $prevStaffWorkEndDate = old('staffworkenddate', $selstaff->EndDateC);
                                @endphp
                                <label for="staffWorkEndDate" class="form-label">End Date</label>
                                <input type="date" class="form-control font-sm" id="staffWorkEndDate" name="staffworkenddate" maxlength="30" value="{{ $prevStaffWorkEndDate }}" />
                                    @error('staffworkenddate')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-sm transition duration-200">Submit</button>
            </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/staff-update.js') }}"></script>
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
            const birthDateInput = document.getElementById('staffBirthDate');
            const ageInput = document.getElementById('staffAge');
            
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

            // Automatic age calculation
            if (birthDateInput && ageInput) {
                function calculateAge() {
                    const birthDate = new Date(birthDateInput.value);
                    const today = new Date();
                    
                    if (birthDateInput.value && !isNaN(birthDate.getTime())) {
                        let age = today.getFullYear() - birthDate.getFullYear();
                        const monthDiff = today.getMonth() - birthDate.getMonth();
                        const dayDiff = today.getDate() - birthDate.getDate();
                        
                        // Adjust age if birthday hasn't occurred yet this year
                        if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                            age--;
                        }
                        
                        ageInput.value = age;
                    } else {
                        ageInput.value = '';
                    }
                }
                
                // Calculate age on birth date change
                birthDateInput.addEventListener('change', calculateAge);
                
                // Calculate age on input (for immediate feedback)
                birthDateInput.addEventListener('input', calculateAge);
                
                // Calculate age on page load
                calculateAge();
            }

            const staffForm = document.querySelector('form[action^="/submit-staff-update"]');
            if (staffForm) {
                staffForm.addEventListener('submit', function () {
                    const emailInput = document.getElementById('staffEmail');
                    const emailDomainSelectEl = document.getElementById('staffEmailDomain');
                    const customDomainInput = document.getElementById('staffCustomEmailDomain');
                    const regionSelect = document.getElementById('staffRegion');
                    const branchSelect = document.getElementById('staffBranch');
                    const zipcodeInput = document.getElementById('staffZipcode');

                    // IMPORTANT: Make zipcode editable before submission to ensure it's sent
                    if (zipcodeInput) {
                        const originalReadonly = zipcodeInput.readOnly;
                        zipcodeInput.readOnly = false;
                        console.log('Zipcode readonly temporarily disabled for submission (was:', originalReadonly, ')');
                    }

                    if (emailInput) {
                        emailInput.value = emailInput.value.trim();
                    }

                    if (customDomainInput) {
                        customDomainInput.value = customDomainInput.value.trim();
                    }

                    normalizeStaffCustomEmail();

                    console.log('Staff Update Debug -> email:', emailInput ? emailInput.value : null);
                    console.log('Staff Update Debug -> selected domain:', emailDomainSelectEl ? emailDomainSelectEl.value : null);
                    console.log('Staff Update Debug -> custom domain:', customDomainInput ? customDomainInput.value : null);
                    console.log('Staff Update Debug -> region:', regionSelect ? regionSelect.value : null);
                    console.log('Staff Update Debug -> branch:', branchSelect ? branchSelect.value : null);
                    console.log('Staff Update Debug -> zipcode:', zipcodeInput ? zipcodeInput.value : null);
                    console.log('Staff Update Debug -> zipcode readonly after fix:', zipcodeInput ? zipcodeInput.readOnly : null);
                });
            }
        });
    </script>
    
    <!-- Hidden inputs for staff address cascading system -->
    <input type="hidden" id="oldStaffAddressRegion" value="{{ $selectedAddressRegion }}" />
    <input type="hidden" id="oldStaffProvince" value="{{ old('staff_address_province', '') }}" />
    <input type="hidden" id="oldStaffMunicipality" value="{{ $selstaff->Municipality ?? '' }}" />
    <input type="hidden" id="oldStaffBarangay" value="{{ $selstaff->Barangay ?? '' }}" />
    <input type="hidden" id="staff_zipcode_backup" name="staff_zipcode" value="{{ $prevStaffZipcode }}" />
    
    <script src="{{ asset('js/staff-address-cascading.js') }}"></script>
@endsection