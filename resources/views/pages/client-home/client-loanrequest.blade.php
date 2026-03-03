<!-- 2024 SilverDust) S. Maceren -->
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Loan Request</h3>
                @if(!$loanRequest)
                    <button onclick="showLoanRequestModal()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-semibold rounded-md shadow-sm hover:shadow transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Request a Loan
                    </button>
                @else
                    <div
                        class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-500 text-sm font-semibold rounded-md border border-gray-200 cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Request Sent
                    </div>
                @endif
            </div>

            <!-- Eligibility Alert -->
            @if($isEligible)
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-green-700 font-medium">You are eligible for loan request! ({{ $tier }}% tier)</p>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0v-6a1 1 0 112 0v6zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-yellow-700 font-medium">{{ $eligibilityMessage ?: 'You are not yet eligible for loan request.' }}</p>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg mt-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
        </div>
        <!-- Loan Details Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Loan Amount Card -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium mb-2">Loan Request Amount</p>
                        <p class="text-3xl font-bold text-blue-900">₱ {{ number_format($netLoanAmount, 2) }}</p>
                    </div>
                    <div class="bg-blue-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Monthly Payment Card -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium mb-2">Amount to Pay Every Month</p>
                        <p class="text-3xl font-bold text-purple-900">₱ {{ number_format($monthlyLoanAmount, 2) }}</p>
                    </div>
                    <div class="bg-purple-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium mb-2">Status</p>
                        @if($loanStatus == 'Pending')
                            <span
                                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-200 text-gray-700">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $loanStatus }}
                            </span>
                        @elseif($loanStatus == 'Verified')
                            <span
                                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-200 text-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $loanStatus }}
                            </span>
                        @elseif($loanStatus == 'Approved')
                            <span
                                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-200 text-green-700">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $loanStatus }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-200 text-gray-700">{{ $loanStatus }}</span>
                        @endif
                    </div>
                    <div class="bg-green-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Balance Card -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-6 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-orange-600 font-medium mb-2">Balance</p>
                        <p class="text-3xl font-bold text-orange-900">₱ {{ number_format($loanBalance, 2) }}</p>
                    </div>
                    <div class="bg-orange-200 rounded-full p-4">
                        <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LOAN APPLICATION MODAL -->
    <div id="loanApplicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-0 border w-full max-w-2xl shadow-xl rounded-lg bg-white">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <h3 class="text-xl font-bold text-gray-900">Loan Application</h3>
                <button onclick="closeLoanModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">

                <form id="loanApplicationForm" method="POST" action="/submit-client-loanrequest/{{ session('user_id') }}">
                    @csrf
                    <input type="hidden" name="waiver_signed" id="waiverSigned" value="0">
                    <input type="hidden" name="signature_data" id="signatureData" value="">

                    <!-- Loan Details Section -->
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-blue-900 mb-3 text-lg">Loan Details</h4>
                        <div class="flex flex-row justify-between items-center gap-4">
                            <div class="text-center">
                                <p class="text-sm text-blue-600 mb-1">Loanable Amount</p>
                                <p class="text-lg font-bold text-blue-900">₱ {{ number_format($loanableAmount ?? 0, 2) }}</p>
                            </div>
                            <div class="text-center border-l border-blue-200">
                                <p class="text-sm text-blue-600 mb-1">Processing Fee (10%)</p>
                                <p class="text-lg font-bold text-blue-900">₱ {{ number_format($processingFee ?? 0, 2) }}</p>
                            </div>
                            <div class="text-center border-l border-blue-200">
                                <p class="text-sm text-blue-600 mb-1">Net Amount</p>
                                <p class="text-lg font-bold text-green-700">₱ {{ number_format($netLoanAmount ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Term Selection -->
                    <div class="mb-6">
                        <label for="termMonths" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Loan Term <span class="text-red-500">*</span>
                        </label>
                        <select name="term_months" id="termMonths" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="calculateMonthlyPayment()">
                            <option value="">-- Select Term --</option>
                            <option value="2">2 months</option>
                            <option value="3">3 months</option>
                            <option value="6">6 months</option>
                            <option value="9">9 months</option>
                            <option value="12" selected>12 months</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Interest rate: 1.25% per month</p>
                    </div>

                    <!-- Monthly Payment Preview -->
                    <div class="bg-purple-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-purple-900 mb-3 text-lg">Monthly Payment Breakdown</h4>
                        <div class="flex flex-row justify-between items-center gap-4">
                            <div class="text-center">
                                <p class="text-sm text-purple-600 mb-1">Loan Payment</p>
                                <p class="text-lg font-bold text-purple-900" id="monthlyLoanPayment">₱ 0.00</p>
                            </div>
                            <div class="text-center border-l border-purple-200">
                                <p class="text-sm text-purple-600 mb-1">Contract Premium</p>
                                <p class="text-lg font-bold text-purple-900">₱ {{ number_format($monthlyContractPremium ?? 0, 2) }}</p>
                            </div>
                            <div class="text-center border-l border-purple-200">
                                <p class="text-sm text-purple-600 mb-1">Total Monthly Due</p>
                                <p class="text-xl font-bold text-purple-900" id="totalMonthlyDue">₱ 0.00</p>
                            </div>
                        </div>
                    </div>

                    <!-- Waiver of Rights -->
                    <div class="border-2 border-gray-300 rounded-lg p-6 pb-7 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-4 text-center text-lg uppercase tracking-wide">Waiver of Rights</h4>
                        
                        <div class="bg-gray-50 p-4 rounded mb-4 text-sm leading-relaxed">
                            <p class="mb-3">
                                I <span id="waiverApplicantNameBlank" class="inline-block border-b border-gray-400 min-w-[140px] text-center font-semibold">&nbsp;</span> member of sure life care &amp; services with Contract Number <span id="waiverContractNumberBlank" class="inline-block border-b border-gray-400 min-w-[110px] text-center font-semibold">&nbsp;</span> applied for a loan in my Contract.
                            </p>
                            <p class="mb-12">
                                I understand that after applying for a loan , I waive my right of any benefits and privileges stated in the Contract as a member . In Case of loss of life, I also agreed that I have to pay the remaining balance of my loan to be rendered service.
                            </p>

                            <!-- Applicant's Full name & signature - Fixed alignment -->
                            <div class="mt-8 mb-8">
                                <div class="flex justify-between items-end gap-12">
                                    <div class="text-center" style="min-width: 180px;">
                                        <div class="relative" style="height: 20px;">
                                            <p class="font-bold text-gray-900 absolute bottom-0 w-full text-center leading-none mb-0">{{ strtoupper(date('F d, Y')) }}</p>
                                        </div>
                                        <div class="border-b border-gray-400 pb-1"></div>
                                        <p class="text-xs text-gray-500 mt-1 text-center">DATE</p>
                                    </div>
                                    <div class="text-center" style="max-width: 250px;">
                                        <div class="relative" style="height: 20px;">
                                            <p id="waiverPrintedName" class="font-bold text-gray-900 text-xs absolute bottom-0 w-full text-center leading-none mb-0 whitespace-nowrap overflow-visible">{{ ($client->firstname ?? '') . ' ' . ($client->lastname ?? '') }}</p>
                                            <img id="waiverSignatureOverPrinted" class="hidden absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-[-32px]" style="max-height: 110px; z-index: 10;" alt="">
                                        </div>
                                        <div class="border-b border-gray-400 pb-1"></div>
                                        <p class="text-xs text-gray-500 mt-1 text-left bg-white px-1 inline-block">Applicant's Full name & signature:</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signature Canvas -->
                        <div class="mt-6 mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Digital Signature <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">Please sign below using your mouse or touch screen:</p>
                            <div id="signatureContainer" class="border-2 border-gray-200 rounded bg-gray-50 p-3" style="touch-action: none;">
                                <div id="signaturePadSurface" class="border border-gray-200 rounded-md bg-white overflow-hidden" style="height: 120px;">
                                    <canvas id="signatureCanvas" class="w-full h-full cursor-crosshair block"></canvas>
                                </div>
                                <div class="flex justify-start mt-2">
                                    <button type="button" onclick="clearSignature()"
                                        class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-xs rounded transition-colors">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Applicant Full Name and Contract Number -->
                        <div class="mt-6 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="applicantFullNameInput" class="block text-xs text-gray-500 mb-1">Applicant Full Name</label>
                                    <input type="text" id="applicantFullNameInput" value="{{ ($client->firstname ?? '') . ' ' . ($client->lastname ?? '') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="contractNumberInput" class="block text-xs text-gray-500 mb-1">Contract Number</label>
                                    <input type="text" id="contractNumberInput" value="{{ $client->contractnumber ?? '' }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Agreement Checkbox -->
                        <div class="flex items-start mt-6 px-1">
                            <input type="checkbox" id="agreeWaiver" required 
                                class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                            <label for="agreeWaiver" class="text-sm text-gray-700 cursor-pointer select-none">
                                I have read and agree to the Waiver of Rights stated above <span class="text-red-500">*</span>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeLoanModal()" 
                            class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-md transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="submitLoanBtn" disabled
                            class="px-6 py-2.5 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-md transition-colors shadow-sm">
                            Submit Loan Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Loan calculation variables
        const loanableAmount = {{ $loanableAmount ?? 0 }};
        const monthlyContractPremium = {{ $monthlyContractPremium ?? 0 }};
        const interestRate = 0.0125; // 1.25%
        let hasSignature = false;

        function showLoanRequestModal() {
            document.getElementById('loanApplicationModal').classList.remove('hidden');
            calculateMonthlyPayment();
            initSignatureCanvas();
        }

        function closeLoanModal() {
            document.getElementById('loanApplicationModal').classList.add('hidden');
        }

        function calculateMonthlyPayment() {
            const termMonths = parseInt(document.getElementById('termMonths').value) || 12;
            
            // Calculate interest: principal × 1.25% × termMonths
            const totalInterest = loanableAmount * interestRate * termMonths;
            const totalRepayable = loanableAmount + totalInterest;
            const monthlyLoanPayment = totalRepayable / termMonths;
            const totalMonthlyDue = monthlyLoanPayment + monthlyContractPremium;
            
            document.getElementById('monthlyLoanPayment').textContent = '₱ ' + monthlyLoanPayment.toFixed(2);
            document.getElementById('totalMonthlyDue').textContent = '₱ ' + totalMonthlyDue.toFixed(2);
        }

        // Signature Canvas
        let signatureCanvas, ctx, isDrawing = false;

        function initSignatureCanvas() {
            signatureCanvas = document.getElementById('signatureCanvas');
            ctx = signatureCanvas.getContext('2d');

            const surface = document.getElementById('signaturePadSurface');
            const rect = surface.getBoundingClientRect();
            signatureCanvas.width = Math.max(300, Math.floor(rect.width));
            signatureCanvas.height = Math.max(80, Math.floor(rect.height));
            
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            
            // Event listeners
            signatureCanvas.addEventListener('mousedown', startDrawing);
            signatureCanvas.addEventListener('mousemove', draw);
            signatureCanvas.addEventListener('mouseup', stopDrawing);
            signatureCanvas.addEventListener('mouseout', stopDrawing);
            signatureCanvas.addEventListener('touchstart', handleTouch);
            signatureCanvas.addEventListener('touchmove', handleTouch);
            signatureCanvas.addEventListener('touchend', stopDrawing);
        }

        function getPos(e) {
            const rect = signatureCanvas.getBoundingClientRect();
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            hasSignature = true;
            updateSubmitButton();
        }

        function stopDrawing() {
            isDrawing = false;
            if (hasSignature) {
                const preview = document.getElementById('waiverSignatureOverPrinted');
                if (preview) {
                    preview.src = signatureCanvas.toDataURL('image/png');
                    preview.classList.remove('hidden');
                }
            }
        }

        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 'mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            signatureCanvas.dispatchEvent(mouseEvent);
        }

        function clearSignature() {
            ctx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
            hasSignature = false;
            const preview = document.getElementById('waiverSignatureOverPrinted');
            if (preview) {
                preview.src = '';
                preview.classList.add('hidden');
            }
            updateSubmitButton();
        }

        function updateWaiverFields() {
            const fullNameInput = document.getElementById('applicantFullNameInput');
            const contractInput = document.getElementById('contractNumberInput');
            const fullName = (fullNameInput && fullNameInput.value ? fullNameInput.value.trim() : '') || '';
            const contractNumber = (contractInput && contractInput.value ? contractInput.value.trim() : '') || '';

            const nameBlank = document.getElementById('waiverApplicantNameBlank');
            if (nameBlank) {
                nameBlank.textContent = fullName ? (fullName + ' ') : ' ';
            }

            const contractBlank = document.getElementById('waiverContractNumberBlank');
            if (contractBlank) {
                contractBlank.textContent = contractNumber ? (contractNumber + ' ') : ' ';
            }

            const printedName = document.getElementById('waiverPrintedName');
            if (printedName) {
                printedName.textContent = fullName;
            }
        }

        function updateSubmitButton() {
            const agreed = document.getElementById('agreeWaiver').checked;
            const termSelected = document.getElementById('termMonths').value !== '';
            const submitBtn = document.getElementById('submitLoanBtn');
            
            submitBtn.disabled = !(hasSignature && agreed && termSelected);
        }

        // Form submission
        document.getElementById('loanApplicationForm').addEventListener('submit', function(e) {
            if (!hasSignature) {
                e.preventDefault();
                alert('Please sign the waiver form.');
                return false;
            }
            
            // Save signature data
            document.getElementById('signatureData').value = signatureCanvas.toDataURL('image/png');
            document.getElementById('waiverSigned').value = '1';
            
            return true;
        });

        // Agreement checkbox listener
        document.getElementById('agreeWaiver').addEventListener('change', updateSubmitButton);
        document.getElementById('termMonths').addEventListener('change', updateSubmitButton);

        document.getElementById('applicantFullNameInput').addEventListener('input', updateWaiverFields);
        document.getElementById('contractNumberInput').addEventListener('input', updateWaiverFields);

        // Close modal on outside click
        document.getElementById('loanApplicationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoanModal();
            }
        });

        // Show success/error modal on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateWaiverFields();
            @if(session('success'))
                showSwiftModal('Success!', '{{ session('success') }}', 'success', [
                    {text: 'OK', class: 'bg-green-500 hover:bg-green-600 text-white'}
                ]);
            @endif

            @if(session('error'))
                showSwiftModal('Error', '{{ session('error') }}', 'error', [
                    {text: 'OK', class: 'bg-red-500 hover:bg-red-600 text-white'}
                ]);
            @endif
        });
    </script>
@endsection