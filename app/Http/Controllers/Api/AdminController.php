<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class AdminController extends Controller
{
    /**
     * عرض جميع المستخدمين
     */
    public function listUsers(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json($users);
    }

    /**
     * إنشاء مستخدم جديد
     */
    public function createUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => ['required', new Enum(UserRole::class)],
            'phone' => 'nullable|string',
            'doctor_id' => 'nullable|exists:users,id', // إذا كان مريض
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'doctor_id' => $data['doctor_id'] ?? null,
            'is_active' => true,
        ]);

        $this->log('CREATE_USER', ['created_user_id' => $user->id]);

        return response()->json([
            'message' => 'تم إنشاء المستخدم بنجاح',
            'user' => $user
        ], 201);
    }

    /**
     * عرض مستخدم محدد
     */
    public function showUser($id)
    {
        $user = User::with(['scans', 'patients'])->findOrFail($id);
        return response()->json($user);
    }

    /**
     * تحديث مستخدم
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role' => ['sometimes', new Enum(UserRole::class)],
            'phone' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($data);

        $this->log('UPDATE_USER', ['updated_user_id' => $user->id]);

        return response()->json([
            'message' => 'تم تحديث المستخدم بنجاح',
            'user' => $user
        ]);
    }

    /**
     * حذف مستخدم
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // لا يمكن حذف Super Admin
        if ($user->isSuperAdmin()) {
            return response()->json([
                'error' => 'لا يمكن حذف مدير النظام'
            ], 403);
        }

        $this->log('DELETE_USER', ['deleted_user_id' => $user->id, 'email' => $user->email]);

        $user->delete();

        return response()->json(['message' => 'تم حذف المستخدم بنجاح']);
    }

    /**
     * تفعيل/تعطيل مستخدم
     */
    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->isSuperAdmin()) {
            return response()->json([
                'error' => 'لا يمكن تعطيل مدير النظام'
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $this->log('TOGGLE_USER_STATUS', [
            'user_id' => $user->id,
            'new_status' => $user->is_active
        ]);

        return response()->json([
            'message' => $user->is_active ? 'تم تفعيل المستخدم' : 'تم تعطيل المستخدم',
            'is_active' => $user->is_active
        ]);
    }

    /**
     * إعدادات النظام
     */
    public function getSettings()
    {
        // يمكن تخزينها في ملف config أو جدول settings
        return response()->json([
            'ai_endpoint' => config('services.ai.endpoint'),
            'ai_timeout' => config('services.ai.timeout', 60),
            'max_upload_size' => config('app.max_upload_size', 8192),
            'allowed_extensions' => ['jpg', 'jpeg', 'png'],
        ]);
    }

    public function updateSettings(Request $request)
    {
        // تحديث الإعدادات
        // يمكن استخدام spatie/laravel-settings أو جدول في الداتابيز
        
        $this->log('UPDATE_SETTINGS', $request->all());

        return response()->json(['message' => 'تم تحديث الإعدادات']);
    }

    private function log($action, array $meta = [])
    {
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'ip' => request()->ip(),
            'meta' => $meta
        ]);
    }
}