<!-- 2023 SilverDust) S. Maceren --> 
@extends('layouts.main')

@section('content')
    <div class="m-3">
        <div class="bg-white p-3">
            <h3 class="text-dark">Push Notifications</h3>
            <div class="alert alert-secondary mb-3" role="alert">
                Send notifications to staffs or clients.
            </div>
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
        </div>
        <div class="bg-white mt-3 p-3 w-50">
            <div class="card">
                <form class="m-3" action="/submit-notification" method="POST">
                    @csrf
                    <div class="mb-3">
                        <div class="row">
                            <div class="col">
                                <label for="notifType" class="form-label">Type</label>
                                <select class="form-control font-sm" id="notifType" name="notiftype">
                                    @php
                                        $selectedOption = old('notiftype');
                                    @endphp
                                    <option value="email">Email</option>
                                    <option value="sms" disabled>SMS</option>
                                </select>
                            </div>
                            <div class="col">
                                <label for="notifTarget" class="form-label">Send To</label>
                                <select class="form-control font-sm" id="notifTarget" name="notiftarget">
                                    @php
                                        $selectedOption = old('notiftarget');
                                    @endphp
                                    <option value="staffs">Staff</option>
                                    <option value="clients" disabled>Client</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notifContent" class="form-label">Message</label>
                        <textarea class="form-control" style="resize:none; height: 300px;" name="notifmsg"></textarea>
                            @error('notifmsg')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-sm w-25 mt-3">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>  
@endsection