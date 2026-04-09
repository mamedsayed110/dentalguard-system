@extends('layouts.app')

@section('content')
<style>
.login-card {
    max-width: 420px;
    margin: auto;
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.login-title {
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    color: #1F2937;
    margin-bottom: 10px;
}

.login-desc {
    text-align: center;
    font-size: 14px;
    color: #6B7280;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #E5E7EB;
    font-size: 14px;
}

.btn-login {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg,#00D4FF,#0EA5E9);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
}

.btn-login:hover {
    opacity: 0.9;
}

.login-links {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    font-size: 13px;
}
.login-links a {
    color: #00D4FF;
    text-decoration: none;
}
</style>

<div class="login-card">
    <div class="login-title">Welcome to DentAI</div>
    <div class="login-desc">Login to your dashboard</div>

    @if($errors->any())
        <div style="color:red;margin-bottom:10px">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button class="btn-login">Login</button>

        <div class="login-links">
            <a href="{{ route('register') }}">Register</a>
        </div>

    </form>
</div>
@endsection
