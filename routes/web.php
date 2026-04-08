<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});

// Register Page
Route::get('/register', function () {
    return file_get_contents(public_path('register.html'));
});

// Login Page
Route::get('/login', function () {
    return file_get_contents(public_path('login.html'));
});

// Dashboard
Route::get('/dashboard', function () {
    return file_get_contents(public_path('dashboard.html'));
});

// Don't use fallback - it conflicts with API routes
