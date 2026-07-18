@extends('layouts.app')
@section('title','เข้าสู่ระบบ — Simple Stock')
@section('content')
<div class="relative flex min-h-screen overflow-hidden bg-slate-950">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(37,99,235,.32),transparent_32%),radial-gradient(circle_at_80%_80%,rgba(6,182,212,.18),transparent_28%)]"></div>
    <div class="absolute inset-0 opacity-[.04] [background-image:linear-gradient(#fff_1px,transparent_1px),linear-gradient(90deg,#fff_1px,transparent_1px)] [background-size:40px_40px]"></div>
    <div class="relative m-auto grid w-full max-w-6xl overflow-hidden rounded-none bg-white shadow-2xl sm:mx-6 sm:rounded-3xl lg:grid-cols-2">
        <section class="hidden min-h-[660px] flex-col justify-between bg-gradient-to-br from-blue-700 via-blue-600 to-cyan-500 p-12 text-white lg:flex">
            <div class="flex items-center gap-3"><span class="grid size-12 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/25"><svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5 12 3 4 7.5m16 0L12 12m8-4.5V16.5L12 21m0-9L4 7.5M12 12v9M4 7.5v9L12 21"/></svg></span><div><strong class="block text-xl">Simple Stock</strong><span class="text-sm text-blue-100">Inventory Control System</span></div></div>
            <div><span class="mb-5 inline-flex rounded-full bg-white/15 px-4 py-2 text-sm ring-1 ring-white/20">จัดการสต๊อกอย่างมั่นใจ</span><h1 class="max-w-md text-4xl font-bold leading-tight">เห็นทุกความเคลื่อนไหว<br>ควบคุมทุกยอดคงเหลือ</h1><p class="mt-5 max-w-md text-base leading-7 text-blue-100">ระบบรับ-จ่าย Part และ FG ที่ออกแบบให้พนักงานทำงานได้รวดเร็ว พร้อมประวัติที่ตรวจสอบย้อนหลังได้ทุกขั้นตอน</p></div>
            <div class="grid grid-cols-3 gap-4 text-center"><div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15"><strong class="block text-2xl">Real-time</strong><span class="text-xs text-blue-100">ยอดคงเหลือ</span></div><div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15"><strong class="block text-2xl">Secure</strong><span class="text-xs text-blue-100">ตรวจสอบสิทธิ์</span></div><div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15"><strong class="block text-2xl">Audit</strong><span class="text-xs text-blue-100">ประวัติครบถ้วน</span></div></div>
        </section>
        <section class="flex min-h-screen items-center p-6 sm:min-h-[660px] sm:p-12 lg:p-16">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-9 lg:hidden"><span class="grid size-12 place-items-center rounded-2xl bg-brand-600 text-white shadow-lg shadow-blue-200"><svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5 12 3 4 7.5m16 0L12 12m8-4.5V16.5L12 21m0-9L4 7.5M12 12v9M4 7.5v9L12 21"/></svg></span></div>
                <p class="text-sm font-bold uppercase tracking-widest text-brand-600">ยินดีต้อนรับ</p><h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">เข้าสู่ระบบ</h2><p class="mt-2 text-sm text-slate-500">กรอกข้อมูลบัญชีเพื่อเข้าสู่ระบบจัดการสต๊อก</p>
                @if($errors->any())<div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{$errors->first()}}</div>@endif
                <form method="post" action="{{route('login.store')}}" class="mt-8 space-y-5">@csrf
                    <div><label class="label">อีเมล</label><div class="relative"><svg class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l9 6 9-6M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg><input class="input pl-11" type="email" name="email" value="{{old('email')}}" placeholder="name@company.com" autofocus required></div></div>
                    <div><div class="flex justify-between"><label class="label">รหัสผ่าน</label></div><div class="relative"><svg class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10V7a5 5 0 0 1 10 0v3m-9 11h8a3 3 0 0 0 3-3v-5a3 3 0 0 0-3-3H8a3 3 0 0 0-3 3v5a3 3 0 0 0 3 3Z"/></svg><input class="input pl-11" type="password" name="password" placeholder="••••••••" required></div></div>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"> จดจำการเข้าสู่ระบบ</label>
                    <button class="btn-primary w-full py-3">เข้าสู่ระบบ <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg></button>
                </form>
                <p class="mt-8 text-center text-xs text-slate-400">Simple Stock System · Secure Inventory Management</p>
            </div>
        </section>
    </div>
</div>
@endsection
