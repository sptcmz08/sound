@extends('layouts.app')

@section('title', 'แดชบอร์ด')
@section('header', 'ภาพรวมระบบ')

@section('content')
<div class="space-y-6">
    <section>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="page-title">สวัสดี, {{ auth()->user()->name }}</h2>
                <p class="page-subtitle">เลือกงานที่ต้องการ หรือดูสถานะคลังล่าสุดได้จากหน้านี้</p>
            </div>
            <span class="badge-green">
                <span class="mr-2 size-2 rounded-full bg-emerald-500"></span>
                ระบบพร้อมใช้งาน
            </span>
        </div>

        @if(auth()->user()->canOperateStock())
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('stock.receive') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                <span class="mb-5 grid size-11 place-items-center rounded-xl bg-white/20">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0 4-4m-4 4-4-4M5 20h14"/></svg>
                </span>
                <strong class="block text-xl">รับสินค้าเข้าสต็อก</strong>
                <span class="mt-1 block text-sm text-emerald-50">คีย์สินค้าและจำนวนที่รับเข้า</span>
                <span class="absolute right-5 top-1/2 text-2xl transition group-hover:translate-x-1">→</span>
            </a>
            @endif

            <a href="{{ route('requisitions.withdraw') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 text-white shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                <span class="mb-5 grid size-11 place-items-center rounded-xl bg-white/20">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21V9m0 0 4 4m-4-4-4 4M5 4h14"/></svg>
                </span>
                <strong class="block text-xl">เบิกออกสต็อก</strong>
                <span class="mt-1 block text-sm text-amber-50">เลือกเบิก PART, WIP หรือ FG</span>
                <span class="absolute right-5 top-1/2 text-2xl transition group-hover:translate-x-1">→</span>
            </a>

            <a href="{{ route('requisitions.production') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-700 p-5 text-white shadow-lg shadow-violet-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                <span class="mb-5 grid size-11 place-items-center rounded-xl bg-white/20">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21V10l5 3V9l5 3V4h4v17M3 21h18"/></svg>
                </span>
                <strong class="block text-xl">ผลิต WIP / FG</strong>
                <span class="mt-1 block text-sm text-violet-50">เลือกวัตถุดิบและสร้างงานผลิต</span>
                <span class="absolute right-5 top-1/2 text-2xl transition group-hover:translate-x-1">→</span>
            </a>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('requisitions.issues') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-700 p-5 text-white shadow-lg shadow-blue-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                <span class="mb-5 grid size-11 place-items-center rounded-xl bg-white/20">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16m0 0-4-4m4 4-4 4M5 5v14"/></svg>
                </span>
                <strong class="block text-xl">จ่ายสินค้า</strong>
                <span class="mt-1 block text-sm text-blue-50">ตรวจ อนุมัติ และพิมพ์ใบเบิก</span>
                @if($pendingCount)
                    <span class="absolute right-4 top-4 rounded-full bg-white px-2.5 py-1 text-xs font-bold text-blue-700">รอ {{ $pendingCount }}</span>
                @else
                    <span class="absolute right-5 top-1/2 text-2xl transition group-hover:translate-x-1">→</span>
                @endif
            </a>
            @endif
        </div>
        @endif
    </section>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <div class="stat-card">
            <div class="flex items-start justify-between gap-3"><span class="grid size-11 place-items-center rounded-xl bg-blue-50 text-blue-600"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h10"/></svg></span><span class="badge-blue">PART</span></div>
            <strong class="mt-5 block text-3xl font-bold text-slate-950">{{ number_format($partCount) }}</strong><span class="text-sm font-medium text-slate-500">รายการ PART ในระบบ</span>
        </div>
        <div class="stat-card">
            <div class="flex items-start justify-between gap-3"><span class="grid size-11 place-items-center rounded-xl bg-violet-50 text-violet-600"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/></svg></span><span class="rounded-full bg-violet-50 px-3 py-1.5 text-sm font-bold text-violet-700 ring-1 ring-inset ring-violet-200">WIP</span></div>
            <strong class="mt-5 block text-3xl font-bold text-slate-950">{{ number_format($wipCount) }}</strong><span class="text-sm font-medium text-slate-500">รายการ WIP ในระบบ</span>
        </div>
        <div class="stat-card">
            <div class="flex items-start justify-between gap-3"><span class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14v11H5zM8 8V5h8v3"/></svg></span><span class="badge-green">FG</span></div>
            <strong class="mt-5 block text-3xl font-bold text-slate-950">{{ number_format($fgCount) }}</strong><span class="text-sm font-medium text-slate-500">สินค้าสำเร็จรูป</span>
        </div>
        <div class="stat-card">
            <div class="flex items-start justify-between gap-3"><span class="grid size-11 place-items-center rounded-xl bg-cyan-50 text-cyan-600"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M6 10h12v10H6z"/></svg></span><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-sm font-bold text-cyan-700 ring-1 ring-inset ring-cyan-200">คงเหลือ</span></div>
            <strong class="mt-5 block text-3xl font-bold text-slate-950">{{ number_format($stockLines) }}</strong><span class="text-sm font-medium text-slate-500">รายการที่มีสต็อก</span>
        </div>
        <div class="stat-card sm:col-span-2 lg:col-span-1">
            <div class="flex items-start justify-between gap-3"><span class="grid size-11 place-items-center rounded-xl bg-rose-50 text-rose-600"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v5m0 4h.01M5 4h14v16H5z"/></svg></span><span class="badge-red">รอดำเนินการ</span></div>
            <strong class="mt-5 block text-3xl font-bold text-slate-950">{{ number_format($pendingCount) }}</strong><span class="text-sm font-medium text-slate-500">คำขอรออนุมัติ</span>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <div class="table-shell">
            <div class="panel-header">
                <div><h3 class="text-xl font-bold text-slate-950">รายการล่าสุด</h3><p class="mt-0.5 text-sm text-slate-500">คำขอเบิกและงานผลิตที่เพิ่งสร้าง</p></div>
                <a href="{{ route('requisitions.index') }}" class="font-semibold text-blue-600 hover:text-blue-800">ดูทั้งหมด →</a>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>เลขที่เอกสาร</th><th>ประเภท</th><th>ผู้ขอ</th><th>สถานะ</th></tr></thead>
                    <tbody>
                        @forelse($recentRequests as $request)
                        <tr>
                            @php($displayProduct = $request->targetProduct ?? $request->items->first()?->product)
                            <td><div class="flex items-center gap-3"><x-product-image :product="$displayProduct" size="sm" /><a href="{{ route('requisitions.show', $request) }}" class="font-bold text-blue-700 hover:underline">{{ $request->request_no }}</a></div></td>
                            <td>{{ $request->request_type->label() }}</td>
                            <td>{{ $request->requester->name }}</td>
                            <td><span class="{{ $request->status->badgeClass() }}">{{ $request->status->label() }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="empty-state"><strong class="block text-slate-700">ยังไม่มีรายการ</strong><span class="text-sm">รายการเบิกและผลิตล่าสุดจะแสดงที่นี่</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">ขั้นตอนทำงาน</h3><p class="mt-0.5 text-sm text-slate-500">ภาพรวมตั้งแต่รับเข้าจนถึงจ่ายออก</p></div></div>
            <div class="panel-body space-y-5">
                @foreach([
                    ['1', 'รับสินค้าเข้า', 'แอดมินคีย์สินค้าและจำนวนเข้าคลัง', 'bg-emerald-500'],
                    ['2', 'เบิกหรือผลิต', 'เลือก PART, WIP หรือ FG ที่ต้องการ', 'bg-violet-500'],
                    ['3', 'ตรวจและอนุมัติ', 'แอดมินตรวจยอดก่อนหักสต็อก', 'bg-blue-500'],
                    ['4', 'พิมพ์ใบเบิกและลงนาม', 'ผู้ขอและผู้อนุมัติลงชื่อบนเอกสาร', 'bg-amber-500'],
                ] as [$number, $title, $description, $color])
                <div class="flex gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full {{ $color }} text-sm font-bold text-white">{{ $number }}</span>
                    <div><strong class="block text-slate-900">{{ $title }}</strong><p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p></div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection
