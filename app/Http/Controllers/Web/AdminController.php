<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::where('role','!=','super_admin')->get();
        return view('admin.users', compact('users'));
    }

    public function toggle($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return back();
    }
}
