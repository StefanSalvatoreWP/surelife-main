<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/staff.css') }}">
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-500 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">View Staff ({{ $staff->LastName . ', ' . $staff->FirstName }})</h1>
                    <p class="text-green-600 text-sm">View staff information and staff details</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
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
                <div class="p-6 space-y-6">
                    <!-- Identification Section -->
                    <div>
                        <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Identification</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Position</span>
                                <span class="text-gray-900">{{ $staff->Role }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Region</span>
                                <span class="text-gray-900">{{ $staff->RegionName }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Branch</span>
                                <span class="text-gray-900">{{ $staff->BranchName }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Scheme</span>
                                <span class="text-gray-900">{{ $staff->Scheme }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Recruited By</span>
                                <span class="text-gray-900">NA</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Date Accomplished</span>
                                <span class="text-gray-900">{{ $staff->DateAccomplished }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Section -->
                    <div>
                        <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Personal</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Name</span>
                                <span class="text-gray-900">{{ $staff->LastName . ', ' . $staff->FirstName . " " . $staff->MiddleName }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Gender</span>
                                <span class="text-gray-900">{{ $staff->Gender }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Birth Date</span>
                                <span class="text-gray-900">{{ $staff->BirthDate }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Age</span>
                                <span class="text-gray-900">{{ $staff->Age }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Birth Place</span>
                                <span class="text-gray-900">{{ $staff->BirthPlace }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Nationality</span>
                                <span class="text-gray-900">{{ $staff->Nationality }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Civil Status</span>
                                <span class="text-gray-900">{{ $staff->CivilStatus }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">SSS</span>
                                <span class="text-gray-900">{{ $staff->SSS }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">TIN</span>
                                <span class="text-gray-900">{{ $staff->TIN }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">GSIS</span>
                                <span class="text-gray-900">{{ $staff->GSIS }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Spouse</span>
                                <span class="text-gray-900">{{ $staff->Spouse }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Occupation</span>
                                <span class="text-gray-900">{{ $staff->Occupation }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">No. Of Dependents</span>
                                <span class="text-gray-900">{{ $staff->NoOfDependents }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div>
                        <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Address</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">City</span>
                                <span class="text-gray-900">{{ $staff->MunicipalityDisplay ?? $staff->Municipality }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Barangay</span>
                                <span class="text-gray-900">{{ $staff->BarangayDisplay ?? $staff->Barangay }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">ZIP Code</span>
                                <span class="text-gray-900">{{ $staff->ZipCode }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Street</span>
                                <span class="text-gray-900">{{ $staff->Street }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Subdivision</span>
                                <span class="text-gray-900">{{ $staff->Subdivision }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Section -->
                    <div>
                        <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Contact</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Telephone</span>
                                <span class="text-gray-900">{{ $staff->TelephoneNumber }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Mobile</span>
                                <span class="text-gray-900">{{ $staff->MobileNumber }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Email Address</span>
                                <span class="text-gray-900">{{ $staff->EmailAddress }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Educational Section -->
                    <div>
                        <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Educational</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Educational Attainment</span>
                                <span class="text-gray-900">{{ $staff->EducationalAttainment }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Last School Attended</span>
                                <span class="text-gray-900">{{ $staff->LastSchoolAttended }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Work History Section -->
                    <div>
                        <h4 class="text-sm font-bold text-purple-700 uppercase tracking-wide mb-3 pb-2 border-b-2 border-purple-200">Work History</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Company Name</span>
                                <span class="text-gray-900">{{ $staff->CompanyName }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Nature of Work</span>
                                <span class="text-gray-900">{{ $staff->WorkNature }}</span>
                            </div>
                            <div class="flex justify-between py-2 hover:bg-purple-50 px-2 rounded transition">
                                <span class="text-gray-600 font-medium">Start | End Date</span>
                                <span class="text-gray-900">{{ $staff->StartDateC . ' | ' . $staff->EndDateC }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clients Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Clients
                    </h3>
                </div>
                <div class="w-full">
                    <table id="common_dataTable" class="w-full display" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Contract No.</th>
                                <th>Name</th>
                                <th>Date Recruited</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $clientKey => $clientIndex)
                                <tr>
                                    <td>{{ $clientKey + 1 }}</td>
                                    @if(empty($clientIndex->ContractNumber))
                                        <td><span class="text-gray-400 italic">Not available</span></td>
                                    @else
                                        <td>{{ $clientIndex->ContractNumber }}</td>
                                    @endif
                                    <td>{{ $clientIndex->LastName . ', ' . $clientIndex->FirstName . ' ' . $clientIndex->MiddleName }}</td>
                                    <td>{{ $clientIndex->DateAccomplished }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/staff-view.js') }}"></script>
@endsection
