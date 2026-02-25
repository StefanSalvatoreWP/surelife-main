<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">View Staff ({{ $staff->LastName . ', ' . $staff->FirstName }})</h1>
                    <p class="text-purple-100 text-sm">View staff information and staff details</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-purple-200 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @elseif(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @elseif(session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-yellow-700 font-medium">{{ session('warning') }}</p>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex gap-4 mb-6">
            <a href="/staff" class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Return
            </a>
            <a href="/staff-update/{{ $staff->Id }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Update
            </a>
        </div>
        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Staff Information Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Staff Information
                    </h3>
                </div>
                <div class="p-6">
                    <!-- Identification Section -->
                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                            Identification
                        </h4>
                        <div class="space-y-3">
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Position</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Role }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Region</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->RegionName }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Branch</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->BranchName }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Scheme</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Scheme }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>RecruitedBy</p>
                            </div>
                            <div class="col-sm-6">
                                <p>NA</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Date Accomplished</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->DateAccomplished }}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-4">
                                <p class="fw-bold">Personal</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Name</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->LastName . ', ' . $staff->FirstName . " " . $staff->MiddleName }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Gender</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Gender }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>BirthDate</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->BirthDate }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Age</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Age }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>BirthPlace</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->BirthPlace }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Nationality</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Nationality }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Civil Status</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->CivilStatus }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>SSS</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->SSS }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Tax Identification Number</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->TIN }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>GSIS</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->GSIS }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Spouse</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Spouse }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Occupation</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Occupation }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>No. Of Dependencies</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->NoOfDependents }}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-4">
                                <p class="fw-bold">Address</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>City</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Municipality }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Barangay</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Barangay }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>ZIP code</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->ZipCode }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Street</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Street }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Subdivision</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->Subdivision }}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-4">
                                <p class="fw-bold">Contact</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Telephone</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->TelephoneNumber }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Mobile</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->MobileNumber }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Email Address</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->EmailAddress }}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-4">
                                <p class="fw-bold">Educational</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Educational Attainment</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->EducationalAttainment }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Last school attended</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->LastSchoolAttended }}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-4">
                                <p class="fw-bold">Work History</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Work History</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->CompanyName }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Nature of Work</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->WorkNature }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p>Start | End Date</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{ $staff->StartDateC . ' | ' . $staff->EndDateC }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mt-2 text-dark fw-bold">Clients</h5>
                        <div class="mt-5"></div>
                        <table id="common_dataTable" class="table table-hover table-striped mt-5 font-sm">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Contract No.</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Date Recruited</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clients as $clientKey => $clientIndex)
                                    <tr>
                                        <td>{{ $clientKey + 1 }}</td>
                                        @if(empty($clientIndex->ContractNumber))
                                            <td><span class="text-secondary">Not available</span>
                                        @else
                                            <td>{{ $clientIndex->ContractNumber }}</td>
                                        @endif
                                        <td>{{ $clientIndex->LastName . ', ' . $clientIndex->FirstName . ' ' . $clientIndex->MiddleName }}</td>
                                        <td>{{ $clientIndex->DateAccomplished }} </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/staff-view.js') }}"></script>
@endsection