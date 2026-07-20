@php
    $isAdminCreated = $r->requester->isAdmin();
    $canApprove = auth()->user()->isAdmin() && $r->status === \App\Enums\RequisitionStatus::PENDING && ($isAdminCreated || $r->requester_signed_at);

    // Workflow step info
    if ($r->status->value === 'APPROVED') {
        $stepInfo = ['label' => 'เสร็จสิ้น — พร้อมดาวน์โหลด PDF ปริ้นส่งแผนกเบิก', 'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200'];
    } elseif ($r->status->value === 'REJECTED') {
        $stepInfo = ['label' => 'ไม่อนุมัติ — '.$r->rejection_reason, 'class' => 'bg-rose-50 text-rose-800 border-rose-200'];
    } elseif ($isAdminCreated) {
        $stepInfo = ['label' => 'รอ Admin อนุมัติ', 'class' => 'bg-amber-50 text-amber-800 border-amber-200'];
    } elseif ($r->requester_signed_at) {
        $stepInfo = ['label' => 'ลงนามแล้ว — รอ Admin ตรวจสอบและอนุมัติ', 'class' => 'bg-amber-50 text-amber-800 border-amber-200'];
    } else {
        $stepInfo = ['label' => 'รอพนักงานลงนามออนไลน์', 'class' => 'bg-blue-50 text-blue-800 border-blue-200'];
    }
@endphp
<dialog id="process-{{$r->id}}" class="modal-dialog w-full max-w-4xl p-0">
    <div class="overflow-hidden rounded-2xl bg-white">
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5"><div><div class="flex flex-wrap items-center gap-2"><h3 class="text-2xl font-bold text-slate-950">{{$r->request_no}}</h3><span class="{{$r->status->badgeClass()}}">{{$r->status->label()}}</span>@if($isAdminCreated)<span class="badge-blue">สร้างโดย Admin</span>@endif</div><p class="mt-1 text-slate-500">{{$r->request_type->label()}} · {{$r->requested_at->format('d/m/Y H:i')}}</p></div><button type="button" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100" data-close-modal>✕</button></div>
        <div class="max-h-[70vh] overflow-y-auto p-6">
            {{-- Workflow Status Banner --}}
            <div class="mb-5 rounded-xl border p-4 text-sm font-bold {{$stepInfo['class']}}">📋 สถานะ: {{$stepInfo['label']}}</div>

            <div class="mb-6 grid gap-4 rounded-2xl bg-slate-50 p-5 sm:grid-cols-2 lg:grid-cols-4"><div><span class="text-sm text-slate-500">ผู้สร้างรายการ</span><strong class="block text-slate-950">{{$r->requester->name}}</strong></div><div><span class="text-sm text-slate-500">คลังสินค้า</span><strong class="block text-slate-950">{{$r->warehouse->name}}</strong></div><div><span class="text-sm text-slate-500">วัตถุประสงค์</span><strong class="block text-slate-950">{{$r->purpose}}</strong></div><div><span class="text-sm text-slate-500">การอนุมัติ</span><strong class="block text-slate-950">{{$isAdminCreated?'ดำเนินการทันทีโดย Admin':($r->approver?->name ?? 'รอดำเนินการ')}}</strong></div></div>
            @if($r->targetProduct)<div class="mb-5 flex items-center gap-4 rounded-2xl border border-violet-200 bg-violet-50 p-5"><x-product-image :product="$r->targetProduct" size="lg" /><div><span class="text-sm font-bold text-violet-700">ผลลัพธ์ที่เพิ่มเข้าสต็อก</span><h4 class="mt-1 text-xl font-bold text-slate-950">{{$r->targetProduct->code}} — {{$r->targetProduct->name}}</h4><p>จำนวน {{\App\Support\Quantity::format($r->target_quantity)}} {{$r->targetProduct->unit->name}}</p></div></div>@endif
            <div class="table-shell shadow-none"><div class="panel-header"><h4 class="text-lg font-bold text-slate-950">{{$r->request_type->isBuild()?'ส่วนประกอบที่ใช้':'รายการที่เบิก'}}</h4></div><div class="table-wrap"><table class="data-table"><thead><tr><th>รหัส</th><th>รายการ</th><th class="text-right">จำนวน</th><th>หมายเหตุ</th></tr></thead><tbody>@foreach($r->items as $item)<tr><td class="font-mono font-bold">{{$item->product->code}}</td><td><div class="flex items-center gap-3"><x-product-image :product="$item->product" size="sm" /><span>{{$item->product->name}}</span></div></td><td class="text-right font-bold">{{\App\Support\Quantity::format($item->quantity)}} {{$item->product->unit->name}}</td><td>{{$item->note ?: '—'}}</td></tr>@endforeach</tbody></table></div></div>
            @if($r->status === \App\Enums\RequisitionStatus::REJECTED)<div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800"><strong>เหตุผลที่ไม่อนุมัติ:</strong> {{$r->rejection_reason}}</div>@endif
            @if(auth()->user()->isAdmin() && $r->status === \App\Enums\RequisitionStatus::PENDING)
                @if(!$r->requester_signed_at && !$isAdminCreated)<div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">กำลังรอ {{$r->requester->name}} ลงนามออนไลน์ จึงจะอนุมัติได้</div>@endif
                <div class="mt-6 grid gap-4 border-t border-slate-100 pt-6 sm:grid-cols-2">@if($canApprove)<form method="post" action="{{route('requisitions.approve',$r)}}">@csrf<button class="btn-success w-full">✓ อนุมัติและปรับสต็อก</button></form>@endif<form method="post" action="{{route('requisitions.reject',$r)}}">@csrf<textarea name="reason" class="input reject-reason" rows="2" required placeholder="ระบุเหตุผลที่ไม่อนุมัติ"></textarea><button class="btn-danger mt-3 w-full">ไม่อนุมัติรายการ</button></form></div>
            @elseif($r->status === \App\Enums\RequisitionStatus::APPROVED)
                <div class="mt-6 border-t border-slate-100 pt-6">
                    <div class="rounded-xl border-2 border-blue-200 bg-blue-50 p-5 text-center">
                        <p class="text-lg font-bold text-blue-900">📄 เอกสารพร้อมแล้ว</p>
                        <p class="mt-1 text-sm text-blue-700">ดาวน์โหลด PDF แล้วปริ้นไปส่งแผนกเบิกเพื่อรับสินค้า</p>
                        <div class="mt-4 flex flex-wrap justify-center gap-3">
                            <a href="{{route('requisitions.pdf',$r)}}" class="btn-primary">ดาวน์โหลด PDF</a>
                            <a target="_blank" href="{{route('requisitions.print',$r)}}" class="btn-secondary">ดูตัวอย่างเอกสาร</a>
                        </div>
                    </div>
                </div>
            @elseif(auth()->id() === $r->requested_by)
                <div class="mt-6 flex justify-end border-t border-slate-100 pt-6"><a href="{{route('requisitions.show',$r)}}" class="btn-primary">เปิดเพื่อลงนามออนไลน์</a></div>
            @endif
        </div>
    </div>
</dialog>
