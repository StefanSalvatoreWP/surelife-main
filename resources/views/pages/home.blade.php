<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- What's New Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <h2 class="text-xl font-bold text-white">What's New?</h2>
                        <span class="ml-auto bg-blue-500 text-white text-xs font-semibold px-3 py-1 rounded-full">v1.7.0</span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Activity Log -->
                        <div class="flex items-start space-x-3 p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Activity Log</h3>
                                <p class="text-sm text-gray-600 mt-1">This feature logs every action on the server, allowing for the identification of who performed each action within the system.</p>
                            </div>
                        </div>

                        <!-- Loan Admin View -->
                        <div class="flex items-start space-x-3 p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Loan (Admin View)</h3>
                                <p class="text-sm text-gray-600 mt-1">New feature where admins manage loan requests.</p>
                            </div>
                        </div>

                        <!-- Loan Client View -->
                        <div class="flex items-start space-x-3 p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Loan (Client View)</h3>
                                <p class="text-sm text-gray-600 mt-1">New feature where eligible clients can make loan requests.</p>
                            </div>
                        </div>

                        <!-- Completed Memorial -->
                        <div class="flex items-start space-x-3 p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Completed Memorial</h3>
                                <p class="text-sm text-gray-600 mt-1">This action marks the selected client's memorial plan as served.</p>
                            </div>
                        </div>

                        <!-- Certificate of Full Payment -->
                        <div class="flex items-start space-x-3 p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Certificate of Full Payment</h3>
                                <p class="text-sm text-gray-600 mt-1">Added <span class="font-bold text-gray-700">"Not valid without seal"</span> text.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bug Fixes Section -->
                    <div class="mt-8">
                        <div class="flex items-center space-x-2 mb-4">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-900">Bug Fixes</h3>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">v1.7.1</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                                <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-sm text-gray-900">Client View</h4>
                                    <p class="text-xs text-gray-600 mt-0.5">Selected tab will remain active when performing actions instead of returning to the 'Client Information'.</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                                <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-sm text-gray-900">Payment History</h4>
                                    <p class="text-xs text-gray-600 mt-0.5">Fixed issues where void payments is not displayed when selected.</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                                <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-sm text-gray-900">Statement of Accounts (SOA)</h4>
                                    <p class="text-xs text-gray-600 mt-0.5">Fixed issues where void payments is still visible under payments history.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <h2 class="text-xl font-bold text-white">Tips & Guidelines</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Tip 1 -->
                        <div class="flex items-start space-x-3 p-4 bg-amber-50 rounded-lg border-l-4 border-amber-500">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-gray-700">Only <span class="font-bold text-blue-600">Admins</span> have access to menus such as Documents and Systems.</p>
                        </div>

                        <!-- Tip 2 -->
                        <div class="flex items-start space-x-3 p-4 bg-amber-50 rounded-lg border-l-4 border-amber-500">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-gray-700">Check clients information before you verify and approve them.</p>
                        </div>

                        <!-- Tip 3 -->
                        <div class="flex items-start space-x-3 p-4 bg-amber-50 rounded-lg border-l-4 border-amber-500">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-gray-700">Succeeding payments can <span class="font-bold text-blue-600">only</span> be made for approved clients.</p>
                        </div>

                        <!-- Tip 4 -->
                        <div class="p-4 bg-amber-50 rounded-lg border-l-4 border-amber-500">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 font-semibold mb-2">Before you proceed with adding new client or updating existing client, make sure you have:</p>
                                    <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 ml-2">
                                        <li>Existing and active package.</li>
                                        <li>Official Receipt for the selected branch.</li>
                                        <li>Contracts for the selected branch.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Tip 5 -->
                        <div class="flex items-start space-x-3 p-4 bg-red-50 rounded-lg border-l-4 border-red-500">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-gray-700"><span class="font-bold">Warning:</span> Approved clients can't be deleted.</p>
                        </div>

                        <!-- Tip 6 -->
                        <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 font-semibold mb-2">You can view used Contracts and Official Receipts under:</p>
                                    <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 ml-2">
                                        <li>Documents → Contracts → View Series</li>
                                        <li>Documents → Official Receipts → View Series</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Tip 7 -->
                        <div class="flex items-start space-x-3 p-4 bg-amber-50 rounded-lg border-l-4 border-amber-500">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-gray-700">Existing contracts and official receipts cannot be updated. Remove it and add it again if it was meant for another purpose or was entered incorrectly.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection