<!doctype html>
<html lang="th" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WIP Stock — Inventory Management System')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>if(localStorage.getItem('wip-stock-sidebar')==='collapsed')document.documentElement.classList.add('sidebar-collapsed')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full bg-slate-100 text-slate-800 antialiased font-sans">
@auth
@php
    $pendingRequests = auth()->user()->isAdmin() ? \App\Models\Requisition::where('status', 'PENDING')->count() : 0;
    $userInitial = mb_strtoupper(mb_substr(auth()->user()->name, 0, 1));
@endphp

<div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden"></div>

{{-- Sidebar with d:\stock Dark Gradient --}}
<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 shadow-2xl transition-all duration-300 lg:translate-x-0">
    {{-- App Logo --}}
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-4">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center gap-3 no-underline">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30">
                <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
            </div>
            <div class="sidebar-label min-w-0">
                <h1 class="truncate text-sm font-bold leading-tight text-white">WIP Stock</h1>
                <p class="truncate text-[11px] text-slate-400">Inventory System</p>
            </div>
        </a>
        <button id="sidebar-close" class="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden" aria-label="ปิดเมนู">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="scrollbar-thin flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @include('layouts.navigation')
    </nav>

    {{-- Bottom User Badge --}}
    <div class="shrink-0 border-t border-white/10 p-4">
        <div class="flex items-center gap-3">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 shadow-md">
                <span class="text-xs font-bold text-white">{{ $userInitial }}</span>
            </div>
            <div class="sidebar-label min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-400">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Staff' }}</p>
            </div>
            <form class="sidebar-label" method="post" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-rose-400 transition-colors" title="ออกจากระบบ">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Main Container --}}
<div id="app-main" class="min-h-screen flex flex-col transition-all duration-300 lg:pl-64">
    {{-- Glassmorphism Top Bar --}}
    <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200/80 bg-white/80 px-4 backdrop-blur-md lg:px-6">
        <div class="flex items-center gap-3">
            <button id="sidebar-open" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="เปิดเมนู">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <button id="desktop-sidebar-toggle" class="hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:block" aria-label="ย่อหรือขยายเมนู">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>
            <div class="min-w-0">
                <h1 class="truncate text-base font-semibold text-slate-800">@yield('header', 'หน้าหลัก')</h1>
            </div>
        </div>

        <form action="{{ route('products.index') }}" class="mx-auto hidden w-full max-w-md px-6 md:block">
            <div class="relative">
                <svg class="absolute left-3.5 top-2.5 size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                <input name="q" class="h-9 w-full rounded-xl border border-slate-200/80 bg-slate-50 pl-10 pr-4 text-xs text-slate-700 outline-none placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="ค้นหารหัสหรือชื่อสินค้า...">
            </div>
        </form>

        <div class="flex items-center gap-3">
            <span class="hidden text-xs text-slate-400 font-medium xl:inline">{{ now()->format('d/m/Y') }}</span>
            
            @if(auth()->user()->isAdmin())
            <a href="{{ route('requisitions.approvals') }}" class="relative p-2 rounded-xl text-slate-500 hover:bg-slate-100 transition-colors" title="คำขอรออนุมัติ">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                @if($pendingRequests)
                    <span class="absolute top-1.5 right-1.5 size-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                @endif
            </a>
            @endif

            <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 text-xs font-bold text-white shadow-sm">
                    {{ $userInitial }}
                </div>
                <span class="hidden text-xs font-medium text-slate-700 sm:block">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </header>

    {{-- Main Content View --}}
    <main class="flex-1 p-4 lg:p-6 space-y-6">
        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-800 shadow-sm">
                <span class="flex size-6 items-center justify-center rounded-xl bg-emerald-600 text-xs text-white font-bold">✓</span>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-800 shadow-sm">
                <strong class="font-bold block text-rose-950 mb-1">ไม่สามารถดำเนินการได้</strong>
                <ul class="list-disc pl-5 space-y-0.5 text-xs text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
@else
    @yield('content')
@endauth
@stack('scripts')
</body>
</html>
