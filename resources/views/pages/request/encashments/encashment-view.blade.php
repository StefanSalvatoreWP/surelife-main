<!-- 2024 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="m-3">
        <div class="bg-white p-3">
            <h3 class="text-dark">View Encashment [ {{ $staff[0]->LastName }}, {{ $staff[0]->FirstName}} {{ $staff[0]->MiddleName }}]</h3>
            <div class="alert alert-dark mb-3" role="alert">
                View selected encashment request.
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
                <a href="/req-encashments" class="btn btn-outline-secondary btn-sm" role="button">Return</a>
                @if($encashmentData->Status == 'Pending')
                    <a href="/view-req-encashment-adjustment/{{ $encashmentData->Id }}" class="btn btn-outline-success btn-sm ms-2" role="button">Verify</a>
                @elseif($encashmentData->Status == 'Verified')
                    <a href="/view-req-encashment-adjustment/{{ $encashmentData->Id }}" class="btn btn-outline-success btn-sm ms-2" role="button">Record</a>
                @elseif($encashmentData->Status == 'Recorded')
                    <a href="/view-req-encashment-adjustment/{{ $encashmentData->Id }}" class="btn btn-outline-success btn-sm ms-2" role="button">Approve</a>
                @elseif($encashmentData->Status == 'Approved')
                    <a class="btn btn-outline-success btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#encashmentReqReleaseModal" role="button">Release</a>
                @endif

                @if($encashmentData->Status != 'Released' && $encashmentData->Status != 'Rejected')
                    <a class="btn btn-danger btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#encashmentReqRejectModal" role="button">Reject</a>
                @endif
            </div>
            <input type="hidden" id="encashmentReqId" value="{{$encashmentData->Id}}" />
        </div>
        <div class="row mt-3">
            <div class="col-sm-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mt-2 text-dark fw-bold">Summary</h5>
                    </div>
                    <div class="row m-3">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col">
                                    <p>Amount</p>
                                </div>
                                <div class="col">
                                    <p>₱ {{ number_format($encashmentData->Amount, 2) }} </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <p>Date Requested</p>
                                </div>
                                <div class="col">
                                    <p>{{ $encashmentData->DateRequested}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <p>Status</p>
                                </div>
                                <div class="col">
                                    @if($encashmentData->Status == 'Pending')
                                        <p class='text-secondary'>{{ $encashmentData->Status}}</p>
                                    @elseif($encashmentData->Status == 'Verified')
                                        <p class='text-primary'>{{ $encashmentData->Status}}</p>
                                    @elseif($encashmentData->Status == 'Recorded')
                                        <p class='text-warning'>{{ $encashmentData->Status}}</p>
                                    @elseif($encashmentData->Status == 'Approved')
                                        <p class='text-success'>{{ $encashmentData->Status}}</p>
                                    @elseif($encashmentData->Status == 'Released')
                                        <p class='fw-bold text-success'>{{ $encashmentData->Status}}</p>
                                    @elseif($encashmentData->Status == 'Rejected')
                                        <p class='fw-bold text-danger'>{{ $encashmentData->Status}}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <p>Remarks</p>
                                </div>
                                <div class="col">
                                    <p>{{ $encashmentData->Remarks}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <p>Voucher Code</p>
                                </div>
                                <div class="col">
                                    <p>
                                        @if($encashmentData->Status == 'Released')
                                            {{ $encashmentData->VoucherCode}}
                                        @else
                                            Not available
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col">
                                    <p>Incentives</p>
                                </div>
                                <div class="col">
                                    <p>₱ {{ number_format($encashmentData->Incentives, 2) }} </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <p>Incentives Remarks</p>
                                </div>
                                <div class="col">
                                    <p>{{ $encashmentData->IncentivesRemarks}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <p>Adjustments</p>
                                </div>
                                <div class="col">
                                    <p>₱ {{ number_format($encashmentData->Adjustments, 2) }} </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <p>Adjustment Remarks</p>
                                </div>
                                <div class="col">
                                    <p>{{ $encashmentData->AdjustmentsRemarks}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm">
                                    <p>Total</p>
                                </div>
                                <div class="col-sm">
                                    @php
                                        $totalComs = ($encashmentData->Amount + $encashmentData->Incentives) - $encashmentData->Adjustments;
                                    @endphp
                                    <p class="fw-bold text-success">₱ {{ number_format($totalComs, 2) }} </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mt-2 text-dark fw-bold">Transactions</h5>
                    </div>
                    <div class="row m-3">
                        <div class="col">
                            <div class="row">
                                <div class="col-sm-4">
                                    <p>Verified</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-black-50">
                                        @if($encashmentData->VerifiedBy == $staff[0]->Id )
                                            {{ $encashmentData->DateVerified }} [ {{ $staff[0]->LastName }} , {{ $staff[0]->FirstName }} {{ $staff[0]->MiddleName }} ]
                                        @else
                                            Not available - Not available
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p>Recorded</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-black-50">
                                        @if($encashmentData->RecordedBy == $staff[0]->Id )
                                            {{ $encashmentData->DateVerified }} [ {{ $staff[0]->LastName }} , {{ $staff[0]->FirstName }} {{ $staff[0]->MiddleName }} ]
                                        @else
                                            Not available - Not available
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p>Approved</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-black-50">
                                        @if($encashmentData->ApprovedBy == $staff[0]->Id )
                                            {{ $encashmentData->DateApproved }} [ {{ $staff[0]->LastName }} , {{ $staff[0]->FirstName }} {{ $staff[0]->MiddleName }} ]
                                        @else
                                            Not available - Not available
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p>Released</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-black-50">
                                        @if($encashmentData->ReleasedBy == $staff[0]->Id )
                                            {{ $encashmentData->DateReleased }} [ {{ $staff[0]->LastName }} , {{ $staff[0]->FirstName }} {{ $staff[0]->MiddleName }} ]
                                        @else
                                            Not available - Not available
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p>Rejected</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-black-50">
                                        @if($encashmentData->RejectedBy == $staff[0]->Id )
                                            {{ $encashmentData->DateRejected }} [ {{ $staff[0]->LastName }} , {{ $staff[0]->FirstName }} {{ $staff[0]->MiddleName }} ]
                                        @else
                                            Not available - Not available
                                        @endif
                                    </p>
                                </div>
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
                        <h5 class="card-title mt-2 text-dark fw-bold">Clients</h5>
                    </div>
                    <div class="row m-3">
                        <table id="common_dataTable" class="table table-hover table-striped mt-5 font-sm">
                            <thead>
                                <tr>
                                    <th scope="col">Payment Date</th>
                                    <th scope="col">Last</th>
                                    <th scope="col">First</th>
                                    <th scope="col">Middle</th>
                                    <th scope="col">Contract No.</th>
                                    <th scope="col">Package</th>
                                    <th scope="col">Term</th>
                                    <th scope="col">Amount Paid</th>
                                    <th scope="col">Commission</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL ENCASHMENT RELEASE -->
        <div class="modal fade" id="encashmentReqReleaseModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-dark fw-bold" id="staticBackdropLabel">Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="encashmentReqReleaseForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <input type="text" class="form-control font-sm" placeholder="Enter voucher code" name="vouchercode" required />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm w-25" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-success btn-sm w-25" id="confirmEncashmentReqRelease">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL ENCASHMENT REJECT -->
        <div class="modal fade" id="encashmentReqRejectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-dark fw-bold" id="staticBackdropLabel">Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="encashmentReqRejectForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <p>Reject this selected encashment request?</p>
                            <input type="text" class="form-control" name="rejectremarks" placeholder="Enter remarks" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm w-25" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger btn-sm w-25" id="confirmEncashmentReqReject">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/req-encashment-view.js') }}"></script>
@endsection