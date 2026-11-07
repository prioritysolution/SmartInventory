@extends('Auth.Layouts.auth-layout')
@section('content')
<div class="card si-auth-card border-0">
    <div class="card-body">
        <div class="si-auth-brand">
            <img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="Smart Inventory">
        </div>

        <h2 class="si-auth-title">Welcome Back!</h2>
        <p class="si-auth-subtitle">Login to access your account</p>

        <div class="mb-3">
            <label class="form-label" for="org_code">Organization Code</label>
            <div class="input-group">
                <span class="input-group-text border-end-0">
                    <i class="isax isax-building"></i>
                </span>
                <input type="text" id="org_code" class="form-control border-start-0 ps-0" placeholder="Enter Organization Code" autocomplete="off" value="{{ $rememberOrg ?? '' }}">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="auth_user">User Name</label>
            <div class="input-group">
                <span class="input-group-text border-end-0">
                    <i class="isax isax-user"></i>
                </span>
                <input type="text" id="auth_user" class="form-control border-start-0 ps-0" placeholder="Enter User Name" autocomplete="off" value="{{ $rememberUser ?? '' }}">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="auth_pass">Password</label>
            <div class="pass-group input-group">
                <span class="input-group-text border-end-0">
                    <i class="isax isax-lock"></i>
                </span>
                <input type="password" id="auth_pass" class="pass-inputs form-control border-start-0 ps-0 pe-5" placeholder="Enter Password" value="{{ $rememberPass ?? '' }}">
                <span class="isax toggle-password isax-eye-slash"></span>
            </div>
        </div>

        <div class="si-auth-options">
            <label class="si-remember" for="remember_me">
                <input class="form-check-input" id="remember_me" type="checkbox" @checked(!empty($rememberChecked))>
                <span>Remember Me</span>
            </label>
        </div>

        <div>
            <button type="button" onclick="proc_login()" class="btn si-auth-btn text-white w-100">
                <i class="isax isax-login"></i>
                <span>Sign In</span>
            </button>
        </div>

        <div class="si-auth-footer">
            <div class="si-auth-powered">
                <span>Powered by</span>
                <a href="https://prioritysolutions.in/" target="_blank" rel="noopener noreferrer" class="si-auth-powered-logo" title="Priority Solutions">
                    <span class="si-auth-powered-mark" aria-hidden="true">P</span>
                    <span class="si-auth-powered-name">
                        <span class="ps-priority">PRIORITY</span><span class="ps-solutions">SOLUTIONS</span>
                    </span>
                </a>
            </div>
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
