@extends('layouts.app')

@section('title', 'รายการรออนุมัติ')
@section('header', 'รายการรออนุมัติ')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="page-kicker">ศูนย์อนุมัติคำขอ</span>
            <h2 class="page-title">รายการรออนุมัติการเบิกและผลิต</h2>
            <p class="page-subtitle"> Admin ตรวจสอบและกดอนุมัติคำขอเบิกพัสดุหรือสั่งผลิตเพื่อตัดสต็อกและออกใบเบิก</p>
        </div>
        <div class="flex gap-2.5">
            <a href="{{ route('requisitions.index') }}" class="btn-secondary">
                <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                ดูประวัติใบเบิกทั้งหมด
            </a>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="metric-card">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500">รอ Admin อนุมัติ</span>
                    <strong class="mt-1 block text-2xl font-bold text-amber-600">{{ $pendingCount }} <span class="text-xs font-normal text-slate-400">รายการ</span></strong>
                </div>
                <div class="size-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg border border-amber-200/60">
                    ⏳
                </div>
            </div>
        </div>
        <div class="metric-card">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500">ประเภทคำขอ</span>
                    <strong class="mt-1 block text-2xl font-bold text-blue-600">เบิก & ผลิต</strong>
                </div>
                <div class="size-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg border border-blue-200/60">
                    📋
                </div>
            </div>
        </div>
        <div class="metric-card">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500">การดำเนินการ</span>
                    <strong class="mt-1 block text-2xl font-bold text-emerald-600">อนุมัติแบบ Realtime</strong>
                </div>
                <div class="size-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg border border-emerald-200/60">
                    ⚡
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table (matching requisitions/index.blade.php) --}}
    <div class="table-shell">
        <div class="panel-header">
            <div>
                <h3 class="section-title">รายการคำขอที่รอการตรวจสอบ</h3>
                <p class="section-subtitle">คลิก "เปิดตรวจ" เพื่อตรวจสอบรายละเอียดรายการพัสดุและอนุมัติ</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="py-4">เลขที่เอกสาร / วันที่</th>
                        <th class="py-4">ประเภทคำขอ</th>
                        <th class="py-4">ผู้ขอ / คลังสินค้า</th>
                        <th class="py-4">รายการสินค้า/พัสดุ</th>
                        <th class="py-4">สถานะ</th>
                        <th class="py-4 text-right">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                    @php
                        $displayProduct = $row->targetProduct ?? $row->items?->first()?->product;
                        $isAdminCreated = $row->requester?->isAdmin() ?? false;
                        $typeLabel = method_exists($row->request_type, 'label') ? $row->request_type->label() : (string)$row->request_type;
                        $statusLabel = method_exists($row->status, 'label') ? $row->status->label() : (string)$row->status;
                        $badgeClass = method_exists($row->status, 'badgeClass') ? $row->status->badgeClass() : 'badge-amber';
                    @endphp
                    <tr>
                        <td class="py-4">
                            <a href="{{ route('requisitions.show', $row) }}" class="font-mono text-xs font-bold text-blue-700 hover:underline block">
                                {{ $row->request_no }}
                            </a>
                            <span class="mt-0.5 block text-[10px] text-slate-400 font-medium">
                                📅 {{ $row->requested_at?->format('d/m/Y H:i') ?? '—' }} น.
                            </span>
                        </td>
                        <td class="py-4">
                            <strong class="block text-xs font-bold text-slate-800">{{ $typeLabel }}</strong>
                            @if($isAdminCreated)
                                <span class="mt-1 inline-block text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/60">สร้างโดย Admin</span>
                            @endif
                        </td>
                        <td class="py-4">
                            <strong class="block text-xs font-bold text-slate-900">{{ $row->requester?->name ?? '—' }}</strong>
                            <span class="text-[10px] text-slate-500 block mt-0.5">🏬 {{ $row->warehouse?->name ?? '—' }}</span>
                        </td>
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <x-product-image :product="$displayProduct" size="md" class="size-12 rounded-xl border border-slate-200 object-cover shadow-sm shrink-0" />
                                <div class="min-w-0">
                                    <strong class="block text-xs font-bold text-slate-800 truncate">
                                        {{ $row->targetProduct ? $row->targetProduct->code.' — '.$row->targetProduct->name : ($row->items->first()?->product?->name ?? 'รายการพัสดุ') }}
                                    </strong>
                                    <span class="text-[10px] text-slate-500 block mt-0.5">
                                        {{ $row->targetProduct ? 'จำนวนสร้าง: '.\App\Support\Quantity::format($row->target_quantity).' '.$row->targetProduct->unit->name : 'เบิกทั้งหมด '.($row->items_count ?? $row->items->count()).' รายการ' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4">
                            <span class="{{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="py-4 text-right">
                            <a href="{{ route('requisitions.show', $row) }}" class="btn-primary">
                                เปิดตรวจ →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state py-12 text-center text-slate-400 font-medium">
                            <span class="text-2xl block mb-2">🎉</span>
                            ไม่มีรายการคำขอเบิกหรือสั่งผลิตที่รอการอนุมัติในขณะนี้
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $rows->links() }}</div>
    </div>
</div>
@endsection
