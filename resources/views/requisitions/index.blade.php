@extends('layouts.app')
@section('title','รายการเบิกและผลิต') @section('header','รายการเบิกและผลิต')
@section('content')
@php
    $groupedRows = $rows->getCollection()->groupBy(fn($row) => $row->status->value);
    $sections = [
        ['status' => 'PENDING', 'title' => 'รอดำเนินการ', 'subtitle' => 'รายการที่รอลงนามหรือรอผู้ดูแลระบบตรวจสอบ', 'badge' => 'badge-amber', 'dot' => 'bg-amber-500'],
        ['status' => 'APPROVED', 'title' => 'อนุมัติและปรับสต็อกแล้ว', 'subtitle' => 'รายการที่ดำเนินการสำเร็จและออกเอกสารได้', 'badge' => 'badge-green', 'dot' => 'bg-emerald-500'],
        ['status' => 'REJECTED', 'title' => 'ไม่อนุมัติ', 'subtitle' => 'รายการที่ถูกตีกลับพร้อมเหตุผล', 'badge' => 'badge-red', 'dot' => 'bg-rose-500'],
    ];
@endphp

<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div><span class="mb-2 inline-flex rounded-full bg-violet-50 px-3 py-1 text-sm font-bold text-violet-700 ring-1 ring-violet-200">PROCESS LIST</span><h2 class="page-title">{{auth()->user()->isAdmin()?'รายการดำเนินงานทั้งหมด':'รายการของฉัน'}}</h2><p class="page-subtitle">แยกรายการตามสถานะ เพื่อค้นหาและจัดการได้ง่าย</p></div>
    <div class="flex flex-wrap gap-3"><a href="{{route('requisitions.withdraw')}}" class="btn-secondary">เบิกสินค้า</a><a href="{{route('requisitions.production')}}" class="btn-primary">+ ผลิต WIP / FG</a></div>
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
                <thead><tr><th>เลขที่ใบเบิก</th><th>ประเภท / รายการ</th><th>ผู้ดำเนินการ</th><th>คลังสินค้า</th><th>วันที่ทำรายการ</th><th>ที่มา</th><th class="text-right">จัดการ</th></tr></thead>
                <tbody>
                @forelse($sectionRows as $r)
                    @php $isAdminCreated = $r->requester->isAdmin(); $canApprove = auth()->user()->isAdmin() && $r->status === \App\Enums\RequisitionStatus::PENDING && ($isAdminCreated || $r->requester_signed_at); $displayProduct = $r->targetProduct ?? $r->items->first()?->product; @endphp
                    <tr>
                        <td><strong class="block whitespace-nowrap text-slate-950">{{$r->request_no}}</strong><span class="{{$r->status->badgeClass()}} mt-1">{{$r->status->label()}}</span></td>
                        <td><div class="flex items-center gap-3"><x-product-image :product="$displayProduct" size="sm" /><div><strong class="block text-slate-900">{{$r->request_type->label()}}</strong><span class="text-sm text-slate-500">{{$r->targetProduct ? $r->targetProduct->name.' × '.\App\Support\Quantity::format($r->target_quantity).' '.$r->targetProduct->unit->name : $r->items_count.' รายการ'}}</span></div></div></td>
                        <td class="font-semibold text-slate-900">{{$r->requester->name}}</td>
                        <td>{{$r->warehouse->name}}</td>
                        <td class="whitespace-nowrap">{{$r->requested_at->format('d/m/Y H:i')}}</td>
                        <td>@if($isAdminCreated)<span class="badge-blue">สร้างโดย Admin</span>@else<span class="badge-slate">คำขอพนักงาน</span>@endif</td>
                        <td><div class="flex min-w-max justify-end gap-2"><button type="button" class="btn-secondary px-4 py-2" data-open-process="{{$r->id}}">รายละเอียด</button>@if($canApprove)<form method="post" action="{{route('requisitions.approve',$r)}}">@csrf<button class="btn-success px-4 py-2">อนุมัติ</button></form><button type="button" class="btn-danger px-4 py-2" data-open-process="{{$r->id}}" data-reject-focus>ไม่อนุมัติ</button>@endif</div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">ไม่มีรายการในสถานะนี้</td></tr>
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
