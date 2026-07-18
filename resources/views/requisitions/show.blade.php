@extends('layouts.app')

@section('title', $requisition->request_no)
@section('header', 'รายละเอียดใบเบิก')

@section('content')
<div class="mb-7 flex flex-wrap items-start justify-between gap-4">
    <div>
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="page-title">{{ $requisition->request_no }}</h2>
            <span class="{{ $requisition->status->badgeClass() }}">{{ $requisition->status->label() }}</span>
        </div>
        <p class="page-subtitle">{{ $requisition->request_type->label() }} · ผู้ขอเบิก {{ $requisition->requester->name }} · {{ $requisition->requested_at->format('d/m/Y H:i') }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('requisitions.index') }}" class="btn-secondary">กลับ</a>
        @if($requisition->status->value === 'APPROVED')
            <a href="{{ route('requisitions.pdf', $requisition) }}" class="btn-primary">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2m-12-5h12v8H6v-8Z"/></svg>
                ดาวน์โหลด PDF
            </a>
        @endif
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
    <div class="space-y-6">
        <section class="panel">
            <div class="panel-header"><h3 class="text-xl font-bold text-slate-950">ข้อมูลใบเบิก</h3></div>
            <div class="panel-body grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div><span class="text-sm text-slate-500">เลขที่ใบเบิก</span><strong class="block text-lg text-slate-950">{{ $requisition->request_no }}</strong></div>
                <div><span class="text-sm text-slate-500">วันที่เบิก</span><strong class="block text-lg text-slate-950">{{ $requisition->requested_at->format('d/m/Y H:i') }}</strong></div>
                <div><span class="text-sm text-slate-500">ชื่อพนักงานผู้เบิก</span><strong class="block text-lg text-slate-950">{{ $requisition->requester->name }}</strong></div>
                <div><span class="text-sm text-slate-500">ประเภท</span><strong class="block text-lg text-slate-950">{{ $requisition->request_type->label() }}</strong></div>
                <div><span class="text-sm text-slate-500">คลังสินค้า</span><strong class="block text-lg text-slate-950">{{ $requisition->warehouse->name }}</strong></div>
                <div><span class="text-sm text-slate-500">วัตถุประสงค์</span><strong class="block text-lg text-slate-950">{{ $requisition->purpose }}</strong></div>
            </div>
        </section>

        @if(!$requisition->requester->isAdmin())
        <section class="panel {{ $requisition->requester_signed_at ? 'border-emerald-200' : 'border-amber-200' }}">
            <div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">ลายเซ็นผู้ขอเบิก</h3><p class="text-sm text-slate-500">ยืนยันตัวตนและลงนามออนไลน์ก่อนส่งให้แอดมินอนุมัติ</p></div><span class="{{ $requisition->requester_signed_at ? 'badge-green' : 'badge-amber' }}">{{ $requisition->requester_signed_at ? 'ลงนามแล้ว' : 'รอลงนาม' }}</span></div>
            <div class="panel-body">
                @if($requisition->requester_signed_at)
                    <img src="{{route('requisitions.signature',$requisition)}}" class="h-24 max-w-full object-contain" alt="ลายเซ็นผู้ขอเบิก"><p class="mt-2 text-sm text-slate-500">ลงนามโดย {{$requisition->requester->name}} · {{$requisition->requester_signed_at->format('d/m/Y H:i')}}</p>
                @elseif(auth()->id()===$requisition->requested_by)
                    @if(auth()->user()->signature)
                    <div class="grid items-end gap-4 md:grid-cols-[1fr_220px]"><div><img src="{{route('signature.show',auth()->user()->signature)}}" class="h-24 max-w-full object-contain" alt="ลายเซ็นที่บันทึกไว้"><a href="{{route('signature.edit')}}" class="text-sm font-semibold text-blue-600">เปลี่ยนลายเซ็น</a></div><form method="post" action="{{route('requisitions.sign',$requisition)}}">@csrf<label><span class="label">PIN ลายเซ็น 4 หลัก</span><input class="input text-center text-xl tracking-[.35em]" type="password" name="pin" inputmode="numeric" maxlength="4" required></label><button class="btn-success mt-3 w-full">ลงนามใบเบิก</button></form></div>
                    @else
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-amber-50 p-4"><span>กรุณาบันทึกลายเซ็นและ PIN ก่อนลงนามเอกสาร</span><a href="{{route('signature.edit')}}" class="btn-primary">ตั้งค่าลายเซ็น</a></div>
                    @endif
                @else
                    <p class="text-amber-700">กำลังรอ {{$requisition->requester->name}} ลงนามออนไลน์</p>
                @endif
            </div>
        </section>
        @endif

        @if($requisition->targetProduct)
        <section class="flex items-center gap-5 rounded-2xl border border-violet-200 bg-violet-50 p-6"><x-product-image :product="$requisition->targetProduct" size="lg" /><div>
            <span class="font-semibold text-violet-700">รายการที่ผลิตเข้าสต็อก</span>
            <h3 class="mt-1 text-2xl font-bold text-slate-950">{{ $requisition->targetProduct->code }} — {{ $requisition->targetProduct->name }}</h3>
            <p class="mt-2 text-lg">จำนวน <strong>{{ \App\Support\Quantity::format($requisition->target_quantity) }} {{ $requisition->targetProduct->unit->name }}</strong></p>
        </div></section>
        @endif

        <section class="table-shell">
            <div class="panel-header">
                <div><h3 class="text-xl font-bold text-slate-950">{{ $requisition->request_type->isBuild() ? 'รายการอะไหล่ที่ใช้ผลิต' : 'รายการที่ขอเบิก' }}</h3><p class="mt-0.5 text-sm text-slate-500">รายการนี้จะแสดงในใบเบิกพัสดุ</p></div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>ลำดับ</th><th>รหัส</th><th>รายการ</th><th class="text-right">จำนวน</th><th>หมายเหตุ</th></tr></thead>
                    <tbody>
                        @foreach($requisition->items as $index => $item)
                        <tr><td>{{ $index + 1 }}</td><td class="font-bold">{{ $item->product->code }}</td><td><div class="flex items-center gap-3"><x-product-image :product="$item->product" size="sm" /><span>{{ $item->product->name }}</span></div></td><td class="text-right text-lg font-bold">{{ \App\Support\Quantity::format($item->quantity) }} {{ $item->product->unit->name }}</td><td>{{ $item->note ?: '—' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        @if($requisition->status->value === 'PENDING' && auth()->user()->isAdmin())
        @if(!$requisition->requester->isAdmin() && !$requisition->requester_signed_at)
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6"><h3 class="text-xl font-bold text-amber-900">รอลายเซ็นผู้ขอเบิก</h3><p class="mt-2 text-amber-800">{{$requisition->requester->name}} ต้องลงนามออนไลน์ก่อน จึงจะอนุมัติและปรับสต็อกได้</p></section>
        @else
        <section class="panel border-emerald-200">
            <div class="panel-header bg-emerald-50"><div><h3 class="text-xl font-bold text-emerald-900">อนุมัติรายการ</h3><p class="mt-0.5 text-sm text-emerald-700">ตรวจรายการก่อนอนุมัติและปรับสต็อก</p></div></div>
            <div class="panel-body">
                <div class="mb-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">ไม่ต้องวาดลายเซ็นในหน้าจอนี้ ชื่อผู้ขอและผู้อนุมัติจะพิมพ์ลงในใบเบิกสำหรับลงนามบนเอกสาร</div>
                <form method="post" action="{{ route('requisitions.approve', $requisition) }}">@csrf<button class="btn-success w-full">✓ อนุมัติและบันทึกสต็อก</button></form>
                <p class="mt-3 text-sm text-amber-700">ระบบจะตัดหรือเพิ่มสต็อกทันทีเมื่ออนุมัติ</p>
            </div>
        </section>
        @endif
        <section class="panel">
            <div class="panel-body">
                <form method="post" action="{{ route('requisitions.reject', $requisition) }}">@csrf<label><span class="label">เหตุผลที่ไม่อนุมัติ</span><textarea name="reason" class="input" rows="3" required></textarea></label><button class="btn-danger mt-4 w-full">ไม่อนุมัติ</button></form>
            </div>
        </section>
        @elseif($requisition->status->value === 'APPROVED')
        <section class="panel border-emerald-200">
            <div class="panel-body text-center">
                <div class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-100 text-2xl text-emerald-700">✓</div>
                <h3 class="mt-3 text-xl font-bold text-slate-950">อนุมัติและบันทึกสต็อกแล้ว</h3>
                <p class="mt-2">ผู้อนุมัติ: {{ $requisition->approver->name }}<br>{{ $requisition->approved_at->format('d/m/Y H:i') }}</p>
                <a href="{{ route('requisitions.pdf', $requisition) }}" class="btn-primary mt-5 w-full">ดาวน์โหลดใบเบิก PDF</a>
                <a target="_blank" href="{{ route('requisitions.print', $requisition) }}" class="btn-secondary mt-3 w-full">ดูตัวอย่างก่อนพิมพ์</a>
            </div>
        </section>
        @else
        <section class="rounded-2xl border border-rose-200 bg-rose-50 p-6"><h3 class="text-xl font-bold text-rose-900">ไม่อนุมัติ</h3><p class="mt-2 text-rose-800">{{ $requisition->rejection_reason }}</p></section>
        @endif
    </aside>
</div>
@endsection
