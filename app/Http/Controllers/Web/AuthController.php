<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // صفحة تسجيل الدخول
    public function loginPage()
    {
        return view('auth.login');
    }

    // تنفيذ تسجيل الدخول
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($data)) {
            return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة']);
        }
// if (!Auth::attempt($data)) {
//     return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة']);
// }

// dd(session()->all()); // ✅ اختبار

        if (!auth()->user()->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'الحساب معطل']);
        }

        return redirect()->route('dashboard');
    }

    // صفحة التسجيل
    public function registerPage()
    {
        return view('auth.register');
    }

    // تنفيذ إنشاء الحساب
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::USER,
            'is_active' => true
        ]);

        return redirect()->route('login')->with('success' , 'تم إنشاء الحساب بنجاح');
    }

    // تسجيل الخروج
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
