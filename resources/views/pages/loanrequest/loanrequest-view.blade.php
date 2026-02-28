<!-- 2024 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="m-3">
        <div class="bg-white p-3">
            <h3 class="text-dark">View Loan Request</h3>
            <div class="alert alert-secondary mb-3" role="alert">
                Manage loan request for this selected client.
            </div>
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @elseif(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @elseif(session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif
            <div class="d-flex">
                <a href="/req-loans" class="btn btn-outline-secondary btn-sm" role="button">Return</a>
                @if($loanRequestDetails->Status == 'Pending')
                    <a class="btn btn-success btn-sm ms-1" onclick="showLoanRequestModal('{{ $loanRequestDetails->Id }}', 'verify')" role="button">Verify</a>       
                @elseif($loanRequestDetails->Status == 'Verified')
                    <a class="btn btn-success btn-sm ms-1" onclick="showLoanRequestModal('{{ $loanRequestDetails->Id }}', 'approve')" role="button">Approve</a>       
                @endif
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-1">
                                Contract
                            </div>
                            <div class="col-sm-2">
                                {{ $clientDetails->ContractNumber }}
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-1">
                                Name
                            </div>
                            <div class="col-sm-2">
                                {{ $clientDetails->LastName . ', ' . $clientDetails->FirstName . ' ' . $clientDetails->MiddleName }}
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-1">
                                Branch
                            </div>
                            <div class="col-sm-2">
                                {{ $clientBranch->BranchName }}
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-1">
                                Mode
                            </div>
                            <div class="col-sm-2">
                                {{ $clientTerm->Term }}
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-1">
                                Amount
                            </div>
                            <div class="col-sm-2">
                                ₱ {{ number_format($clientTerm->Price, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="font-sm"><strong>Computation of loanable amount</strong></h5>
                            </div>
                        </div>
                        <div class="mx-3 my-5">
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">Annual</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">₱ {{ number_format($annualPaymentAmount, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">No. of years paid</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">{{ $noOfYearsPaid }}</h5>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="row mt-3">
                                <div class="col">
                                    
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">₱ {{ number_format($totalAnnualPayment, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">Loanable percentage (%)</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">{{ $totalNumYearsPaid }}</h5>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">Gross loanable amount</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">₱ {{ number_format($grossLoanableAmount, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="text-danger font-sm">Less: Handling fee (10%)</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">₱ {{ number_format($handlingFee, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm"><strong>Net loanable Amount</strong></h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm"><strong> ₱ {{ number_format($netLoanableAmount, 2) }} </strong></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="col">
                            <h5 class="font-sm"><strong>Computation of monthly dues with interest</strong></h5>
                        </div>
                        <div class="mx-3 my-5">
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">Gross loanable amount</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">₱ {{ number_format($grossLoanableAmount , 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">No. of months of payment</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">{{ $noOfMonthPayments }}</h5>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">Loan monthly due</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">₱ {{ number_format($loanMonthlyDue, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">Percentage of interest (%)</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">{{ $percentageInterest * 100 }}</h5>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">Interest</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">₱ {{ number_format($interest, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">Term</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">{{ $term }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm">Monthly interest</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">₱ {{ number_format($monthlyInterest, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm text-danger">Add: Loan Monthly Due</h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm">₱ {{ number_format($loanMonthlyDue, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="row mt-3">
                                <div class="col">
                                    <h5 class="font-sm"><strong>Total monthly due for 12 months</strong></h5>
                                </div>
                                <div class="col">
                                    <div class="w-25 text-end">
                                        <h5 class="font-sm"><strong>₱ {{ number_format($totalMonthlyDue, 2) }}</strong></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/req-loan.js') }}"></script>
    <script>
        function showLoanRequestModal(loanReqId, action) {
            const actionText = action === 'verify' ? 'verify' : 'approve';
            showSwiftModal('Confirmation', `You are going to ${actionText} this loan request.\n\nYou cannot undo this action. Continue?`, 'warning', [
                {text: 'Submit', class: 'bg-green-500 hover:bg-green-600 text-white', action: 'submitLoanRequest(' + loanReqId + ')'},
                {text: 'Close', class: 'bg-gray-200 hover:bg-gray-300 text-gray-800'}
            ]);
        }
        
        function submitLoanRequest(loanReqId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/submit-loan-request-approval/' + loanReqId;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            
            form.appendChild(csrfToken);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
@endsection