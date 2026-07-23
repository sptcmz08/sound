@extends('layouts.app')

@section('title', 'แดชบอร์ด — WIP Stock')
@section('header', 'แดชบอร์ด')

@section('content')
@php
    $typeTotal = max(1, $partCount + $supplyCount + $wipCount + $fgCount);
    $typeStats = [
        ['PART', 'อะไหล่ผลิต', $partCount, 'bg-blue-500', 'text-blue-600', 'bg-blue-50'],
        ['SUPPLY', 'วัสดุสิ้นเปลือง', $supplyCount, 'bg-cyan-500', 'text-cyan-600', 'bg-cyan-50'],
        ['WIP', 'งานระหว่างประกอบ', $wipCount, 'bg-purple-500', 'text-purple-600', 'bg-purple-50'],
        ['FG', 'สินค้าพร้อมขาย', $fgCount, 'bg-emerald-500', 'text-emerald-600', 'bg-emerald-50'],
    ];
@endphp

<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="page-kicker">ภาพรวมระบบ</span>
            <h2 class="page-title">สวัสดีคุณ {{ auth()->user()->name }}</h2>
            <p class="page-subtitle">สรุปสถานะสต็อกและรายการเคลื่อนไหวนำเข้า-เบิกออก ประจำวันที่ {{ now()->format('d/m/Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2.5">
            <a href="{{ route('reports.balances') }}" class="btn-secondary">
                <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1.5-3m8.25 3l1.5-3m-10.5 0h10.5M6 7.5h12M6 10.5h12"/></svg>
                ดูยอดคงเหลือ
            </a>
            @if(auth()->user()?->canOperateStock())
            <a href="{{ route('requisitions.withdraw') }}" class="btn-primary">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                + เบิกสินค้าออก
            </a>
            @endif
        </div>
    </div>

    {{-- KPI Cards Grid (d:\stock design) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Value --}}
        <div class="metric-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">มูลค่าสต็อกรวม</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">฿{{ number_format($stockValue, 2) }}</p>
                    <p class="text-xs font-medium text-emerald-600 mt-1 flex items-center gap-1">
                        <span class="inline-block size-1.5 rounded-full bg-emerald-500"></span>
                        {{ number_format($stockQuantity, 2) }} หน่วยในทุกคลัง
                    </p>
                </div>
                <div class="size-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 text-white shrink-0">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-9h6a2 2 0 010 4H9a2 2 0 000 4h6"/></svg>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Items --}}
        <div class="metric-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">รายการสินค้าทั้งหมด</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($productCount) }} <span class="text-xs font-normal text-slate-400">รายการ</span></p>
                    <p class="text-xs font-medium text-blue-600 mt-1">แบ่งเป็น PART · SUPPLY · WIP · FG</p>
                </div>
                <div class="size-11 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20 text-white shrink-0">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                </div>
            </div>
        </div>

        {{-- Card 3: Low Stock --}}
        <div class="metric-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">สินค้าใกล้หมด</p>
                    <p class="text-2xl font-bold {{ $lowStockCount ? 'text-rose-600' : 'text-slate-900' }} mt-1">{{ number_format($lowStockCount) }} <span class="text-xs font-normal text-slate-400">รายการ</span></p>
                    <a href="{{ route('reports.balances') }}" class="text-xs font-semibold text-rose-600 hover:underline mt-1 inline-flex items-center gap-0.5">
                        ตรวจสอบรายการ →
                    </a>
                </div>
                <div class="size-11 rounded-2xl {{ $lowStockCount ? 'bg-gradient-to-br from-rose-500 to-red-600 shadow-rose-500/20' : 'bg-gradient-to-br from-slate-400 to-slate-500 shadow-slate-400/20' }} flex items-center justify-center shadow-lg text-white shrink-0">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                </div>
            </div>
        </div>

        {{-- Card 4: Month Sales & Pending --}}
        <div class="metric-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">ยอดขายเดือนนี้</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">฿{{ number_format($monthSales, 2) }}</p>
                    <p class="text-xs font-medium {{ $pendingCount ? 'text-amber-600' : 'text-slate-400' }} mt-1">
                        {{ $pendingCount ? "มี {$pendingCount} ใบเบิกรอ Admin อนุมัติ" : 'ไม่มีใบเบิกรออนุมัติ' }}
                    </p>
                </div>
                <div class="size-11 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/20 text-white shrink-0">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 005.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Row (d:\stock style) --}}
    @if(auth()->user()?->canOperateStock())
    <div class="panel">
        <div class="panel-header">
            <div>
                <h3 class="section-title">ทำรายการด่วน</h3>
                <p class="section-subtitle">กดทำรายการสต็อก นำเข้า ผลิต หรือบันทึกการขายได้ทันที</p>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 p-4">
            @foreach([
                [route('operations.create', 'supplier-receive'), 'รับเข้าสต็อก', 'Supplier → Stock', 'from-emerald-500 to-teal-600', 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12L12 16.5m0 0l4.5-4.5M12 16.5V3'],
                [route('requisitions.withdraw'), 'เบิกออกสต็อก', 'ขออนุมัติเบิกพัสดุ', 'from-amber-500 to-orange-600', 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5'],
                [route('requisitions.production'), 'สั่งผลิต WIP / FG', 'PART → WIP → FG', 'from-purple-500 to-indigo-600', 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6.75M9 11.25h6.75M9 15.75h6.75'],
                [route('operations.create', 'sale'), 'บันทึกการขาย', 'FG + Options', 'from-blue-500 to-cyan-600', 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
                [route('operations.create', 'claim'), 'เคลมสินค้า', 'รับคืนจากลูกค้า', 'from-pink-500 to-rose-600', 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99'],
                [route('reports.cost-profit'), 'รายงานสรุป', 'ต้นทุนและกำไร', 'from-slate-600 to-slate-800', 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 005.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941'],
            ] as [$url, $title, $desc, $gradient, $path])
            <a href="{{ $url }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md hover:scale-[1.02] transition-all group">
                <div class="size-10 rounded-xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-white shadow-md shrink-0">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <strong class="block text-xs font-bold text-slate-800 truncate group-hover:text-blue-600 transition-colors">{{ $title }}</strong>
                    <span class="block text-[10px] text-slate-400 truncate mt-0.5">{{ $desc }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Product Type Ratio + Recent Movements --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Product Type Ratio Card --}}
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="section-title">สัดส่วนประเภทสินค้า</h3>
                    <p class="section-subtitle">จำแนกตามประเภท PART, SUPPLY, WIP และ FG</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn-ghost">ดูสินค้าทั้งหมด</a>
            </div>
            <div class="panel-body space-y-4">
                @foreach($typeStats as [$type, $label, $count, $bar, $text, $soft])
                <div>
                    <div class="mb-1.5 flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-700 flex items-center gap-2">
                            <span class="size-2.5 rounded-full {{ $bar }}"></span>
                            {{ $type }} — {{ $label }}
                        </span>
                        <strong class="{{ $text }} font-bold">{{ $count }} รายการ</strong>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full {{ $soft }}">
                        <div class="h-full rounded-full {{ $bar }} transition-all duration-500" style="width: {{ max(4, ($count / $typeTotal) * 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Movements Card --}}
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="section-title">ความเคลื่อนไหวล่าสุด</h3>
                    <p class="section-subtitle">ประวัติการรับเข้าและตัดจ่ายล่าสุด</p>
                </div>
                <a href="{{ route('reports.movements') }}" class="btn-ghost">ดูประวัติทั้งหมด</a>
            </div>
            <div class="divide-y divide-slate-100 px-6">
                @forelse($recentTransactions as $transaction)
                @php($isIn = (float) $transaction->quantity_in > 0)
                <div class="flex items-center gap-3.5 py-3.5">
                    <div class="size-9 rounded-xl flex items-center justify-center shrink-0 font-bold text-xs {{ $isIn ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-rose-100 text-rose-700 border border-rose-200' }}">
                        {{ $isIn ? '↓' : '↑' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <strong class="block truncate text-xs font-bold text-slate-800">{{ $transaction->product->code }} — {{ $transaction->product->name }}</strong>
                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transaction->document->document_no }} · {{ $transaction->occurred_at->format('d/m/Y H:i') }} น.</span>
                    </div>
                    <strong class="text-xs font-bold {{ $isIn ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $isIn ? '+' : '-' }}{{ \App\Support\Quantity::format($isIn ? $transaction->quantity_in : $transaction->quantity_out) }}
                    </strong>
                </div>
                @empty
                <div class="empty-state">ยังไม่มีรายการเคลื่อนไหว</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Low Stock Items Table + Recent Requisitions --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Low Stock Table --}}
        <div class="table-shell">
            <div class="panel-header">
                <div>
                    <h3 class="section-title">สินค้าใกล้หมด / ต่ำกว่าเกณฑ์</h3>
                    <p class="section-subtitle">สินค้าที่ควรรีบสั่งซื้อหรือเติมสต็อก</p>
                </div>
                <a href="{{ route('reports.balances') }}" class="btn-ghost">ดูสต็อกคงเหลือ</a>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>รหัส / ชื่อสินค้า</th>
                            <th>ประเภท</th>
                            <th>คลังสินค้า</th>
                            <th class="text-right">คงเหลือ</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStocks as $balance)
                        <tr>
                            <td>
                                <strong class="block text-xs font-bold text-slate-900">{{ $balance->product->code }}</strong>
                                <span class="text-[10px] text-slate-400">{{ $balance->product->name }}</span>
                            </td>
                            <td><span class="badge-slate">{{ $balance->product->product_type->value }}</span></td>
                            <td><span class="text-xs text-slate-600">{{ $balance->warehouse->name }}</span></td>
                            <td class="text-right font-bold text-slate-900">
                                {{ \App\Support\Quantity::format($balance->quantity) }} <span class="font-normal text-[10px] text-slate-500">{{ $balance->product->unit->name }}</span>
                            </td>
                            <td><span class="badge-amber">ใกล้หมด</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">ไม่มีสินค้าที่ต่ำกว่าเกณฑ์ขั้นต่ำ</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Requisitions --}}
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="section-title">ใบเบิกล่าสุด</h3>
                    <p class="section-subtitle">สถานะคำขอเบิกพัสดุและสั่งผลิต</p>
                </div>
                <a href="{{ route('requisitions.index') }}" class="btn-ghost">ดูใบเบิกทั้งหมด</a>
            </div>
            <div class="divide-y divide-slate-100 px-6">
                @forelse($recentRequests as $request)
                <a href="{{ route('requisitions.show', $request) }}" class="flex items-center gap-3.5 py-3.5 group">
                    <div class="size-9 rounded-xl bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-600 shrink-0 border border-slate-200">
                        REQ
                    </div>
                    <div class="min-w-0 flex-1">
                        <strong class="block truncate text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">{{ $request->request_no }}</strong>
                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ method_exists($request->request_type, 'label') ? $request->request_type->label() : $request->request_type }} · {{ $request->requester?->name ?? '—' }}</span>
                    </div>
                    <span class="{{ method_exists($request->status, 'badgeClass') ? $request->status->badgeClass() : 'badge-slate' }}">
                        {{ method_exists($request->status, 'label') ? $request->status->label() : $request->status }}
                    </span>
                </a>
                @empty
                <div class="empty-state">ยังไม่มีรายการใบเบิก</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
