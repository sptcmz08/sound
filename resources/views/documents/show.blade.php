@extends('layouts.app')
@section('title', $document->document_no)
@section('header', 'รายละเอียดเอกสาร')

@section('content')
@php
    $showCost = $document->items->contains(fn ($item) => (float) $item->unit_cost > 0);
    $showPrice = $document->items->contains(fn ($item) => (float) $item->unit_price > 0);
    $showsTransactions = $document->items->isEmpty() && $document->transactions->isNotEmpty();
    $lineCount = $showsTransactions ? $document->transactions->count() : $document->items->count();
    $quantityTotal = $showsTransactions
        ? $document->transactions->sum(fn ($transaction) => (float) $transaction->quantity_in + (float) $transaction->quantity_out)
        : $document->items->sum(fn ($item) => (float) $item->quantity);
    $valueTotal = $document->items->sum(fn ($item) => (float) $item->quantity * (float) ($showPrice ? $item->unit_price : $item->unit_cost));
    $nextOperation = match($document->document_type->value) {
        'SUPPLIER_IN' => 'supplier-receive',
        'SALE_OUT' => 'sale',
        'CLAIM_IN' => 'claim',
        'WASTE_OUT' => 'waste',
        default => null,
    };
@endphp

<div class="space-y-5">
    <div class="page-head">
        <div>
            <div class="flex flex-wrap items-center gap-2"><span class="page-kicker">{{ $document->document_type->label() }}</span><span class="{{ $document->status->value === 'POSTED' ? 'badge-green' : ($document->status->value === 'CANCELLED' ? 'badge-slate' : 'badge-amber') }}">{{ $document->status->value }}</span></div>
            <h2 class="page-title mt-2 font-mono">{{ $document->document_no }}</h2>
            <p class="page-subtitle">บันทึกวันที่ {{ $document->document_date->format('d/m/Y') }} · {{ $document->warehouse->name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dashboard') }}" class="btn-secondary">หน้าหลัก</a>
            @if($nextOperation)<a href="{{ route('operations.create', $nextOperation) }}" class="btn-primary">+ ทำรายการใหม่</a>@endif
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="metric-card"><span class="text-xs text-slate-500">จำนวนรายการ</span><strong class="mt-2 block text-xl text-slate-900">{{ $lineCount }} รายการ</strong></div>
        <div class="metric-card"><span class="text-xs text-slate-500">จำนวนรวม</span><strong class="mt-2 block text-xl text-slate-900">{{ \App\Support\Quantity::format($quantityTotal) }} หน่วย</strong></div>
        <div class="metric-card"><span class="text-xs text-slate-500">{{ $showPrice ? 'ยอดขายรวม' : ($showCost ? 'มูลค่ารวม' : 'สถานะสต็อก') }}</span><strong class="mt-2 block text-xl {{ $showPrice ? 'text-blue-700' : 'text-slate-900' }}">{{ ($showPrice || $showCost) ? '฿'.number_format($valueTotal, 2) : 'บันทึกแล้ว' }}</strong></div>
    </div>

    <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
        <section class="table-shell min-w-0">
            <div class="panel-header"><div><h3 class="section-title">รายการสินค้า</h3><p class="section-subtitle">สินค้าหลักและ Option ที่เกี่ยวข้องกับเอกสารนี้</p></div></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>สินค้า</th><th>ประเภท</th><th class="text-right">จำนวน</th>@if($showCost)<th class="text-right">ต้นทุน/หน่วย</th>@endif @if($showPrice)<th class="text-right">ราคาขาย/หน่วย</th><th class="text-right">รวม</th>@endif</tr></thead>
                    <tbody>
                    @if($showsTransactions)
                    @foreach($document->transactions as $transaction)
                    @php($transactionQuantity = (float) $transaction->quantity_in > 0 ? $transaction->quantity_in : $transaction->quantity_out)
                    <tr><td><div class="flex items-center gap-3"><x-product-image :product="$transaction->product" size="sm" /><div><strong class="block text-xs text-slate-800">{{ $transaction->product->code }} — {{ $transaction->product->name }}</strong><span class="text-[10px] text-slate-400">คืนจาก {{ $document->reference_no }}</span></div></div></td><td><span class="badge-slate">{{ $transaction->product->product_type->value }}</span></td><td class="text-right"><strong>{{ \App\Support\Quantity::format($transactionQuantity) }}</strong> {{ $transaction->product->unit->name }}</td></tr>
                    @endforeach
                    @else
                    @foreach($document->items as $item)
                    <tr>
                        <td><div class="flex items-start gap-3"><x-product-image :product="$item->product" size="sm" /><div><strong class="block text-xs text-slate-800">{{ $item->product->code }} — {{ $item->product->name }}</strong>@if($item->options->isNotEmpty())<div class="mt-2 space-y-1">@foreach($item->options as $option)<span class="block rounded-md bg-violet-50 px-2 py-1 text-[10px] text-violet-700">{{ $option->optionItem->group->name }}: {{ $option->optionItem->optionProduct->name }} × {{ \App\Support\Quantity::format($option->quantity) }}</span>@endforeach</div>@endif</div></div></td>
                        <td><span class="badge-slate">{{ $item->product->product_type->value }}</span></td>
                        <td class="text-right"><strong>{{ \App\Support\Quantity::format($item->quantity) }}</strong> {{ $item->product->unit->name }}</td>
                        @if($showCost)<td class="text-right">฿{{ number_format((float) $item->unit_cost, 2) }}</td>@endif
                        @if($showPrice)<td class="text-right">฿{{ number_format((float) $item->unit_price, 2) }}</td><td class="text-right font-semibold text-slate-900">฿{{ number_format((float) $item->quantity * (float) $item->unit_price, 2) }}</td>@endif
                    </tr>
                    @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-4">
            <section class="panel">
                <div class="panel-header"><h3 class="section-title">ข้อมูลเอกสาร</h3></div>
                <dl class="divide-y divide-slate-100 px-5 text-xs">
                    @foreach([
                        ['ประเภท', $document->document_type->label()],
                        ['คลัง', $document->warehouse->name],
                        ['ผู้ติดต่อ', $document->contact_name ?: '—'],
                        ['เลขอ้างอิง', $document->reference_no ?: '—'],
                        ['ผู้บันทึก', $document->poster?->name ?? $document->creator->name],
                        ['เวลาบันทึก', $document->posted_at?->format('d/m/Y H:i') ?? '—'],
                    ] as [$label, $value])
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-400">{{ $label }}</dt><dd class="text-right font-semibold text-slate-700">{{ $value }}</dd></div>
                    @endforeach
                </dl>
                @if($document->note)<div class="border-t border-slate-100 bg-slate-50 px-5 py-4 text-xs leading-5 text-slate-600"><strong class="mb-1 block text-slate-700">หมายเหตุ</strong>{{ $document->note }}</div>@endif
            </section>

            @if($document->reversal)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">เอกสารนี้ถูกย้อนรายการแล้ว <a href="{{ route('documents.show', $document->reversal) }}" class="font-semibold underline">ดู {{ $document->reversal->document_no }}</a></div>
            @endif

            @if(auth()->user()->isAdmin() && $document->status->value === 'POSTED' && $document->document_type->value !== 'REVERSAL')
            <form method="post" action="{{ route('documents.cancel', $document) }}" class="panel p-4" onsubmit="return confirm('ยืนยันยกเลิกเอกสาร? ระบบจะสร้างรายการย้อนกลับโดยไม่ลบประวัติเดิม')">@csrf<label><span class="label">เหตุผลการยกเลิก</span><textarea class="input" name="reason" rows="2" required></textarea></label><button class="btn-danger mt-3 w-full">ยกเลิกและย้อนสต็อก</button></form>
            @endif
        </aside>
    </div>
</div>
@endsection
