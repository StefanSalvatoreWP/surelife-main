<!-- 2024 SilverDust) S. Maceren -->
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
                        @if($latestVersion ?? null)
                            <span class="ml-auto bg-blue-500 text-white text-xs font-semibold px-3 py-1 rounded-full">v{{ $latestVersion['version'] }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="p-6 max-h-[500px] overflow-y-auto">
                    @if($error ?? false)
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                            <p class="text-red-700">{{ $error }}</p>
                        </div>
                    @elseif(empty($changelog))
                        <p class="text-gray-500 text-center py-8">No changelog entries available.</p>
                    @else
                        @foreach($changelog as $version)
                            <!-- Version Section -->
                            <div class="{{ $loop->first ? '' : 'mt-8 pt-6 border-t border-gray-200' }}">
                                
                                <!-- Version Header -->
                                <div class="flex items-center space-x-2 mb-4">
                                    @if($loop->first)
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Latest</span>
                                    @endif
                                    <h3 class="text-base font-bold text-gray-900">
                                        @if($version['version'] === 'Unreleased')
                                            Coming Soon
                                        @else
                                            v{{ $version['version'] }}
                                        @endif
                                    </h3>
                                    @if($version['date'])
                                        <span class="text-xs text-gray-400">{{ $version['date'] }}</span>
                                    @endif
                                </div>

                                <!-- Added Section -->
                                @if(isset($version['sections']['added']) && !empty($version['sections']['added']))
                                    <div class="space-y-3 mb-4">
                                        @foreach($version['sections']['added'] as $item)
                                            <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg {{ $loop->first ? '' : 'hover:bg-blue-100 transition duration-200' }}">
                                                <div class="flex-shrink-0 mt-1">
                                                    <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                                </div>
                                                <div>
                                                    @if($item['has_bold'] ?? false)
                                                        <h4 class="font-semibold text-sm text-gray-900">{{ $item['title'] }}</h4>
                                                        <p class="text-xs text-gray-600 mt-0.5">{{ $item['description'] }}</p>
                                                    @else
                                                        <p class="text-xs text-gray-700">{!! $item['description'] !!}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Changed Section -->
                                @if(isset($version['sections']['changed']) && !empty($version['sections']['changed']))
                                    <div class="mb-4">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            <span class="text-xs font-semibold text-amber-700">Improvements</span>
                                        </div>
                                        <div class="space-y-2 ml-6">
                                            @foreach($version['sections']['changed'] as $item)
                                                <div class="flex items-start space-x-2 p-2 bg-amber-50 rounded">
                                                    <span class="text-amber-400">—</span>
                                                    <p class="text-xs text-gray-600">
                                                        @if($item['has_bold'] ?? false)
                                                            <strong class="font-medium text-gray-800">{{ $item['title'] }}</strong> — {{ $item['description'] }}
                                                        @else
                                                            {!! $item['description'] !!}
                                                        @endif
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Fixed Section -->
                                @if(isset($version['sections']['fixed']) && !empty($version['sections']['fixed']))
                                    <div class="mb-4">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-xs font-semibold text-green-700">Bug Fixes</span>
                                        </div>
                                        <div class="space-y-2 ml-6">
                                            @foreach($version['sections']['fixed'] as $item)
                                                <div class="flex items-start space-x-3 p-2 bg-green-50 rounded-lg">
                                                    <svg class="w-3 h-3 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <div>
                                                        @if($item['has_bold'] ?? false)
                                                            <h4 class="font-semibold text-xs text-gray-900">{{ $item['title'] }}</h4>
                                                            <p class="text-xs text-gray-600">{{ $item['description'] }}</p>
                                                        @else
                                                            <p class="text-xs text-gray-600">{!! $item['description'] !!}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Security Section -->
                                @if(isset($version['sections']['security']) && !empty($version['sections']['security']))
                                    <div class="mb-4">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            <span class="text-xs font-semibold text-red-700">Security</span>
                                        </div>
                                        <div class="space-y-2 ml-6">
                                            @foreach($version['sections']['security'] as $item)
                                                <div class="flex items-start space-x-2 p-2 bg-red-50 rounded">
                                                    <span class="text-red-400">—</span>
                                                    <p class="text-xs text-gray-600">
                                                        @if($item['has_bold'] ?? false)
                                                            <strong class="font-medium text-gray-800">{{ $item['title'] }}</strong> — {{ $item['description'] }}
                                                        @else
                                                            {!! $item['description'] !!}
                                                        @endif
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
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