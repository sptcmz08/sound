<!doctype html>
<html lang="th" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>@yield('title','Simple Stock')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script>if(localStorage.getItem('simple-stock-sidebar')==='collapsed')document.documentElement.classList.add('sidebar-collapsed')</script>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full">
@auth
@php
    $nav = fn(bool $active) => $active
        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-950/30'
        : 'text-slate-300 hover:bg-white/10 hover:text-white';
    $pendingRequests = auth()->user()->isAdmin() ? \App\Models\Requisition::where('status','PENDING')->count() : 0;
@endphp

<div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden"></div>
<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-gradient-to-b from-slate-950 via-slate-900 to-slate-900 text-slate-300 shadow-2xl transition-all duration-300 lg:translate-x-0">
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-4">
        <a href="{{route('dashboard')}}" class="flex min-w-0 flex-1 items-center gap-3">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-950/40">
                <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5 12 3 4 7.5m16 0L12 12m8-4.5V16.5L12 21m0-9L4 7.5M12 12v9M4 7.5v9L12 21"/></svg>
            </span>
            <span class="sidebar-label min-w-0"><strong class="block truncate text-base text-white">Simple Stock</strong><small class="block truncate text-xs text-slate-400">Inventory Control</small></span>
        </a>
        <button id="sidebar-close" class="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden" aria-label="ปิดเมนู">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="scrollbar-thin flex-1 overflow-y-auto px-3 py-4">
        @include('layouts.navigation')
    </nav>

    <div class="shrink-0 border-t border-white/10 p-3">
        <div class="flex items-center gap-3 rounded-xl bg-white/5 p-2.5">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 text-sm font-bold text-white">{{mb_substr(auth()->user()->name,0,1)}}</span>
            <div class="sidebar-label min-w-0 flex-1"><p class="truncate text-sm font-semibold text-white">{{auth()->user()->name}}</p><p class="truncate text-xs text-slate-400">{{auth()->user()->email}}</p></div>
            <form class="sidebar-label" method="post" action="{{route('logout')}}">@csrf<button class="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white" title="ออกจากระบบ"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 8V5a2 2 0 0 0-2-2H5v18h7a2 2 0 0 0 2-2v-3m-4-4h11m0 0-3-3m3 3-3 3"/></svg></button></form>
        </div>
    </div>
</aside>

<div id="app-main" class="min-h-screen transition-all duration-300 lg:pl-64">
    <header class="sticky top-0 z-30 flex h-16 items-center border-b border-slate-200 bg-white/85 px-4 backdrop-blur-xl lg:px-6">
        <button id="sidebar-open" class="mr-2 rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 lg:hidden" aria-label="เปิดเมนู"><svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <button id="desktop-sidebar-toggle" class="mr-3 hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 lg:block" aria-label="ย่อ/ขยายเมนู"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <div class="min-w-0 flex-1"><h1 class="truncate text-xl font-bold text-slate-900">@yield('header','แดชบอร์ด')</h1></div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
            <a href="{{route('requisitions.issues')}}" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800" title="คำขอรอจ่าย"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12h4"/></svg>@if($pendingRequests)<span class="absolute right-1 top-1 size-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>@endif</a>
            @endif
            <div class="hidden items-center gap-2 rounded-xl px-2 py-1.5 sm:flex"><span class="grid size-8 place-items-center rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 text-xs font-bold text-white">{{mb_substr(auth()->user()->name,0,1)}}</span><span class="max-w-32 truncate text-sm font-semibold text-slate-700">{{auth()->user()->name}}</span></div>
        </div>
    </header>

    <main class="mx-auto max-w-[1680px] p-4 lg:p-6">
        @if(session('success'))<div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 font-semibold text-emerald-700"><span class="grid size-6 place-items-center rounded-full bg-emerald-500 text-sm text-white">✓</span>{{session('success')}}</div>@endif
        @if($errors->any())<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800"><strong>ไม่สามารถดำเนินการได้</strong><ul class="mt-1 list-disc pl-6">@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
@else
    @yield('content')
@endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
