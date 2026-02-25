<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="m-3">
        <div class="bg-white p-3">
            <h3 class="text-dark">Commission</h3>
            <div class="alert alert-secondary mb-3" role="alert">
                View commissions based on payments made by clients.
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
                <button id="request-cancel-btn" data-bs-toggle="modal" data-bs-target="#comsCancelRequestModal" class="btn btn-outline-danger btn-sm me-2">Cancel</button>
                <button id="request-btn" data-bs-toggle="modal" data-bs-target="#comsRequestModal" class="btn btn-outline-success btn-sm me-2" disabled>Request</button>
            </div>
              
        </div>
        <div class="bg-white mt-3 p-3">
            <table id="common_dataTable" class="table table-hover table-striped mt-5 font-sm">
                <thead>
                    <tr>
                        <th scope="col">Select</th>
                        <th scope="col">Id</th>
                        <th scope="col">Last</th>
                        <th scope="col">First</th>
                        <th scope="col">Middle</th>
                        <th scope="col">Contract No</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Commission</th>
                        <th scope="col">Payment Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Voucher Code</th>
                    </tr>
                </thead>
            </table>

            <!-- MODAL ENCASHMENT REQUEST -->
            <div class="modal fade" id="comsRequestModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-dark fw-bold" id="staticBackdropLabel">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>You cannot make another request if there are any request pending. Continue?</p>
                        </div>
                        <form id="comsRequestForm" method="POST">
                            @csrf
                            @method('POST')
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm w-25" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-success btn-sm w-25" id="confirmComsRequest">Confirm</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MODAL CANCEL ENCASHMENT REQUEST -->
            <div class="modal fade" id="comsCancelRequestModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-dark fw-bold" id="staticBackdropLabel">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Cancel all requests?</p>
                        </div>
                        <form id="comsCancelRequestForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm w-25" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-success btn-sm w-25" id="confirmCancelComsRequest">Confirm</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/commission.js') }}"></script>
@endsection