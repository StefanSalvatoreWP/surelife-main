<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl border-2 border-green-300 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">New Deposit</h1>
                    <p class="text-green-600 text-sm">Create new deposit for your selected region and branch</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-green-500 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
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
            <a href="/deposit" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-200 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Return
            </a>
        </div>

        <!-- Form Section -->
        <form action="/submit-deposit-insert" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-cyan-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                        DEPOSIT INFORMATION
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="regionName" class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                            <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="regionName" name="regionid">
                                @php
                                    $selectedRegion = old('regionid');
                                @endphp
                                @foreach($regions as $region)
                                    <option value="{{ $region->Id }}" {{ $selectedRegion == $region->Id ? 'selected' : '' }}>
                                        {{ $region->RegionName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            @php
                                $selectedBranch = old('branchid');
                            @endphp
                            <input type="hidden" id="selectedBranch" value={{ $selectedBranch }} />
                            <label for="branchName" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                            <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="branchName" name="branchid">
                                <option value="0">Select branch</option>
                            </select>
                            @error('branchid')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="bankName" class="block text-sm font-medium text-gray-700 mb-2">Bank</label>
                            <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="bankName" name="bankid">
                                @php
                                    $selectedBankName = old('bankid');
                                @endphp
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->Id }}" {{ $selectedBankName == $bank->Id ? 'selected' : '' }}>
                                        {{ $bank->BankName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            @php
                                $selectedBankAccountNo = old('bankaccountid');
                            @endphp
                            <input type="hidden" id="selectedBankAccountNo" value={{ $selectedBankAccountNo }} />
                            <label for="bankAccountNo" class="block text-sm font-medium text-gray-700 mb-2">Bank Account No.</label>
                            <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="bankAccountNo" name="bankaccountid">
                                <option value="0">Select bank</option>
                            </select>
                            @error('bankaccountid')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                        <div>
                            @php
                                $prevDepositAmount = old('depositamount');
                            @endphp
                            <label for="depositAmount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                            <input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="depositAmount" name="depositamount" value="{{ $prevDepositAmount }}"/>
                            @error('depositamount')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            @php
                                $prevDepositDate = old('depositdate');
                            @endphp
                            <label for="depositDate" class="block text-sm font-medium text-gray-700 mb-2">Deposit Date</label>
                            <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="depositDate" name="depositdate" value="{{ $prevDepositDate }}"/>
                            @error('depositdate')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            @php
                                $prevSequenceNo = old('sequenceno');
                            @endphp
                            <label for="sequenceNo" class="block text-sm font-medium text-gray-700 mb-2">Sequence No.</label>
                            <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="sequenceNo" name="sequenceno" maxlength="100" value="{{ $prevSequenceNo }}"/>
                            @error('sequenceno')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            @php
                                $prevDepositSlip = old('depositslip');
                            @endphp
                            <label for="depositSlip" class="block text-sm font-medium text-gray-700 mb-2">Deposit Slip</label>
                            <input type="file" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="depositSlip" name="depositslip" />
                            @error('depositslip')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                        <div>
                            @php
                                $prevDepositedBy = old('depositedbystaffid');
                            @endphp
                            <input type="hidden" id="selectedStaffId" value={{ $prevDepositedBy }} />
                            <label for="depositedBy" class="block text-sm font-medium text-gray-700 mb-2">Deposited By</label>
                            <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="depositedBy" name="depositedbystaffid">
                                <option value="0">Select Staff</option>
                            </select>
                            @error('depositedbystaffid')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-3">
                            @php
                                $prevDepositNote = old('depositnote');
                            @endphp
                            <label for="depositNote" class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                            <textarea class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200" id="depositNote" name="depositnote" rows="4" style="resize:none;">{{ $prevDepositNote }}</textarea>
                            @error('depositnote')
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
                        Submit Deposit
                    </span>
                </button>
            </div>
        </form>
    </div>
    <script src="{{ asset('js/deposit-create.js') }}"></script>
@endsection