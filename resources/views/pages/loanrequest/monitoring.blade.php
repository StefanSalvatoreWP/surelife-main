@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="page-title">Loan Monitoring Dashboard</h2>
            <p class="text-muted">Monitor loan payments by due date periods</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $due30Days }}</h4>
                            <small>30 Days Due</small>
                        </div>
                        <i class="fas fa-calendar-day fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $due60Days }}</h4>
                            <small>60 Days Due</small>
                        </div>
                        <i class="fas fa-calendar-week fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $due90Days }}</h4>
                            <small>90 Days Due</small>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $lapsed }}</h4>
                            <small>91+ Days Lapsed</small>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row align-items-end">
                <div class="col-md-3">
                    <label for="branchFilter" class="form-label">Branch</label>
                    <select id="branchFilter" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>
                                {{ $branch->branchname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="periodFilter" class="form-label">Period</label>
                    <select id="periodFilter" class="form-select">
                        <option value="30">30 Days Due</option>
                        <option value="60">60 Days Due</option>
                        <option value="90">90 Days Due</option>
                        <option value="lapsed">91+ Days Lapsed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" id="applyFilters" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Loan Details</h5>
        </div>
        <div class="card-body">
            <table id="loanMonitoringTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Contract Number</th>
                        <th>Loan Amount</th>
                        <th>Monthly Due</th>
                        <th>Remaining Balance</th>
                        <th>Days Until Due</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let table = $('#loanMonitoringTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("loan.monitoring.data") }}',
            data: function(d) {
                d.branch_id = $('#branchFilter').val();
                d.days = $('#periodFilter').val() === 'lapsed' ? 91 : $('#periodFilter').val();
                d.type = $('#periodFilter').val() === 'lapsed' ? 'lapsed' : 'due';
            }
        },
        columns: [
            { data: 'client_name', name: 'client_name' },
            { data: 'contract_number', name: 'contract_number' },
            { data: 'amount', name: 'amount', render: function(data) {
                return '₱ ' + parseFloat(data).toLocaleString('en-PH', {minimumFractionDigits: 2});
            }},
            { data: 'monthlyamount', name: 'monthlyamount', render: function(data) {
                return '₱ ' + parseFloat(data).toLocaleString('en-PH', {minimumFractionDigits: 2});
            }},
            { data: 'remaining_balance', name: 'remaining_balance', render: function(data) {
                return '₱ ' + parseFloat(data).toLocaleString('en-PH', {minimumFractionDigits: 2});
            }},
            { data: 'days_until_due', name: 'days_until_due' },
            { data: 'status', name: 'status' },
            { data: null, name: 'actions', orderable: false, searchable: false, render: function(data) {
                return '<a href="/req-loans/view/' + data.Id + '" class="btn btn-sm btn-primary">View</a>';
            }}
        ]
    });

    $('#applyFilters').on('click', function() {
        table.ajax.reload();
    });

    $('#branchFilter, #periodFilter').on('change', function() {
        table.ajax.reload();
    });
});
</script>
@endsection
