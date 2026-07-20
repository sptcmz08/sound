@extends('layouts.app')
@section('title','รายการเบิกและผลิต') @section('header','รายการเบิกและผลิต')
@section('content')
@php
    $groupedRows = $rows->getCollection()->groupBy(fn($row) => $row->status->value);
    $sections = [
        ['status' => 'PENDING', 'title' => 'รอดำเนินการ', 'subtitle' => 'รายการที่รอลงนามหรือรอผู้ดูแลระบบตรวจสอบ', 'badge' => 'badge-amber', 'dot' => 'bg-amber-500'],
        ['status' => 'APPROVED', 'title' => 'อนุมัติและปรับสต็อกแล้ว', 'subtitle' => 'รายการที่ดำเนินการสำเร็จ สามารถดาวน์โหลด PDF ไปส่งแผนกเบิกได้', 'badge' => 'badge-green', 'dot' => 'bg-emerald-500'],
        ['status' => 'REJECTED', 'title' => 'ไม่อนุมัติ', 'subtitle' => 'รายการที่ถูกตีกลับพร้อมเหตุผล', 'badge' => 'badge-red', 'dot' => 'bg-rose-500'],
    ];
@endphp

<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div><span class="mb-2 inline-flex rounded-full bg-violet-50 px-3 py-1 text-sm font-bold text-violet-700 ring-1 ring-violet-200">PROCESS LIST</span><h2 class="page-title">{{auth()->user()->isAdmin()?'รายการดำเนินงานทั้งหมด':'รายการของฉัน'}}</h2><p class="page-subtitle">แยกรายการตามสถานะ เพื่อค้นหาและจัดการได้ง่าย</p></div>
    <div class="flex flex-wrap gap-3"><a href="{{route('requisitions.withdraw')}}" class="btn-secondary">เบิกสินค้า</a><a href="{{route('requisitions.production')}}" class="btn-primary">+ ผลิต WIP / FG</a></div>
</div>

{{-- Workflow Flow Info --}}
<div class="mb-7 flex flex-wrap items-center gap-4 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3">
    <span class="text-sm font-bold text-blue-700">ขั้นตอน:</span>
    @foreach(['📋 สร้างใบเบิก', '✍️ ลงนามออนไลน์', '✅ Admin อนุมัติ', '📄 ปริ้น PDF ส่งแผนกเบิก'] as $i => $flowStep)
    <span class="text-sm font-semibold text-slate-700">{{$flowStep}}</span>
    @if($i < 3)<span class="text-blue-300">→</span>@endif
    @endforeach
</div>

<div class="mb-7 grid gap-3 sm:grid-cols-3">
    <div class="stat-card"><span class="text-sm font-bold text-slate-500">รายการทั้งหมด</span><strong class="mt-2 block text-3xl text-slate-950">{{$rows->total()}}</strong></div>
    <div class="stat-card"><span class="text-sm font-bold text-amber-600">รอดำเนินการ</span><strong class="mt-2 block text-3xl text-slate-950">{{$statusCounts['pending']}}</strong></div>
    <div class="stat-card"><span class="text-sm font-bold text-emerald-600">ดำเนินการสำเร็จ</span><strong class="mt-2 block text-3xl text-slate-950">{{$statusCounts['approved']}}</strong></div>
</div>

<div class="space-y-7">
@foreach($sections as $section)
    @php $sectionRows = $groupedRows->get($section['status'], collect()); @endphp
    <section class="table-shell">
        <div class="panel-header">
            <div class="flex items-center gap-3"><span class="size-3 rounded-full {{$section['dot']}}"></span><div><h3 class="text-xl font-bold text-slate-950">{{$section['title']}}</h3><p class="text-sm text-slate-500">{{$section['subtitle']}}</p></div></div>
            <span class="{{$section['badge']}}">{{$sectionRows->count()}} รายการในหน้านี้</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>เลขที่ใบเบิก</th><th>ประเภท / รายการ</th><th>ผู้ดำเนินการ</th><th>สถานะขั้นตอน</th><th>วันที่ทำรายการ</th><th class="text-right">จัดการ</th></tr></thead>
                <tbody>
                @forelse($sectionRows as $r)
                    @php
                        $isAdminCreated = $r->requester->isAdmin();
                        $canApprove = auth()->user()->isAdmin() && $r->status === \App\Enums\RequisitionStatus::PENDING && ($isAdminCreated || $r->requester_signed_at);
                        $displayProduct = $r->targetProduct ?? $r->items->first()?->product;

                        // Determine workflow step
                        if ($r->status->value === 'APPROVED') {
                            $stepLabel = '📄 PDF พร้อม';
                            $stepClass = 'bg-emerald-100 text-emerald-800';
                        } elseif ($r->status->value === 'REJECTED') {
                            $stepLabel = '✕ ไม่อนุมัติ';
                            $stepClass = 'bg-rose-100 text-rose-800';
                        } elseif ($isAdminCreated) {
                            $stepLabel = '⏳ รออนุมัติ';
                            $stepClass = 'bg-amber-100 text-amber-800';
                        } elseif ($r->requester_signed_at) {
                            $stepLabel = '⏳ รอ Admin อนุมัติ';
                            $stepClass = 'bg-amber-100 text-amber-800';
                        } else {
                            $stepLabel = '✍️ รอลงนาม';
                            $stepClass = 'bg-blue-100 text-blue-800';
                        }
                    @endphp
                    <tr>
                        <td><strong class="block whitespace-nowrap text-slate-950">{{$r->request_no}}</strong><span class="{{$r->status->badgeClass()}} mt-1">{{$r->status->label()}}</span></td>
                        <td><div class="flex items-center gap-3"><x-product-image :product="$displayProduct" size="sm" /><div><strong class="block text-slate-900">{{$r->request_type->label()}}</strong><span class="text-sm text-slate-500">{{$r->targetProduct ? $r->targetProduct->name.' × '.\App\Support\Quantity::format($r->target_quantity).' '.$r->targetProduct->unit->name : $r->items_count.' รายการ'}}</span></div></div></td>
                        <td class="font-semibold text-slate-900">{{$r->requester->name}}</td>
                        <td><span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold {{$stepClass}}">{{$stepLabel}}</span></td>
                        <td class="whitespace-nowrap">{{$r->requested_at->format('d/m/Y H:i')}}</td>
                        <td>
                            <div class="flex min-w-max justify-end gap-2">
                                <a href="{{route('requisitions.show',$r)}}" class="btn-secondary px-4 py-2">รายละเอียด</a>
                                @if($r->status->value === 'APPROVED')
                                    <a href="{{route('requisitions.pdf',$r)}}" class="btn-primary px-4 py-2">📄 PDF</a>
                                @elseif($canApprove)
                                    <form method="post" action="{{route('requisitions.approve',$r)}}">@csrf<button class="btn-success px-4 py-2">อนุมัติ</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">ไม่มีรายการในสถานะนี้</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endforeach
</div>

<div class="mt-5">{{$rows->links()}}</div>

@foreach($rows as $r)
    @include('requisitions._process-dialog', ['r' => $r])
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-open-process]').forEach(button => button.addEventListener('click', () => {
        const dialog = document.getElementById(`process-${button.dataset.openProcess}`); dialog?.showModal();
        if (button.hasAttribute('data-reject-focus')) setTimeout(() => dialog?.querySelector('.reject-reason')?.focus(), 50);
    }));
    document.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', () => button.closest('dialog')?.close()));
    document.querySelectorAll('dialog').forEach(dialog => dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); }));
    @if(request('focus')) document.getElementById('process-{{(int)request('focus')}}')?.showModal(); @endif
});
</script>
@endpush
