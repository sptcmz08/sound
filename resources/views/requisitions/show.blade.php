@extends('layouts.app')

@section('title', $requisition->request_no)
@section('header', 'รายละเอียดใบเบิก')

@section('content')
@php
    $isPending = $requisition->status->value === 'PENDING';
    $isApproved = $requisition->status->value === 'APPROVED';
    $isRejected = $requisition->status->value === 'REJECTED';
    $isStaff = !$requisition->requester->isAdmin();
    $hasSigned = (bool) $requisition->requester_signed_at;
    $canSign = $isPending && !$hasSigned && auth()->id() === $requisition->requested_by && $isStaff;

    // Workflow steps
    $step = 1;
    if ($isStaff && $hasSigned) $step = 2;
    if ($isStaff && $isPending && $hasSigned) $step = 2;
    if (!$isStaff && $isPending) $step = 2; // admin-created goes straight to approve
    if ($isApproved) $step = 4;
    if ($isRejected) $step = 0;

    $steps = [
        ['label' => 'สร้างใบเบิก', 'icon' => '📋', 'done' => true],
        ['label' => 'ลงนามออนไลน์', 'icon' => '✍️', 'done' => $hasSigned || !$isStaff || $isApproved],
        ['label' => 'Admin อนุมัติ', 'icon' => '✅', 'done' => $isApproved],
        ['label' => 'ดาวน์โหลด PDF', 'icon' => '📄', 'done' => $isApproved],
    ];
@endphp

{{-- Page Header --}}
<div class="mb-7 flex flex-wrap items-start justify-between gap-4">
    <div>
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="page-title">{{ $requisition->request_no }}</h2>
            <span class="{{ $requisition->status->badgeClass() }}">{{ $requisition->status->label() }}</span>
        </div>
        <p class="page-subtitle">{{ $requisition->request_type->label() }} · ผู้ขอเบิก {{ $requisition->requester->name }} · {{ $requisition->requested_at->format('d/m/Y H:i') }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('requisitions.index') }}" class="btn-secondary">← กลับ</a>
        @if($isApproved)
            <a href="{{ route('requisitions.pdf', $requisition) }}" class="btn-primary">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2m-12-5h12v8H6v-8Z"/></svg>
                ดาวน์โหลด PDF
            </a>
        @endif
    </div>
</div>

{{-- Workflow Stepper --}}
@if(!$isRejected)
<section class="mb-7 rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 to-white p-6 shadow-sm">
    <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500">ขั้นตอนการเบิก</h3>
    <div class="flex items-start justify-between gap-2">
        @foreach($steps as $i => $s)
        @php
            $active = false;
            if ($isStaff) {
                if ($i === 0) $active = $isPending && !$hasSigned;
                if ($i === 1) $active = $isPending && !$hasSigned && auth()->id() === $requisition->requested_by;
                if ($i === 2) $active = $isPending && $hasSigned;
                if ($i === 3) $active = $isApproved;
            } else {
                if ($i === 0) $active = false;
                if ($i === 1) $active = false;
                if ($i === 2) $active = $isPending;
                if ($i === 3) $active = $isApproved;
            }
        @endphp
        <div class="flex flex-1 flex-col items-center text-center">
            <div class="grid size-14 place-items-center rounded-2xl text-2xl transition-all
                {{ $s['done'] ? 'bg-emerald-100 text-emerald-700 ring-2 ring-emerald-300' : ($active ? 'bg-blue-100 text-blue-700 ring-2 ring-blue-400 animate-pulse' : 'bg-slate-100 text-slate-400') }}">
                {{ $s['done'] ? '✓' : $s['icon'] }}
            </div>
            <span class="mt-2 text-sm font-bold {{ $s['done'] ? 'text-emerald-700' : ($active ? 'text-blue-700' : 'text-slate-400') }}">{{ $s['label'] }}</span>
            @if($s['done'])<span class="text-xs text-emerald-600">เสร็จสิ้น</span>@elseif($active)<span class="text-xs text-blue-600">ดำเนินการ</span>@endif
        </div>
        @if(!$loop->last)
        <div class="mt-6 h-1 flex-1 rounded-full {{ $s['done'] ? 'bg-emerald-300' : 'bg-slate-200' }}"></div>
        @endif
        @endforeach
    </div>
