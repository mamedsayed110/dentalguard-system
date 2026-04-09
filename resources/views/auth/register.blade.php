@extends('layouts.app')

@section('content')
<style>
.register-card {
    max-width: 420px;
    margin: auto;
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.register-title {
    text-align: center;
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 20px;
}
</style>

<div class="register-card">
    <div class="register-title">Create Account</div>

    @if($errors->any())
        <div style="color:red;margin-bottom:15px">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}">
        @csrf

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button class="btn-login">Register</button>

        <div class="login-links">
            <a href="{{ route('login') }}">Already have an account?</a>
        </div>

    </form>
</div>
@endsection
