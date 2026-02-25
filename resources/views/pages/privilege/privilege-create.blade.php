<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="m-3">
        <div class="bg-white p-3">
            <h3>Privilege</h3>
            <div class="alert alert-secondary mb-3" role="alert">
                Manage privilege levels.
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
            <a href="/privilege-create" class="btn btn-outline-primary btn-sm" role="button">Create new branch</a>
        </div>
        <div class="bg-white mt-3 p-3">
            <table id="common_dataTable" class="table table-hover table-striped mt-5 font-sm">
                <thead>
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Branch</th>
                        <th scope="col">Region</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
            </table>

            <!-- MODAL DELETE -->
            <div class="modal fade" id="branchDeleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="staticBackdropLabel">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Delete selected branch? <span class="text-danger" id="branchToDelete"></span></p>
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
    <script src="{{ asset('js/branch.js') }}"></script>
@endsection