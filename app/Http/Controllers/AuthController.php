<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $r, AuditLogService $audit)
    {
        $data = $r->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($data, $r->boolean('remember'))) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->onlyInput('email');
        }$r->session()->regenerate();
        if (! $r->user()->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'บัญชีถูกระงับการใช้งาน']);
        }$r->user()->update(['last_login_at' => now()]);
        $audit->record($r->user(), 'LOGIN', 'user', $r->user()->id);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $r, AuditLogService $audit)
    {
        $audit->record($r->user(), 'LOGOUT', 'user', $r->user()->id);
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('login');
    }
}
