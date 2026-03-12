<!-- Loan Application Form -->
@extends('layouts.main')

@section('content')
<div class="m-3">
    <div class="bg-white p-3">
        <h3 class="text-dark">Loan Application</h3>
        <div class="alert alert-info mb-3" role="alert">
            Apply for a loan based on your premium payments. Minimum 60% premiums paid required.
        </div>
        @if(session('error'))
            @push('scripts')
                <script>showSwiftModal('Error', '{{ session('error') }}', 'error');</script>
            @endpush
        @elseif(session('success'))
            @push('scripts')
                <script>showSwiftModal('Success!', '{{ session('success') }}', 'success');</script>
            @endpush
        @endif
    </div>

    <div class="bg-white mt-3 p-3">
        <form id="loanApplicationForm" method="POST" action="/submit-client-loanrequest/{{ $client->Id ?? 0 }}">
            @csrf
            <input type="hidden" name="contract_id" id="contractId" value="{{ $contract->Id ?? '' }}">

            <!-- Client Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Member Name</label>
                    <input type="text" class="form-control" value="{{ $client->lastname ?? '' }}, {{ $client->firstname ?? '' }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Contract Number</label>
                    <input type="text" class="form-control" id="contractNumber" value="{{ $client->contractnumber ?? '' }}" readonly>
                </div>
            </div>

            <!-- Premium Status -->
            <div class="card mb-4 bg-light">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Premium Payment Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Contract Price</label>
                            <input type="text" class="form-control" id="contractPrice" value="{{ number_format($contract->packageprice ?? 0, 2) }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Premiums Paid</label>
                            <input type="text" class="form-control" id="totalPremiumsPaid" value="{{ number_format($totalPremiumsPaid ?? 0, 2) }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Premium Paid %</label>
                            <div class="progress mt-2" style="height: 25px;">
                                <div class="progress-bar" id="premiumProgress" role="progressbar" style="width: 0%">0%</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div id="eligibilityStatus" class="alert alert-secondary">
                                Checking eligibility...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Calculation -->
            <div class="card mb-4" id="loanCalculationCard" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Loan Calculation</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Tier Achieved</label>
                            <input type="text" class="form-control" id="tierAchieved" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gross Loan Amount</label>
                            <input type="text" class="form-control text-success fw-bold" id="grossLoanAmount" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Processing Fee (10%)</label>
                            <input type="text" class="form-control text-danger" id="processingFee" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Net Loan Amount</label>
                            <input type="text" class="form-control text-primary fw-bold" id="netLoanAmount" readonly>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Loan Term</label>
                            <select class="form-select" id="loanTerm" name="term_months">
                                <option value="2">2 months</option>
                                <option value="3">3 months</option>
                                <option value="6">6 months</option>
                                <option value="9">9 months</option>
                                <option value="12" selected>12 months</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Interest (1.25%/mo)</label>
                            <input type="text" class="form-control" id="totalInterest" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Repayable</label>
                            <input type="text" class="form-control fw-bold" id="totalRepayable" readonly>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Monthly Loan Payment</label>
                            <input type="text" class="form-control" id="monthlyLoanPayment" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Monthly Due (Loan + Contract)</label>
                            <input type="text" class="form-control text-success fw-bold" id="totalMonthlyDue" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Waiver Section -->
            <div class="card mb-4" id="waiverCard" style="display: none;">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Waiver of Rights</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        You must sign the waiver to proceed with your loan application.
                    </div>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#waiverModal" id="openWaiverBtn">
                        <i class="fas fa-file-signature"></i> Sign Waiver of Rights
                    </button>
                    <span id="waiverStatus" class="badge bg-secondary ms-2">Not Signed</span>
                    <input type="hidden" name="waiver_signed" id="waiverSignedInput" value="0">
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-between mt-4">
                <a href="/client-view/{{ $client->Id ?? 0 }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
                <button type="submit" class="btn btn-primary" id="submitLoanBtn" disabled>
                    <i class="fas fa-paper-plane"></i> Submit Loan Application
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Include Waiver Component -->
@include('components.loan-waiver', ['client' => $client ?? null, 'contract' => $contract ?? null, 'loanRequestId' => $loanRequestId ?? ''])
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const clientId = {{ $client->Id ?? 'null' }};
    let loanCalculated = false;
    let waiverSigned = false;

    // Load client contract and payment data
    function loadClientData() {
        // Fetch client payment data via AJAX
        $.get('/get-payment-history', { client_id: clientId }, function(response) {
            calculateEligibility(response);
        });
    }

    function calculateEligibility(data) {
        const contractPrice = parseFloat(data.contract_price || 0);
        const totalPremiumsPaid = parseFloat(data.total_payments || 0);
        const percentPaid = contractPrice > 0 ? (totalPremiumsPaid / contractPrice) * 100 : 0;

        $('#contractPrice').val('₱ ' + contractPrice.toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#totalPremiumsPaid').val('₱ ' + totalPremiumsPaid.toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#premiumProgress').css('width', Math.min(percentPaid, 100) + '%').text(percentPaid.toFixed(1) + '%');

        // Check eligibility tiers
        let eligibleTier = null;
        if (percentPaid >= 100) eligibleTier = 100;
        else if (percentPaid >= 80) eligibleTier = 80;
        else if (percentPaid >= 60) eligibleTier = 60;

        if (eligibleTier) {
            $('#eligibilityStatus').removeClass('alert-secondary alert-danger').addClass('alert-success')
                .html('<i class="fas fa-check-circle"></i> You are eligible for a ' + eligibleTier + '% tier loan!');
            $('#loanCalculationCard').show();
            $('#waiverCard').show();
            calculateLoan(contractPrice, eligibleTier);
        } else {
            $('#eligibilityStatus').removeClass('alert-secondary alert-success').addClass('alert-danger')
                .html('<i class="fas fa-times-circle"></i> Not eligible. You need at least 60% premiums paid.');
            $('#loanCalculationCard').hide();
            $('#waiverCard').hide();
        }
    }

    function calculateLoan(contractPrice, tier) {
        // Tier percentages: 60% premium = 30% loan, 80% = 40%, 100% = 45%
        const loanPercentages = { 60: 30, 80: 40, 100: 45 };
        const grossLoan = contractPrice * (loanPercentages[tier] / 100);
        const processingFee = grossLoan * 0.10;
        const netLoan = grossLoan - processingFee;

        $('#tierAchieved').val(tier + '% Premium Paid');
        $('#grossLoanAmount').val('₱ ' + grossLoan.toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#processingFee').val('-₱ ' + processingFee.toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#netLoanAmount').val('₱ ' + netLoan.toLocaleString('en-PH', {minimumFractionDigits: 2}));

        updateLoanCalculation();
    }

    function updateLoanCalculation() {
        const grossLoan = parseFloat($('#grossLoanAmount').val().replace(/[₱,]/g, '')) || 0;
        const termMonths = parseInt($('#loanTerm').val()) || 12;
        const interestRate = 1.25;

        // Calculate interest: principal × 1.25% × term
        const totalInterest = grossLoan * (interestRate / 100) * termMonths;
        const totalRepayable = grossLoan + totalInterest;
        const monthlyLoanPayment = totalRepayable / termMonths;

        // Get contract premium amount
        const monthlyContractPremium = parseFloat($('#clientMonthlyPremium').val() || 0);
        const totalMonthlyDue = monthlyLoanPayment + monthlyContractPremium;

        $('#totalInterest').val('₱ ' + totalInterest.toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#totalRepayable').val('₱ ' + totalRepayable.toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#monthlyLoanPayment').val('₱ ' + monthlyLoanPayment.toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#totalMonthlyDue').val('₱ ' + totalMonthlyDue.toLocaleString('en-PH', {minimumFractionDigits: 2}));

        loanCalculated = true;
        checkSubmitReady();
    }

    function checkSubmitReady() {
        $('#submitLoanBtn').prop('disabled', !(loanCalculated && waiverSigned));
    }

    // Term selection change
    $('#loanTerm').on('change', updateLoanCalculation);

    // Waiver signed callback (called from waiver component)
    window.onWaiverSigned = function() {
        waiverSigned = true;
        $('#waiverStatus').removeClass('bg-secondary').addClass('bg-success').text('Signed');
        $('#waiverSignedInput').val('1');
        checkSubmitReady();
    };

    // Form submission
    $('#loanApplicationForm').on('submit', function(e) {
        if (!loanCalculated || !waiverSigned) {
            e.preventDefault();
            alert('Please complete all steps including signing the waiver.');
            return false;
        }
    });

    // Initialize
    loadClientData();
});
</script>
@endsection
