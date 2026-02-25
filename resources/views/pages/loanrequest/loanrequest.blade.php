<!-- 2024 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="m-3">
        <div class="bg-white p-3">
            <h3 class="text-dark">Loan Requests</h3>
            <div class="alert alert-secondary mb-3" role="alert">
                Manage loan requests from different clients.
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
        </div>
        <div class="bg-white mt-3 p-3">
            <table id="common_dataTable" class="table table-hover table-striped mt-5 font-sm">
                <thead>
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Contract No.</th>
                        <th scope="col">Last</th>
                        <th scope="col">First</th>
                        <th scope="col">Middle</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Date Requested</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
            </table>

            <!-- MODAL REMARKS -->
            <div class="modal fade" id="loanRequestRemarksModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-dark fw-bold" id="staticBackdropLabel">Remarks</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <pre style="white-space: pre-wrap;"><span class="text-dark" id="loanRequestRemarks"></span></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL DELETE -->
            <div class="modal fade" id="loanRequestDeleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-dark fw-bold" id="staticBackdropLabel">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Delete selected loan request? <span class="text-danger" id="branchToDelete"></span></p>
                            <p>You cannot undo this action. Continue?</p>
                        </div>
                        <form id="deleteForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm w-25" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger btn-sm w-25" id="confirmDelete">Confirm</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/loanrequest.js') }}"></script>
@endsection