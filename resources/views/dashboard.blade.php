@extends('layouts.app')

@section('title', 'หน้าหลัก — WIP Stock')
@section('header', 'หน้าหลัก')

@section('content')
@php
    $typeTotal = max(1, $partCount + $supplyCount + $wipCount + $fgCount);
    $typeStats = [
        ['PART', 'อะไหล่ผลิต', $partCount, 'bg-blue-500', 'text-blue-700', 'bg-blue-50'],
        ['SUPPLY', 'วัสดุสิ้นเปลือง', $supplyCount, 'bg-cyan-500', 'text-cyan-700', 'bg-cyan-50'],
        ['WIP', 'งานระหว่างประกอบ', $wipCount, 'bg-violet-500', 'text-violet-700', 'bg-violet-50'],
        ['FG', 'สินค้าพร้อมขาย', $fgCount, 'bg-emerald-500', 'text-emerald-700', 'bg-emerald-50'],
    ];
@endphp

<div class="space-y-5">
    <div class="page-head">
        <div>
            <span class="page-kicker">ภาพรวมระบบ</span>
            <h2 class="page-title">สวัสดีคุณ {{ auth()->user()->name }}</h2>
            <p class="page-subtitle">สรุปสต็อกและรายการที่ต้องดำเนินการ ณ วันที่ {{ now()->format('d/m/Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.balances') }}" class="btn-secondary">ดูสต็อกคงเหลือ</a>
            @if(auth()->user()->canOperateStock())
            <a href="{{ route('operations.create', 'supplier-receive') }}" class="btn-primary">+ รับสินค้าเข้า</a>
            @endif
        </div>
    </div>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="metric-card">
            <div class="flex items-start justify-between"><div><span class="text-xs text-slate-500">มูลค่าสต็อกคงเหลือ</span><strong class="mt-2 block text-2xl font-semibold text-slate-900">฿{{ number_format($stockValue, 2) }}</strong></div><span class="grid size-10 place-items-center rounded-lg bg-emerald-50 text-emerald-600"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 3v18m5-14H9.5a3 3 0 0 0 0 6h5a3 3 0 0 1 0 6H7"/></svg></span></div>
            <p class="mt-3 text-[11px] text-emerald-600">{{ number_format($stockQuantity, 2) }} หน่วยในทุกคลัง</p>
        </div>
        <div class="metric-card">
            <div class="flex items-start justify-between"><div><span class="text-xs text-slate-500">จำนวนรายการสินค้า</span><strong class="mt-2 block text-2xl font-semibold text-slate-900">{{ number_format($productCount) }} <small class="text-sm font-normal text-slate-400">รายการ</small></strong></div><span class="grid size-10 place-items-center rounded-lg bg-blue-50 text-blue-600"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m21 8-9-5-9 5 9 5 9-5ZM3 8v8l9 5 9-5V8"/></svg></span></div>
            <p class="mt-3 text-[11px] text-blue-600">แบ่งเป็น PART · SUPPLY · WIP · FG</p>
        </div>
        <div class="metric-card">
            <div class="flex items-start justify-between"><div><span class="text-xs text-slate-500">สินค้าใกล้หมด</span><strong class="mt-2 block text-2xl font-semibold text-slate-900">{{ number_format($lowStockCount) }} <small class="text-sm font-normal text-slate-400">รายการ</small></strong></div><span class="grid size-10 place-items-center rounded-lg bg-amber-50 text-amber-600"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10 3 2 20h20L14 3h-4Z"/></svg></span></div>
            <a href="{{ route('reports.balances') }}" class="mt-3 inline-block text-[11px] font-medium text-amber-600">ตรวจสอบรายการ →</a>
        </div>
        <div class="metric-card">
            <div class="flex items-start justify-between"><div><span class="text-xs text-slate-500">ยอดขายเดือนนี้</span><strong class="mt-2 block text-2xl font-semibold text-slate-900">฿{{ number_format($monthSales, 2) }}</strong></div><span class="grid size-10 place-items-center rounded-lg bg-violet-50 text-violet-600"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M4 19V5m0 14h16M7 15l4-4 3 2 5-6"/></svg></span></div>
            <p class="mt-3 text-[11px] {{ $pendingCount ? 'text-rose-600' : 'text-emerald-600' }}">{{ $pendingCount ? "มี {$pendingCount} ใบเบิกรออนุมัติ" : 'ไม่มีใบเบิกรออนุมัติ' }}</p>
        </div>
    </section>

    @if(auth()->user()->canOperateStock())
    <section class="panel">
        <div class="panel-header"><div><h3 class="section-title">ทำรายการด่วน</h3><p class="section-subtitle">ทุกปุ่มเชื่อมไปยัง flow หลักเพียงจุดเดียว</p></div></div>
        <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach([
                [route('operations.create', 'supplier-receive'), 'รับเข้า', 'Supplier → Stock', 'bg-emerald-50 text-emerald-600', 'M12 3v12m0 0 4-4m-4 4-4-4M4 19h16'],
                [route('requisitions.withdraw'), 'เบิก-จ่าย', 'ขออนุมัติการเบิก', 'bg-blue-50 text-blue-600', 'M4 12h13m0 0-4-4m4 4-4 4M7 5H4v14h3'],
                [route('requisitions.production'), 'ผลิต', 'PART → WIP → FG', 'bg-violet-50 text-violet-600', 'M4 19h16M6 19V9l4 3V8l4 3V5h4v14'],
                [route('operations.create', 'sale'), 'ขาย', 'FG + Option', 'bg-amber-50 text-amber-600', 'M5 7h14l-1 13H6L5 7Zm3 0a4 4 0 0 1 8 0'],
                [route('operations.create', 'claim'), 'เคลม', 'รับคืนจากลูกค้า', 'bg-cyan-50 text-cyan-600', 'M4 7v5h5M20 17v-5h-5M6 16a7 7 0 0 0 11 1m1-9A7 7 0 0 0 7 7'],
                [route('reports.cost-profit'), 'รายงาน', 'ต้นทุนและกำไร', 'bg-slate-100 text-slate-600', 'm4 17 5-5 4 3 7-8M16 7h4v4'],
            ] as [$url, $title, $desc, $color, $path])
            <a href="{{ $url }}" class="quick-action group"><span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $color }}"><svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $path }}"/></svg></span><span><strong class="block text-xs text-slate-800">{{ $title }}</strong><small class="mt-0.5 block text-[10px] text-slate-400">{{ $desc }}</small></span></a>
            @endforeach
        </div>
    </section>
    @endif

    <section class="grid gap-4 xl:grid-cols-[1.05fr_.95fr]">
        <div class="panel">
            <div class="panel-header"><div><h3 class="section-title">สัดส่วนรายการสินค้า</h3><p class="section-subtitle">แยกตามประเภทที่เปิดใช้งาน</p></div><a href="{{ route('products.index') }}" class="btn-ghost">ดูสินค้า</a></div>
            <div class="panel-body space-y-4">
                @foreach($typeStats as [$type, $label, $count, $bar, $text, $soft])
                <div>
                    <div class="mb-1.5 flex items-center justify-between text-xs"><span class="font-medium text-slate-600"><span class="mr-2 inline-block size-2 rounded-full {{ $bar }}"></span>{{ $type }} · {{ $label }}</span><strong class="{{ $text }}">{{ $count }}</strong></div>
                    <div class="h-2 overflow-hidden rounded-full {{ $soft }}"><div class="h-full rounded-full {{ $bar }}" style="width: {{ max(3, ($count / $typeTotal) * 100) }}%"></div></div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><div><h3 class="section-title">ความเคลื่อนไหวล่าสุด</h3><p class="section-subtitle">รายการรับเข้าและจ่ายออกจากทุกเมนู</p></div><a href="{{ route('reports.movements') }}" class="btn-ghost">ดูทั้งหมด</a></div>
            <div class="divide-y divide-slate-100 px-5">
                @forelse($recentTransactions as $transaction)
                @php($isIn = (float) $transaction->quantity_in > 0)
                <div class="flex items-center gap-3 py-3">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $isIn ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">{{ $isIn ? '↓' : '↑' }}</span>
                    <div class="min-w-0 flex-1"><strong class="block truncate text-xs text-slate-700">{{ $transaction->product->code }} · {{ $transaction->product->name }}</strong><span class="text-[10px] text-slate-400">{{ $transaction->document->document_no }} · {{ $transaction->occurred_at->format('d/m/Y H:i') }}</span></div>
                    <strong class="text-xs {{ $isIn ? 'text-emerald-600' : 'text-rose-600' }}">{{ $isIn ? '+' : '-' }}{{ \App\Support\Quantity::format($isIn ? $transaction->quantity_in : $transaction->quantity_out) }}</strong>
                </div>
                @empty
                <div class="empty-state">ยังไม่มีรายการเคลื่อนไหว</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[1.15fr_.85fr]">
        <div class="table-shell">
            <div class="panel-header"><div><h3 class="section-title">สินค้าใกล้หมด</h3><p class="section-subtitle">ยอดคงเหลือต่ำกว่าหรือเท่ากับจุดแจ้งเตือน</p></div><a href="{{ route('reports.balances') }}" class="btn-ghost">ดูทั้งหมด</a></div>
            <div class="table-wrap"><table class="data-table"><thead><tr><th>สินค้า</th><th>ประเภท</th><th>คลัง</th><th class="text-right">คงเหลือ</th><th>สถานะ</th></tr></thead><tbody>
                @forelse($lowStocks as $balance)
                <tr><td><strong class="block text-slate-800">{{ $balance->product->code }}</strong><span class="text-[10px] text-slate-400">{{ $balance->product->name }}</span></td><td><span class="badge-slate">{{ $balance->product->product_type->value }}</span></td><td>{{ $balance->warehouse->name }}</td><td class="text-right font-semibold text-slate-800">{{ \App\Support\Quantity::format($balance->quantity) }} {{ $balance->product->unit->name }}</td><td><span class="badge-amber">ใกล้หมด</span></td></tr>
                @empty
                <tr><td colspan="5" class="empty-state">ไม่มีสินค้าใกล้หมด</td></tr>
                @endforelse
            </tbody></table></div>
        </div>

        <div class="panel">
            <div class="panel-header"><div><h3 class="section-title">ใบเบิกล่าสุด</h3><p class="section-subtitle">สถานะคำขอจากพนักงาน</p></div><a href="{{ route('requisitions.index') }}" class="btn-ghost">ดูทั้งหมด</a></div>
            <div class="divide-y divide-slate-100 px-5">
                @forelse($recentRequests as $request)
                <a href="{{ route('requisitions.show', $request) }}" class="flex items-center gap-3 py-3">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-[10px] font-bold text-slate-500">REQ</span>
                    <div class="min-w-0 flex-1"><strong class="block truncate text-xs text-slate-700">{{ $request->request_no }}</strong><span class="text-[10px] text-slate-400">{{ $request->request_type->label() }} · {{ $request->requester->name }}</span></div>
                    <span class="{{ $request->status->badgeClass() }}">{{ $request->status->label() }}</span>
                </a>
                @empty
                <div class="empty-state">ยังไม่มีใบเบิก</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
