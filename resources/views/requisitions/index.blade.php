@extends('layouts.app')
@section('title','รายการเบิกและผลิต')
@section('header','รายการเบิกและผลิต')

@section('content')
@php
    $filters = [
        '' => ['ทั้งหมด', $rows->total()],
        'PENDING' => ['รออนุมัติ', $statusCounts['pending']],
        'APPROVED' => ['อนุมัติแล้ว', $statusCounts['approved']],
        'REJECTED' => ['ไม่อนุมัติ', $statusCounts['rejected']],
    ];
@endphp

<div class="space-y-5">
    <div class="page-head">
        <div><span class="page-kicker">ติดตามงาน</span><h2 class="page-title">{{ auth()->user()->isAdmin() ? 'รายการเบิกและผลิตทั้งหมด' : 'ใบเบิกของฉัน' }}</h2><p class="page-subtitle">ตรวจสถานะและเปิดดูใบเบิกที่ Admin อนุมัติแล้ว</p></div>
        <div class="flex gap-2"><a href="{{ route('requisitions.withdraw') }}" class="btn-secondary">+ เบิกสินค้า</a><a href="{{ route('requisitions.production') }}" class="btn-primary">+ ผลิต WIP / FG</a></div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="metric-card"><span class="text-xs text-slate-500">รอ Admin อนุมัติ</span><strong class="mt-2 block text-2xl text-amber-600">{{ $statusCounts['pending'] }}</strong></div>
        <div class="metric-card"><span class="text-xs text-slate-500">อนุมัติแล้ว</span><strong class="mt-2 block text-2xl text-emerald-600">{{ $statusCounts['approved'] }}</strong></div>
        <div class="metric-card"><span class="text-xs text-slate-500">ไม่อนุมัติ</span><strong class="mt-2 block text-2xl text-rose-600">{{ $statusCounts['rejected'] }}</strong></div>
    </div>

    <div class="panel overflow-hidden">
        <div class="flex overflow-x-auto border-b border-slate-100 p-2">
            @foreach($filters as $value => [$label, $count])
            <a href="{{ route('requisitions.index', $value ? ['status' => $value] : []) }}" class="min-w-max rounded-lg px-4 py-2 text-xs font-semibold no-underline {{ request('status', '') === $value ? 'bg-blue-600 text-white' : 'text-slate-500 hover:bg-slate-50' }}">{{ $label }} <span class="ml-1 opacity-70">{{ $count }}</span></a>
            @endforeach
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>เลขที่ / วันที่</th><th>ประเภท</th><th>ผู้ขอ / คลัง</th><th>รายการ</th><th>สถานะ</th><th class="text-right">ดำเนินการ</th></tr></thead>
                <tbody>
                @forelse($rows as $row)
                @php
                    $pdfReady = $row->isReadyForPdf();
                    $displayProduct = $row->targetProduct ?? $row->items->first()?->product;
                    $isAdminCreated = $row->requester->isAdmin();
                    $step = match(true) {
                        $row->status->value === 'REJECTED' => ['ไม่อนุมัติ', 'badge-red'],
                        $row->status->value === 'APPROVED' => ['อนุมัติแล้ว', 'badge-green'],
                        default => ['รอ Admin อนุมัติ', 'badge-amber'],
                    };
                @endphp
                <tr>
                    <td><a href="{{ route('requisitions.show', $row) }}" class="font-mono text-xs font-semibold text-blue-700">{{ $row->request_no }}</a><span class="mt-1 block text-[10px] text-slate-400">{{ $row->requested_at->format('d/m/Y H:i') }}</span></td>
                    <td><strong class="block text-xs text-slate-800">{{ $row->request_type->label() }}</strong>@if($isAdminCreated)<span class="mt-1 inline-block text-[10px] text-blue-600">สร้างโดย Admin</span>@endif</td>
                    <td><strong class="block text-xs text-slate-700">{{ $row->requester->name }}</strong><span class="text-[10px] text-slate-400">{{ $row->warehouse->name }}</span></td>
                    <td><div class="flex items-center gap-2"><x-product-image :product="$displayProduct" size="sm" /><span class="text-xs text-slate-600">{{ $row->targetProduct ? $row->targetProduct->name.' × '.\App\Support\Quantity::format($row->target_quantity) : $row->items_count.' รายการ' }}</span></div></td>
                    <td><span class="{{ $step[1] }}">{{ $step[0] }}</span></td>
                    <td><div class="flex justify-end gap-1"><a href="{{ route('requisitions.show', $row) }}" class="btn-secondary">เปิดรายการ</a>@if($pdfReady)<a target="_blank" href="{{ route('requisitions.pdf', $row) }}" class="btn-primary">เปิดใบเบิก PDF</a>@endif</div></td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty-state">ไม่พบรายการในสถานะที่เลือก</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $rows->links() }}</div>
    </div>
</div>
@endsection
