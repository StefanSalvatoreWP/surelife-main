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
                    <a class="btn btn-outline-success btn-sm ms-2" onclick="showReleaseModal()" role="button">Release</a>
                @endif

                @if($encashmentData->Status != 'Released' && $encashmentData->Status != 'Rejected')
                    <a class="btn btn-danger btn-sm ms-auto" onclick="showRejectModal()" role="button">Reject</a>
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

        <!-- Swift Modal Scripts -->
        <script>
            function showReleaseModal() {
                const modal = document.getElementById('swiftModal');
                const iconDiv = document.getElementById('swiftModalIcon');
                const titleEl = document.getElementById('swiftModalTitle');
                const messageEl = document.getElementById('swiftModalMessage');
                const actionsEl = document.getElementById('swiftModalActions');

                if (!modal) return;

                iconDiv.innerHTML = `<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>`;
                iconDiv.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-green-100';

                titleEl.textContent = 'Release Encashment';
                
                const encashmentId = document.getElementById('encashmentReqId')?.value || '';
                messageEl.innerHTML = `
                    <div class="text-gray-600 text-sm mb-4">Enter voucher code to release this encashment.</div>
                    <form id="swiftReleaseForm">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="text" id="vouchercode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter voucher code" required>
                    </form>
                `;

                actionsEl.innerHTML = '';
                
                const cancelBtn = document.createElement('button');
                cancelBtn.type = 'button';
                cancelBtn.className = 'w-full py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition duration-200';
                cancelBtn.textContent = 'Cancel';
                cancelBtn.addEventListener('click', () => hideSwiftModal());
                actionsEl.appendChild(cancelBtn);

                const confirmBtn = document.createElement('button');
                confirmBtn.type = 'button';
                confirmBtn.className = 'w-full py-3 px-6 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition duration-200';
                confirmBtn.textContent = 'Confirm Release';
                confirmBtn.addEventListener('click', () => {
                    const voucherCode = document.getElementById('vouchercode')?.value;
                    if (!voucherCode) {
                        alert('Please enter a voucher code');
                        return;
                    }
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/encashment-release/' + encashmentId;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="vouchercode" value="${voucherCode}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                });
                actionsEl.appendChild(confirmBtn);

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function showRejectModal() {
                const modal = document.getElementById('swiftModal');
                const iconDiv = document.getElementById('swiftModalIcon');
                const titleEl = document.getElementById('swiftModalTitle');
                const messageEl = document.getElementById('swiftModalMessage');
                const actionsEl = document.getElementById('swiftModalActions');

                if (!modal) return;

                iconDiv.innerHTML = `<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>`;
                iconDiv.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-red-100';

                titleEl.textContent = 'Reject Encashment';
                
                const encashmentId = document.getElementById('encashmentReqId')?.value || '';
                messageEl.innerHTML = `
                    <div class="text-gray-600 text-sm mb-4">Are you sure you want to reject this encashment request?</div>
                    <form id="swiftRejectForm">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="text" id="rejectremarks" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Enter remarks (optional)">
                    </form>
                `;

                actionsEl.innerHTML = '';
                
                const cancelBtn = document.createElement('button');
                cancelBtn.type = 'button';
                cancelBtn.className = 'w-full py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition duration-200';
                cancelBtn.textContent = 'Cancel';
                cancelBtn.addEventListener('click', () => hideSwiftModal());
                actionsEl.appendChild(cancelBtn);

                const confirmBtn = document.createElement('button');
                confirmBtn.type = 'button';
                confirmBtn.className = 'w-full py-3 px-6 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition duration-200';
                confirmBtn.textContent = 'Confirm Reject';
                confirmBtn.addEventListener('click', () => {
                    const remarks = document.getElementById('rejectremarks')?.value || '';
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/encashment-reject/' + encashmentId;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="rejectremarks" value="${remarks}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                });
                actionsEl.appendChild(confirmBtn);

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        </script>
    </div>
    <script src="{{ asset('js/req-encashment-view.js') }}"></script>
@endsection