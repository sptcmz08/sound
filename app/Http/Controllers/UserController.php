<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', ['users' => User::orderBy('name')->paginate(30)]);
    }

    public function store(Request $r, AuditLogService $audit)
    {
        $data = $r->validate(['name' => ['required', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'role' => ['required', Rule::in([User::ADMIN, User::STOCK_STAFF, User::VIEWER])], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        $user = User::create($data + ['is_active' => true, 'must_change_password' => true]);
        $audit->record($r->user(), 'CREATE', 'user', $user->id, null, $user->only('name', 'email', 'role'));

        return back()->with('success', 'เพิ่มผู้ใช้แล้ว');
    }

    public function update(Request $r, User $user, AuditLogService $audit)
    {
        $data = $r->validate(['name' => ['required', 'max:255'], 'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)], 'role' => ['required', Rule::in([User::ADMIN, User::STOCK_STAFF, User::VIEWER])], 'is_active' => ['nullable', 'boolean'], 'password' => ['nullable', 'string', 'min:8', 'confirmed']]);
        $newActive = $r->boolean('is_active');
        $newRole = $data['role'];
        if ($user->is($r->user()) && ! $newActive) {
            throw ValidationException::withMessages(['is_active' => 'ไม่สามารถระงับบัญชีที่กำลังใช้งานอยู่ได้']);
        }
        if ($user->role === User::ADMIN && ($newRole !== User::ADMIN || ! $newActive) && User::where('role', User::ADMIN)->where('is_active', true)->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'ระบบต้องมี Administrator ที่ใช้งานได้อย่างน้อย 1 คน']);
        }
        $old = $user->only('name', 'email', 'role', 'is_active');
        $data['is_active'] = $newActive;
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['must_change_password'] = true;
        }$user->update($data);
        $audit->record($r->user(), 'UPDATE', 'user', $user->id, $old, $user->fresh()->only('name', 'email', 'role', 'is_active'));

        return back()->with('success', 'แก้ไขผู้ใช้แล้ว');
    }
}
