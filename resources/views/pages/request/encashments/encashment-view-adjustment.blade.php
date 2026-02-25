<!-- 2024 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="m-3">
        <div class="bg-white p-3">
            <h3 class="text-dark">Adjustments</h3>
            <div class="alert alert-secondary mb-3" role="alert">
                Add incentives and adjustments to the encashment request.
            </div>
            @if(session('duplicate'))
                <div class="alert alert-danger">
                    {{ session('duplicate') }}
                </div>
            @endif
            <a href="/view-req-encashment/{{ $encashmentData->Id }}" class="btn btn-outline-secondary btn-sm" role="button">Return</a>
        </div>
        <div class="bg-white mt-3 p-3 w-50">
            <div class="card">
                <form class="m-3" action="/submit-encashment-adjustment/{{ $encashmentData->Id }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <div class="row">
                            <div class="col">
                                @php
                                    $prevIncentives = old('incentives', $encashmentData->Incentives);
                                @endphp
                                <label for="incentives" class="form-label">Incentives</label>
                                <input type="number" class="form-control font-sm" id="incentives" name="incentives" maxlength="30" value="{{ $prevIncentives }}"/>
                                    @error('incentives')
                                    <p class="text-danger">{{$message}}</p>
                                    @enderror
                            </div>
                            <div class="col">
                                @php
                                    $prevAdjustments = old('adjustments', $encashmentData->Adjustments);
                                @endphp
                                <label for="adjustments" class="form-label">Adjustments</label>
                                <input type="number" class="form-control font-sm" id="adjustments" name="adjustments" maxlength="30" value="{{ $prevAdjustments }}"/>
                                    @error('adjustments')
                                    <p class="text-danger">{{$message}}</p>
                                    @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col">
                                @php
                                    $prevIncentivesRemarks = old('incentivesremarks', $encashmentData->IncentivesRemarks);
                                @endphp
                                <label for="incentivesRemarks" class="form-label">Incentives Remarks</label>
                                <textarea class="form-control font-sm" id="incentivesRemarks" name="incentivesremarks" style="resize:none; height: 150px;">{{ $prevIncentivesRemarks }}</textarea>
                            </div>
                            <div class="col">
                                @php
                                    $prevAdjustmentsRemarks = old('adjustmentsremarks', $encashmentData->AdjustmentsRemarks);
                                @endphp
                                <label for="adjustmentsRemarks" class="form-label">Adjustments Remarks</label>
                                <textarea class="form-control font-sm" id="adjustmentsRemarks" name="adjustmentsremarks" style="resize:none; height: 150px;">{{ $prevAdjustmentsRemarks }}</textarea>
                            </div>
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