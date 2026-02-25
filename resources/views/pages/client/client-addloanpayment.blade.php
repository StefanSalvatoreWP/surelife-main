<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="m-3">
        <div class="bg-white p-3">
            <h3 class="text-dark">New Loan Payment ( {{ $clients->LastName . ', ' . $clients->FirstName }} )</h3>
            <div class="alert alert-dark mb-3" role="alert">
                Create a new loan payment for the selected client.
            </div>
            @if(session('duplicate'))
                <div class="alert alert-danger">
                    {{ session('duplicate') }}
                </div>
            @endif
            <a href="/client-view/{{ $clients->Id }}" class="btn btn-outline-secondary btn-sm" role="button">Return</a>
        </div>
        <div class="bg-white mt-3 p-3 w-50">
            <div class="card">
                <form class="m-3" action="/client-submit-loanpayment/{{ $clients->Id }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <input type="hidden" name="clientbranch" value={{ $clients->BranchId }} />
                        <input type="hidden" name="clientregion" value={{ $clients->RegionId }} />

                        <div class="row">
                            <div class="col">
                                <label for="totalLoanAmount" class="form-label">Total Loan Amount</label>
                                <input type="text" class="form-control font-sm" id="orSeriesCode" name="orseriescode" maxlength="30" value="{{ $loanRequestData->Amount }}" disabled />
                            </div>
                            <div class="col">
                                <label for="totalLoanAmount" class="form-label">Loan Balance</label>
                                <input type="text" class="form-control font-sm" id="orSeriesCode" name="orseriescode" maxlength="30" value="{{ $loanBalance }}" disabled />
                            </div>
                        </div>          
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col">
                                <label for="paymentAmount" class="form-label">Payment Amount</label>
                                <select class="form-control font-sm" id="paymentAmount" name="paymentamount">
                                    @php
                                        $selectedPaymentAmount = old('paymentamount');
                                    @endphp
                                    @foreach($amounts as $amount)
                                        <option value="{{ $amount }}">{{ $amount }}</option>
                                    @endforeach
                                </select>
                                @error('paymentamount')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <select class="form-control font-sm" id="paymentMethod" name="paymentmethod">
                                    @php
                                        $selectedPaymentMethod = old('paymentmethod');
                                    @endphp
                                    <option value="Cash" {{ $selectedPaymentMethod === 'Cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                                @error('paymentmethod')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col">
                                @php 
                                    $prevOrSeriesCode = old('orseriescode'); 
                                @endphp
                                <label for="orSeriesCode" class="form-label">O.R Series Code</label>
                                <input type="text" class="form-control font-sm" id="orSeriesCode" name="orseriescode" maxlength="30" value="{{ $prevOrSeriesCode }}" />
                                    @error('orseriescode')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col">
                                @php 
                                    $prevOrNo = old('orno'); 
                                @endphp
                                <label for="orNo" class="form-label">O.R No.</label>
                                <input type="text" class="form-control font-sm" id="orNo" name="orno" maxlength="30" value="{{ $prevOrNo }}" />
                                    @error('orno')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">       
                            <div class="col">
                                @php
                                    $prevPaymentDate = old('paymentdate');
                                @endphp
                                <label for="paymentDate" class="form-label">Payment Date</label>
                                <input type="date" class="form-control font-sm" id="paymentDate" name="paymentdate" maxlength="30" value="{{ $prevPaymentDate }}" />
                                    @error('paymentdate')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                            </div>
                            <div class="col"></div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-sm w-25 mt-3">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection