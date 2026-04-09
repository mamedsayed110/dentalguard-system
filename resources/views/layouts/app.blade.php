<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DentAI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    {{-- Main CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    @yield('head')
</head>

<body>

{{-- Navbar --}}
@if(auth()->check())
<nav class="navbar">
    <div class="logo">
        <div class="logo-icon">◇</div>
        <span>DentAI</span>
    </div>

    <div class="nav-menu">
        <a class="nav-item" href="{{ route('dashboard') }}">Dashboard</a>

        @if(auth()->user()->role === 'super_admin')
            <a class="nav-item" href="{{ route('admin.users') }}">Admin</a>
        @endif

        <a class="nav-item" href="{{ route('scans.upload') }}">Upload</a>
        <a class="nav-item" href="{{ route('scans.index') }}">History</a>
        <a class="nav-item" href="{{ route('chat') }}">AI Chat</a>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn-logout">Logout</button>
    </form>
</nav>
@endif

{{-- Main Content --}}
<div class="container">
    @yield('content')
</div>

{{-- Footer --}}
<div class="footer">
    © 2025 DentAI | All rights reserved
</div>

</body>
</html>