</section>
@else
<section class="mb-7 flex items-center gap-4 rounded-2xl border border-rose-200 bg-rose-50 p-6">
    <div class="grid size-14 place-items-center rounded-2xl bg-rose-100 text-2xl text-rose-700">✕</div>
    <div>
        <h3 class="text-xl font-bold text-rose-900">ไม่อนุมัติ</h3>
        <p class="mt-1 text-rose-800">{{ $requisition->rejection_reason }}</p>
        @if($requisition->rejecter)<p class="mt-1 text-sm text-rose-600">โดย {{ $requisition->rejecter->name }} · {{ $requisition->rejected_at->format('d/m/Y H:i') }}</p>@endif
    </div>
</section>
@endif

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
    <div class="space-y-6">
        {{-- ข้อมูลใบเบิก --}}
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

        {{-- ลายเซ็นผู้ขอเบิก (เฉพาะพนักงาน) --}}
        @if($isStaff)
        <section class="panel {{ $hasSigned ? 'border-emerald-200' : 'border-amber-200' }}">
            <div class="panel-header">
                <div>
                    <h3 class="text-xl font-bold text-slate-950">ขั้นตอนที่ 2: ลายเซ็นผู้ขอเบิก</h3>
                    <p class="text-sm text-slate-500">ยืนยันตัวตนและลงนามออนไลน์ก่อนส่งให้แอดมินอนุมัติ</p>
                </div>
                <span class="{{ $hasSigned ? 'badge-green' : 'badge-amber' }}">{{ $hasSigned ? 'ลงนามแล้ว' : 'รอลงนาม' }}</span>
            </div>
            <div class="panel-body">
                @if($hasSigned)
                    <div class="flex items-center gap-4 rounded-xl bg-emerald-50 p-4">
                        <img src="{{route('requisitions.signature',$requisition)}}" class="h-24 max-w-full object-contain" alt="ลายเซ็นผู้ขอเบิก">
                        <div>
                            <p class="font-bold text-emerald-800">ลงนามเรียบร้อยแล้ว</p>
                            <p class="mt-1 text-sm text-slate-500">โดย {{$requisition->requester->name}} · {{$requisition->requester_signed_at->format('d/m/Y H:i')}}</p>
                        </div>
                    </div>
                @elseif($canSign)
                    @if(auth()->user()->signature)
                    <div class="rounded-xl border-2 border-dashed border-blue-300 bg-blue-50/50 p-6">
                        <p class="mb-4 font-bold text-blue-900">กรุณาลงนามเพื่อส่งใบเบิกให้ Admin อนุมัติ</p>
                        <div class="grid items-end gap-4 md:grid-cols-[1fr_220px]">
                            <div>
                                <img src="{{route('signature.show',auth()->user()->signature)}}" class="h-24 max-w-full object-contain" alt="ลายเซ็นที่บันทึกไว้">
                                <a href="{{route('signature.edit')}}" class="text-sm font-semibold text-blue-600">เปลี่ยนลายเซ็น</a>
                            </div>
                            <form method="post" action="{{route('requisitions.sign',$requisition)}}">@csrf
                                <label><span class="label">PIN ลายเซ็น 4 หลัก</span><input class="input text-center text-xl tracking-[.35em]" type="password" name="pin" inputmode="numeric" maxlength="4" required></label>
                                <button class="btn-success mt-3 w-full">✍️ ลงนามใบเบิก</button>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-amber-50 p-4"><span>กรุณาบันทึกลายเซ็นและ PIN ก่อนลงนามเอกสาร</span><a href="{{route('signature.edit')}}" class="btn-primary">ตั้งค่าลายเซ็น</a></div>
                    @endif
                @else
                    <p class="text-amber-700">กำลังรอ {{$requisition->requester->name}} ลงนามออนไลน์</p>
                @endif
            </div>
        </section>
        @endif

        {{-- ผลลัพธ์ที่ผลิตเข้าสต็อก --}}
        @if($requisition->targetProduct)
        <section class="flex items-center gap-5 rounded-2xl border border-violet-200 bg-violet-50 p-6"><x-product-image :product="$requisition->targetProduct" size="lg" /><div>
            <span class="font-semibold text-violet-700">รายการที่ผลิตเข้าสต็อก</span>
            <h3 class="mt-1 text-2xl font-bold text-slate-950">{{ $requisition->targetProduct->code }} — {{ $requisition->targetProduct->name }}</h3>
            <p class="mt-2 text-lg">จำนวน <strong>{{ \App\Support\Quantity::format($requisition->target_quantity) }} {{ $requisition->targetProduct->unit->name }}</strong></p>
        </div></section>
        @endif

        {{-- รายการที่ขอเบิก --}}
        <section class="table-shell">
            <div class="panel-header">
                <div><h3 class="text-xl font-bold text-slate-950">{{ $requisition->request_type->isBuild() ? 'รายการส่วนประกอบที่ใช้ผลิต' : 'รายการที่ขอเบิก' }}</h3><p class="mt-0.5 text-sm text-slate-500">รายการนี้จะแสดงในใบเบิกพัสดุ</p></div>
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

    {{-- Sidebar Actions --}}
    <aside class="space-y-6">
        @if($isPending && auth()->user()->isAdmin())
            @if($isStaff && !$hasSigned)
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                <div class="flex items-center gap-3">
                    <div class="grid size-12 place-items-center rounded-xl bg-amber-100 text-xl">⏳</div>
                    <div>
                        <h3 class="text-xl font-bold text-amber-900">รอลายเซ็นผู้ขอเบิก</h3>
                        <p class="mt-1 text-amber-800">{{$requisition->requester->name}} ต้องลงนามออนไลน์ก่อน จึงจะอนุมัติได้</p>
                    </div>
                </div>
            </section>
            @else
            <section class="panel border-emerald-200">
                <div class="panel-header bg-emerald-50">
                    <div>
                        <h3 class="text-xl font-bold text-emerald-900">ขั้นตอนที่ 3: อนุมัติรายการ</h3>
                        <p class="mt-0.5 text-sm text-emerald-700">ตรวจรายการก่อนอนุมัติและปรับสต็อก</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="mb-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                        เมื่ออนุมัติแล้ว ระบบจะตัดสต็อกทันที และสร้างเอกสาร PDF ให้ปริ้นไปส่งแผนกเบิก
                    </div>
                    <form method="post" action="{{ route('requisitions.approve', $requisition) }}">@csrf<button class="btn-success w-full text-lg">✓ อนุมัติและบันทึกสต็อก</button></form>
                    <p class="mt-3 text-sm text-amber-700">⚠ ระบบจะตัดหรือเพิ่มสต็อกทันทีเมื่ออนุมัติ</p>
                </div>
            </section>
            @endif
            <section class="panel">
                <div class="panel-body">
                    <form method="post" action="{{ route('requisitions.reject', $requisition) }}">@csrf<label><span class="label">เหตุผลที่ไม่อนุมัติ</span><textarea name="reason" class="input" rows="3" required></textarea></label><button class="btn-danger mt-4 w-full">ไม่อนุมัติ</button></form>
                </div>
            </section>
        @elseif($isApproved)
            <section class="panel border-emerald-200">
                <div class="panel-body text-center">
                    <div class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-100 text-3xl text-emerald-700">✓</div>
                    <h3 class="mt-3 text-xl font-bold text-slate-950">อนุมัติและบันทึกสต็อกแล้ว</h3>
                    <p class="mt-2">ผู้อนุมัติ: {{ $requisition->approver->name }}<br>{{ $requisition->approved_at->format('d/m/Y H:i') }}</p>
                </div>
            </section>
            <section class="rounded-2xl border-2 border-blue-300 bg-gradient-to-b from-blue-50 to-white p-6 shadow-lg">
                <div class="text-center">
                    <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-100 text-3xl">📄</div>
                    <h3 class="mt-3 text-xl font-bold text-slate-950">ขั้นตอนที่ 4: เอกสาร PDF</h3>
                    <p class="mt-2 text-sm text-slate-600">ดาวน์โหลดเอกสาร PDF ที่ลงนามแล้ว<br>ปริ้นไปส่งแผนกเบิกเพื่อรับสินค้า</p>
                    <a href="{{ route('requisitions.pdf', $requisition) }}" class="btn-primary mt-5 w-full text-lg">
                        <svg class="mr-2 size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                        ดาวน์โหลดใบเบิก PDF
                    </a>
                    <a target="_blank" href="{{ route('requisitions.print', $requisition) }}" class="btn-secondary mt-3 w-full">ดูตัวอย่างก่อนพิมพ์</a>
                </div>
            </section>
        @endif
    </aside>
</div>
@endsection
