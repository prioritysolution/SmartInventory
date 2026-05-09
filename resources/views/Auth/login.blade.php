@extends('Auth.Layouts.auth-layout')
@section('content')
<div class="card border-0 p-lg-3 shadow-lg">
    <div class="card-body">
        <div class="text-center mb-3">
            <h5 class="mb-2">Sign In</h5>
            <p class="mb-0">Please enter below details to access the dashboard</p>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Organization Code</label>
            <div class="input-group">
                <span class="input-group-text border-end-0">
                    <i class="isax isax-building"></i>
                </span>
                <input type="text" id="org_code" class="form-control border-start-0 ps-0" placeholder="Enter Organization Code" autocomplete="off">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">User Name</label>
            <div class="input-group">
                <span class="input-group-text border-end-0">
                    <i class="isax isax-user"></i>
                </span>
                <input type="text" id="auth_user" class="form-control border-start-0 ps-0" placeholder="Enter User Name" autocomplete="off">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="pass-group input-group">
                <span class="input-group-text border-end-0">
                    <i class="isax isax-lock"></i>
                </span>
                <span class="isax toggle-password isax-eye-slash"></span>
                <input type="password" id="auth_pass" class="pass-inputs form-control border-start-0 ps-0" placeholder="****************">
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
                <div class="form-check form-check-md mb-0">
                    <input class="form-check-input" id="remember_me" type="checkbox">           
                    <label for="remember_me" class="form-check-label mt-0">Remember Me</label>
                </div>
            </div>
            <div class="text-end">
                <a href="{{route('forgot-user-pass')}}">Forgot Password</a>
            </div>
        </div>
        <div class="mb-1">
            <button type="button" onclick="proc_login()" class="btn bg-primary-gradient text-white w-100">Sign In</button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    const baseUrl = "{{ url('/') }}";
</script>
<script src="{{ asset('template/assets/js/Auth/proc_login.js') }}"></script>
@endpush

