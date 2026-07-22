<!doctype html>
<html lang="th" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WIP Stock')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>if(localStorage.getItem('wip-stock-sidebar')==='collapsed')document.documentElement.classList.add('sidebar-collapsed')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full">
@auth
@php
    $nav = fn (bool $active) => $active
        ? 'sidebar-link-active'
        : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700';
    $pendingRequests = auth()->user()->isAdmin() ? \App\Models\Requisition::where('status', 'PENDING')->count() : 0;
@endphp

<div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-slate-950/30 backdrop-blur-sm lg:hidden"></div>
<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-60 -translate-x-full flex-col border-r border-slate-200 bg-white shadow-sm transition-all duration-300 lg:translate-x-0">
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-100 px-5">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center gap-3 no-underline">
            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-blue-600 shadow-sm shadow-blue-200">
                <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5 12 3 4 7.5m16 0L12 12m8-4.5V16.5L12 21m0-9L4 7.5M12 12v9M4 7.5v9L12 21"/></svg>
            </span>
            <span class="sidebar-label min-w-0"><strong class="block truncate text-base text-slate-950">WIP Stock</strong><small class="block truncate text-[11px] text-slate-400">Inventory Management</small></span>
        </a>
        <button id="sidebar-close" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 lg:hidden" aria-label="ปิดเมนู">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="scrollbar-thin flex-1 overflow-y-auto px-3 py-4">
        @include('layouts.navigation')
    </nav>

    <div class="shrink-0 border-t border-slate-100 p-3">
        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-2.5">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
            <div class="sidebar-label min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p><p class="truncate text-[11px] text-slate-400">{{ auth()->user()->isAdmin() ? 'ผู้ดูแลระบบ' : 'พนักงานคลัง' }}</p></div>
            <form class="sidebar-label" method="post" action="{{ route('logout') }}">@csrf<button class="rounded-lg p-2 text-slate-400 hover:bg-white hover:text-rose-600" title="ออกจากระบบ"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 8V5a2 2 0 0 0-2-2H5v18h7a2 2 0 0 0 2-2v-3m-4-4h11m0 0-3-3m3 3-3 3"/></svg></button></form>
        </div>
    </div>
</aside>

<div id="app-main" class="min-h-screen transition-all duration-300 lg:pl-60">
    <header class="sticky top-0 z-30 flex h-16 items-center border-b border-slate-200 bg-white/95 px-4 backdrop-blur lg:px-6">
        <button id="sidebar-open" class="mr-2 rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="เปิดเมนู"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <button id="desktop-sidebar-toggle" class="mr-3 hidden rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 lg:block" aria-label="ย่อหรือขยายเมนู"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <div class="min-w-0 shrink-0"><h1 class="truncate text-sm font-semibold text-slate-800">@yield('header', 'หน้าหลัก')</h1></div>

        <form action="{{ route('products.index') }}" class="mx-auto hidden w-full max-w-md px-8 md:block">
            <label class="relative block">
                <svg class="absolute left-3 top-2.5 size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                <input name="q" class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-3 focus:ring-blue-100" placeholder="ค้นหารหัสหรือชื่อสินค้า...">
            </label>
        </form>

        <div class="ml-auto flex items-center gap-2">
            <span class="hidden text-xs text-slate-400 xl:inline">{{ now()->format('d/m/Y') }}</span>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('requisitions.issues') }}" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="คำขอรออนุมัติ"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12h4"/></svg>@if($pendingRequests)<span class="absolute right-1 top-1 size-2 rounded-full bg-rose-500 ring-2 ring-white"></span>@endif</a>
            @endif
            <span class="grid size-8 place-items-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
            <span class="hidden max-w-32 truncate text-xs font-semibold text-slate-700 sm:block">{{ auth()->user()->name }}</span>
        </div>
    </header>

    <main class="mx-auto max-w-[1600px] p-4 lg:p-6">
        @if(session('success'))<div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"><span class="grid size-5 place-items-center rounded-full bg-emerald-500 text-xs text-white">✓</span>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><strong>ไม่สามารถดำเนินการได้</strong><ul class="mt-1 list-disc pl-6">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
@else
    @yield('content')
@endauth
@stack('scripts')
</body>
</html>
