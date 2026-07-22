@extends('layouts.app')

@section('title', 'ภาพรวมระบบสต็อก')
@section('header', 'ภาพรวมระบบ (Executive Dashboard)')

@section('content')
<div class="space-y-8">
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 text-white shadow-xl shadow-slate-900/10">
        <div class="absolute -right-10 -top-10 size-64 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute -bottom-10 right-20 size-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3.5 py-1 text-xs font-bold text-emerald-300 ring-1 ring-emerald-500/30">
                    <span class="size-2 rounded-full bg-emerald-400 animate-ping"></span>
                    ระบบพร้อมใช้งาน · Enterprise Stock Control System
                </span>
                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">สวัสดีคุณ {{ auth()->user()->name }}</h2>
                <p class="mt-2 text-sm text-slate-300 max-w-xl">ยินดีต้อนรับสู่ระบบคุมสต็อกสินค้า PART, SUPPLY, WIP และ FG แบบ Real-time</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('reports.balances') }}" class="btn bg-white/10 text-white backdrop-blur-md hover:bg-white/20">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    เช็กสต็อกคงเหลือ
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('products.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-500 shadow-lg shadow-blue-600/30">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    + เพิ่มสินค้าใหม่
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick Action Tiles --}}
    @if(auth()->user()->canOperateStock())
    <section>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span>⚡</span> ทางลัดการทำรายการสต็อก
            </h3>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('stock.receive') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-700 p-6 text-white shadow-lg shadow-emerald-500/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-500/35">
                <div class="mb-6 inline-flex size-12 items-center justify-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <strong class="block text-xl font-bold">รับสินค้าเข้า (Supplier)</strong>
                <span class="mt-1 block text-xs text-emerald-100 font-medium">คีย์ PART และวัสดุสิ้นเปลืองเข้าคลัง</span>
                <span class="absolute right-6 top-6 size-8 place-items-center rounded-full bg-white/10 text-lg transition-transform duration-300 group-hover:translate-x-1 hidden sm:grid">→</span>
            </a>
            @endif

            <a href="{{ route('requisitions.withdraw') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-500 to-orange-600 p-6 text-white shadow-lg shadow-amber-500/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-amber-500/35">
                <div class="mb-6 inline-flex size-12 items-center justify-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </div>
                <strong class="block text-xl font-bold">เบิกสินค้าออกจากสต็อก</strong>
                <span class="mt-1 block text-xs text-amber-100 font-medium">เลือกเบิก PART, WIP หรือ FG</span>
                <span class="absolute right-6 top-6 size-8 place-items-center rounded-full bg-white/10 text-lg transition-transform duration-300 group-hover:translate-x-1 hidden sm:grid">→</span>
            </a>

            <a href="{{ route('requisitions.production') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 to-purple-800 p-6 text-white shadow-lg shadow-violet-500/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-violet-500/35">
                <div class="mb-6 inline-flex size-12 items-center justify-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <strong class="block text-xl font-bold">ผลิต WIP / FG</strong>
                <span class="mt-1 block text-xs text-violet-100 font-medium">ตัดส่วนประกอบ BOM และเพิ่มเข้าสต็อก</span>
                <span class="absolute right-6 top-6 size-8 place-items-center rounded-full bg-white/10 text-lg transition-transform duration-300 group-hover:translate-x-1 hidden sm:grid">→</span>
            </a>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('requisitions.issues') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white shadow-lg shadow-blue-500/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-blue-500/35">
                <div class="mb-6 inline-flex size-12 items-center justify-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <strong class="block text-xl font-bold">ตรวจและอนุมัติจ่าย</strong>
                <span class="mt-1 block text-xs text-blue-100 font-medium">อนุมัติใบเบิก พิมพ์เอกสาร และตัดสต็อก</span>
                @if($pendingCount)
                    <span class="absolute right-6 top-6 rounded-full bg-rose-500 px-3 py-1 text-xs font-bold text-white shadow-md ring-2 ring-white animate-pulse">รอ {{ $pendingCount }}</span>
                @else
                    <span class="absolute right-6 top-6 size-8 place-items-center rounded-full bg-white/10 text-lg transition-transform duration-300 group-hover:translate-x-1 hidden sm:grid">→</span>
                @endif
            </a>
            @endif
        </div>
    </section>
    @endif

    {{-- Metric Stat Cards --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <span class="badge-part">PART</span>
            </div>
            <strong class="mt-4 block text-3xl font-black text-slate-900">{{ number_format($partCount) }}</strong>
            <span class="text-xs font-semibold text-slate-400">อะไหล่ผลิต (ชิ้นส่วน)</span>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </span>
                <span class="badge-supply">SUPPLY</span>
            </div>
            <strong class="mt-4 block text-3xl font-black text-slate-900">{{ number_format($supplyCount ?? 0) }}</strong>
            <span class="text-xs font-semibold text-slate-400">วัสดุสิ้นเปลือง (ไม่ลง BOM)</span>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
                <span class="badge-wip">WIP</span>
            </div>
            <strong class="mt-4 block text-3xl font-black text-slate-900">{{ number_format($wipCount) }}</strong>
            <span class="text-xs font-semibold text-slate-400">งานประกอบย่อย</span>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14v11H5zM8 8V5h8v3"/></svg>
                </span>
                <span class="badge-fg">FG</span>
            </div>
            <strong class="mt-4 block text-3xl font-black text-slate-900">{{ number_format($fgCount) }}</strong>
            <span class="text-xs font-semibold text-slate-400">สินค้าสำเร็จรูปพร้อมขาย</span>
        </div>

        <div class="stat-card sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="badge-red">รออนุมัติ</span>
            </div>
            <strong class="mt-4 block text-3xl font-black text-slate-900">{{ number_format($pendingCount) }}</strong>
            <span class="text-xs font-semibold text-slate-400">คำขอเบิกค้างอนุมัติ</span>
        </div>
    </section>

    {{-- Activity & Recent Transactions --}}
    <section class="grid gap-6 xl:grid-cols-[1fr_380px]">
        <div class="table-shell">
            <div class="panel-header">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">รายการเคลื่อนไหวล่าสุด</h3>
                    <p class="mt-0.5 text-xs text-slate-500">คำขอเบิกและงานผลิตที่เพิ่งสร้างในระบบ</p>
                </div>
                <a href="{{ route('requisitions.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline flex items-center gap-1">
                    ดูทั้งหมด
                    <span>→</span>
                </a>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>เลขที่เอกสาร</th>
                            <th>ประเภทรายการ</th>
                            <th>ผู้ขอทำรายการ</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $request)
                        <tr>
                            @php($displayProduct = $request->targetProduct ?? $request->items->first()?->product)
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-product-image :product="$displayProduct" size="sm" />
                                    <a href="{{ route('requisitions.show', $request) }}" class="font-bold text-blue-600 hover:underline">
                                        {{ $request->request_no }}
                                    </a>
                                </div>
                            </td>
                            <td><span class="badge-slate">{{ $request->request_type->label() }}</span></td>
                            <td class="font-medium text-slate-700">{{ $request->requester->name }}</td>
                            <td><span class="{{ $request->status->badgeClass() }}">{{ $request->status->label() }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <strong class="block text-slate-700">ยังไม่มีรายการ</strong>
                                <span class="text-xs text-slate-400">รายการเบิกและผลิตล่าสุดจะแสดงที่นี่</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Workflow Status Card --}}
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">ขั้นตอนการดำเนินงาน</h3>
                    <p class="mt-0.5 text-xs text-slate-500">ผังการทำงานระบบคุมสต็อก</p>
                </div>
            </div>
            <div class="panel-body space-y-5">
                @foreach([
                    ['1', 'รับเข้า (Supplier)', 'คีย์สินค้าประเภท PART หรือ SUPPLY เข้าคลัง', 'bg-emerald-500', 'text-emerald-500'],
                    ['2', 'ผลิต WIP / FG', 'เลือกชิ้นส่วนตาม BOM ประกอบเข้าสต็อก', 'bg-violet-500', 'text-violet-500'],
                    ['3', 'เบิกหรือขาย', 'เบิกอะไหล่ หรือขาย FG พร้อมเลือก Option', 'bg-blue-500', 'text-blue-500'],
                    ['4', 'อนุมัติและตัดสต็อก', 'ผู้ดูแลอนุมัติ พิมพ์ใบเบิก และตัดสต็อกอัตโนมัติ', 'bg-amber-500', 'text-amber-500'],
                ] as [$number, $title, $description, $bgColor, $textColor])
                <div class="flex gap-4 items-start">
                    <span class="grid size-9 shrink-0 place-items-center rounded-2xl {{ $bgColor }} text-sm font-bold text-white shadow-md">
                        {{ $number }}
                    </span>
                    <div>
                        <strong class="block text-sm text-slate-900 font-bold">{{ $title }}</strong>
                        <p class="mt-0.5 text-xs text-slate-500 leading-relaxed">{{ $description }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

