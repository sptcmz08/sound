@extends('layouts.app')
@section('title','เข้าสู่ระบบ — WIP Stock')
@section('content')
<div class="flex min-h-screen items-center justify-center bg-slate-50 p-4">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-8">
        <div class="mb-8 flex items-center gap-3">
            <span class="grid size-10 place-items-center rounded-xl bg-blue-600 shadow-sm shadow-blue-200">
                <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5 12 3 4 7.5m16 0L12 12m8-4.5V16.5L12 21m0-9L4 7.5M12 12v9M4 7.5v9L12 21"/></svg>
            </span>
            <div><strong class="block text-base text-slate-950">WIP Stock</strong><span class="block text-xs text-slate-400">Inventory Management</span></div>
        </div>
        <h1 class="text-xl font-bold text-slate-900">เข้าสู่ระบบ</h1>
        <p class="mt-1 text-sm text-slate-500">กรอกบัญชีพนักงานเพื่อเริ่มใช้งานระบบ</p>
        @if($errors->any())<div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{$errors->first()}}</div>@endif
        <form method="post" action="{{route('login.store')}}" class="mt-6 space-y-4">@csrf
            <div><label class="label">อีเมล</label><input class="input" type="email" name="email" value="{{old('email')}}" placeholder="name@company.com" autofocus required></div>
            <div><label class="label">รหัสผ่าน</label><input class="input" type="password" name="password" placeholder="••••••••" required></div>
            <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"> จดจำการเข้าสู่ระบบ</label>
            <button class="btn-primary w-full justify-center">เข้าสู่ระบบ</button>
        </form>
        <p class="mt-7 text-center text-xs text-slate-400">WIP Stock · ระบบจัดการคลังสินค้า</p>
    </div>
</div>
@endsection
