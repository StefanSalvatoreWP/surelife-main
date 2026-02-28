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
                <button id="request-cancel-btn" onclick="showSwiftModal('Cancel Requests', 'Cancel all requests?', 'warning', [{text: 'Confirm', class: 'bg-red-500 hover:bg-red-600 text-white', action: 'submitCancelComsRequest()'}, {text: 'Close', class: 'bg-gray-200 hover:bg-gray-300 text-gray-800'}])" class="btn btn-outline-danger btn-sm me-2">Cancel</button>
                <button id="request-btn" onclick="showSwiftModal('Confirm Request', 'You cannot make another request if there are any request pending. Continue?', 'warning', [{text: 'Confirm', class: 'bg-green-500 hover:bg-green-600 text-white', action: 'submitComsRequest()'}, {text: 'Close', class: 'bg-gray-200 hover:bg-gray-300 text-gray-800'}])" class="btn btn-outline-success btn-sm me-2" disabled>Request</button>
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
        </div>
    </div>
    <script src="{{ asset('js/commission.js') }}"></script>
    <script>
        function submitComsRequest() {
            document.getElementById('comsRequestForm').submit();
        }
        
        function submitCancelComsRequest() {
            document.getElementById('comsCancelRequestForm').submit();
        }
    </script>
@endsection