@extends('AgentAuth.Layouts.auth-layout')
@section('content')
    <div class="login-content">
        <div class="login-userset">
            <div class="login-logo">
                <img src="{{ asset('agenttemplate/assets/img/logo.png') }}" alt="img">
            </div>
            <div class="login-userheading">
                <h3>Sign In</h3>
                <h4>Please login to your account</h4>
            </div>

            <div class="form-login">
                <label>Organization code Code</label>
                <div class="form-addons">
                    <input type="text" id="org_code" placeholder="Enter your organization code" autocomplete="off">
                </div>
            </div>
            <div class="form-login">
                <label>Agent Code</label>
                <div class="form-addons">
                    <input type="text" id="agent_code" placeholder="Enter your code" autocomplete="off">
                </div>
            </div>
            <div class="form-login">
                <label>Password</label>
                <div class="pass-group">
                    <input type="password" id="agent_pass" class="pass-input" placeholder="Enter your password">
                    <span class="fas toggle-password fa-eye-slash"></span>
                </div>
            </div>
            <div class="form-login">
                <button type="button" onclick="proc_agent_login()" class="btn btn-login">Sign In</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const baseUrl = "{{ url('/') }}";
</script>
<script src="{{ asset('agenttemplate/assets/js/Auth/agent_login.js') }}"></script>
@endpush
