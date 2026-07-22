@extends('layouts.app')
@section('title', $requisition->request_no)
@section('header', 'รายละเอียดใบเบิก')

@section('content')
@php
    $isPending = $requisition->status->value === 'PENDING';
    $isApproved = $requisition->status->value === 'APPROVED';
    $isRejected = $requisition->status->value === 'REJECTED';
    $needsStaffSignature = ! $requisition->requester->isAdmin();
    $hasSigned = (bool) $requisition->requester_signed_at;
    $pdfReady = $requisition->isReadyForPdf();
    $canSign = $isApproved && ! $hasSigned && auth()->id() === $requisition->requested_by && $needsStaffSignature;
    $steps = [
        ['สร้างคำขอ', true],
        ['Admin อนุมัติ', $isApproved],
        ['พนักงานลงนาม', $hasSigned || ! $needsStaffSignature],
        ['เอกสาร PDF', $pdfReady],
    ];
@endphp

<div class="space-y-5">
    <div class="page-head">
        <div><div class="flex items-center gap-2"><span class="page-kicker">{{ $requisition->request_type->label() }}</span><span class="{{ $requisition->status->badgeClass() }}">{{ $requisition->status->label() }}</span></div><h2 class="page-title mt-2 font-mono">{{ $requisition->request_no }}</h2><p class="page-subtitle">{{ $requisition->requester->name }} · {{ $requisition->requested_at->format('d/m/Y H:i') }} · {{ $requisition->warehouse->name }}</p></div>
        <div class="flex gap-2"><a href="{{ route('requisitions.index') }}" class="btn-secondary">กลับรายการ</a>@if($pdfReady)<a href="{{ route('requisitions.pdf', $requisition) }}" class="btn-primary">ดาวน์โหลด PDF</a>@endif</div>
    </div>

    @if($isRejected)
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><strong class="block">ไม่อนุมัติรายการนี้</strong><span>{{ $requisition->rejection_reason }}</span>@if($requisition->rejecter)<small class="mt-1 block text-rose-600">{{ $requisition->rejecter->name }} · {{ $requisition->rejected_at->format('d/m/Y H:i') }}</small>@endif</div>
    @else
    <section class="panel p-4">
        <div class="grid gap-2 sm:grid-cols-4">
            @foreach($steps as $index => [$label, $done])
            @php $active = !$done && ($index === 1 ? $isPending : ($index === 2 ? $isApproved && !$hasSigned : false)); @endphp
            <div class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $done ? 'bg-emerald-50' : ($active ? 'bg-blue-50' : 'bg-slate-50') }}"><span class="grid size-7 shrink-0 place-items-center rounded-full text-[10px] font-bold {{ $done ? 'bg-emerald-500 text-white' : ($active ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500') }}">{{ $done ? '✓' : $index + 1 }}</span><div><strong class="block text-[11px] {{ $done ? 'text-emerald-700' : ($active ? 'text-blue-700' : 'text-slate-500') }}">{{ $label }}</strong><small class="block text-[9px] text-slate-400">{{ $done ? 'เสร็จแล้ว' : ($active ? 'กำลังดำเนินการ' : 'ขั้นตอนถัดไป') }}</small></div></div>
            @endforeach
        </div>
    </section>
    @endif

    <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-4">
            @if($requisition->targetProduct)
            <section class="panel p-4"><div class="flex items-center gap-3"><x-product-image :product="$requisition->targetProduct" size="lg" /><div><span class="text-[10px] font-semibold uppercase tracking-wide text-violet-600">ผลผลิตเพิ่มเข้าสต็อก</span><h3 class="mt-1 text-sm font-semibold text-slate-900">{{ $requisition->targetProduct->code }} — {{ $requisition->targetProduct->name }}</h3><p class="mt-1 text-xs text-slate-500">จำนวน <strong class="text-slate-800">{{ \App\Support\Quantity::format($requisition->target_quantity) }} {{ $requisition->targetProduct->unit->name }}</strong></p></div></div></section>
            @endif

            <section class="table-shell">
                <div class="panel-header"><div><h3 class="section-title">{{ $requisition->request_type->isBuild() ? 'ส่วนประกอบที่ใช้ผลิต' : 'รายการที่ขอเบิก' }}</h3><p class="section-subtitle">{{ $requisition->items->count() }} รายการ</p></div></div>
                <div class="table-wrap"><table class="data-table"><thead><tr><th>สินค้า</th><th>ประเภท</th><th class="text-right">จำนวน</th><th>หมายเหตุ</th></tr></thead><tbody>@foreach($requisition->items as $item)<tr><td><div class="flex items-center gap-3"><x-product-image :product="$item->product" size="sm" /><div><strong class="block text-xs text-slate-800">{{ $item->product->code }}</strong><span class="text-[10px] text-slate-400">{{ $item->product->name }}</span></div></div></td><td><span class="badge-slate">{{ $item->product->product_type->value }}</span></td><td class="text-right"><strong>{{ \App\Support\Quantity::format($item->quantity) }}</strong> {{ $item->product->unit->name }}</td><td>{{ $item->note ?: '—' }}</td></tr>@endforeach</tbody></table></div>
            </section>

            <section class="panel">
                <div class="panel-header"><h3 class="section-title">ข้อมูลคำขอ</h3></div>
                <dl class="grid gap-x-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach([['ผู้ขอเบิก', $requisition->requester->name], ['คลังสินค้า', $requisition->warehouse->name], ['แผนก', $requisition->department_name ?: '—'], ['วัตถุประสงค์', $requisition->purpose], ['หมายเหตุ', $requisition->note ?: '—'], ['ผู้อนุมัติ', $requisition->approver?->name ?? '—']] as [$label, $value])
                    <div class="border-b border-slate-100 px-5 py-3"><dt class="text-[10px] text-slate-400">{{ $label }}</dt><dd class="mt-1 text-xs font-semibold text-slate-700">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </section>
        </div>

        <aside class="space-y-4">
            @if($isPending && auth()->user()->isAdmin())
            <section class="panel border-emerald-200"><div class="panel-header"><div><h3 class="section-title text-emerald-800">ตรวจและอนุมัติ</h3><p class="section-subtitle">สต็อกจะถูกปรับหลังยืนยัน</p></div></div><div class="panel-body space-y-3"><form method="post" action="{{ route('requisitions.approve', $requisition) }}">@csrf<button class="btn-success w-full">อนุมัติและปรับสต็อก</button></form><form method="post" action="{{ route('requisitions.reject', $requisition) }}">@csrf<label><span class="label">เหตุผลที่ไม่อนุมัติ</span><textarea name="reason" class="input" rows="2" required></textarea></label><button class="btn-danger mt-2 w-full">ไม่อนุมัติ</button></form></div></section>
            @elseif($canSign)
            <section class="panel border-blue-200"><div class="panel-header"><div><h3 class="section-title text-blue-800">ลงนามรับสินค้า</h3><p class="section-subtitle">ยืนยันด้วย PIN ลายเซ็น 4 หลัก</p></div></div><div class="panel-body">@if(auth()->user()->signature)<img src="{{ route('signature.show', auth()->user()->signature) }}" class="mb-3 h-20 max-w-full object-contain" alt="ลายเซ็น"><form method="post" action="{{ route('requisitions.sign', $requisition) }}">@csrf<label><span class="label">PIN ลายเซ็น</span><input class="input text-center text-lg tracking-[.3em]" type="password" name="pin" inputmode="numeric" maxlength="4" required></label><button class="btn-primary mt-3 w-full">ลงนามและออก PDF</button></form>@else<p class="text-xs text-amber-700">ยังไม่ได้ตั้งค่าลายเซ็น</p><a href="{{ route('signature.edit') }}" class="btn-primary mt-3 w-full">ตั้งค่าลายเซ็น</a>@endif</div></section>
            @elseif($pdfReady)
            <section class="panel border-emerald-200 p-5 text-center"><span class="mx-auto grid size-10 place-items-center rounded-full bg-emerald-100 text-emerald-700">✓</span><h3 class="mt-3 text-sm font-semibold text-slate-900">เอกสารพร้อมใช้งาน</h3><p class="mt-1 text-xs text-slate-500">ดาวน์โหลดหรือเปิดตัวอย่างก่อนพิมพ์</p><a href="{{ route('requisitions.pdf', $requisition) }}" class="btn-primary mt-4 w-full">ดาวน์โหลด PDF</a><a target="_blank" href="{{ route('requisitions.print', $requisition) }}" class="btn-secondary mt-2 w-full">ดูตัวอย่างเอกสาร</a></section>
            @elseif($isApproved)
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-xs text-blue-800"><strong class="block">รอพนักงานลงนาม</strong>{{ $requisition->requester->name }} ต้องลงนามก่อนดาวน์โหลด PDF</div>
            @elseif($isPending)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800"><strong class="block">รอ Admin อนุมัติ</strong>ระบบยังไม่ปรับสต็อกจนกว่าจะได้รับอนุมัติ</div>
            @endif

            @if($hasSigned)
            <section class="panel p-4"><span class="text-[10px] text-slate-400">ลายเซ็นผู้ขอเบิก</span><img src="{{ route('requisitions.signature', $requisition) }}" class="mt-2 h-20 max-w-full object-contain" alt="ลายเซ็น"><p class="mt-2 text-[10px] text-slate-400">ลงนาม {{ $requisition->requester_signed_at->format('d/m/Y H:i') }}</p></section>
            @endif
        </aside>
    </div>
</div>
@endsection
