@extends('Auth.Layouts.auth-layout')
@section('content')
<div class="card border-0 p-lg-3 shadow-lg">
    <div class="card-body">
        <div class="text-center mb-3">
            <h5 class="mb-2">Forgot Password</h5>
            <p class="mb-0">Enter your email to reset your password</p>
        </div>
        <form>
            @csrf
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0">
                        <i class="isax isax-sms-notification"></i>
                    </span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="Enter Email Address" required>
                </div>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn bg-primary-gradient text-white w-100">Send Reset Link</button>
            </div>
            <div class="text-center">
                <a href="{{route('login-index')}}">Back to Login</a>
            </div>
        </form>
    </div>   
</div>
@endsection
