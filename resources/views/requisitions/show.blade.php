@extends('layouts.app')
@section('title', 'ใบเบิกพัสดุ ' . ($requisition->request_no ?? ''))
@section('header', 'รายละเอียดใบเบิกพัสดุ')

@section('content')
@php
    $statusVal = $requisition->status?->value ?? (string)$requisition->status;
    $isPending = $statusVal === 'PENDING';
    $isApproved = $statusVal === 'APPROVED';
    $isRejected = $statusVal === 'REJECTED';
    $pdfReady = method_exists($requisition, 'isReadyForPdf') ? $requisition->isReadyForPdf() : false;
    $typeLabel = method_exists($requisition->request_type, 'label') ? $requisition->request_type->label() : (string)$requisition->request_type;
@endphp

<div class="space-y-6">
    {{-- Header Action Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('requisitions.index') }}" class="btn-secondary text-xs">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                กลับไปประวัติ
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-base font-bold text-slate-900">{{ $requisition->request_no }}</span>
                    <span class="{{ method_exists($requisition->status, 'badgeClass') ? $requisition->status->badgeClass() : 'badge-slate' }}">{{ method_exists($requisition->status, 'label') ? $requisition->status->label() : $statusVal }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">ผู้ขอเบิก: {{ $requisition->requester?->name ?? '—' }} · {{ $requisition->requested_at?->format('d/m/Y H:i') ?? '—' }} น.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($pdfReady)
                <a target="_blank" href="{{ route('requisitions.pdf', $requisition) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                    <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231a1.125 1.125 0 01-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.085 48.085 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.085 48.085 0 011.913-.247m0 0a48.1 48.1 0 0110.56 0m-10.56 0V3.75A1.125 1.125 0 017.5 2.625h9a1.125 1.125 0 011.125 1.125v3.456"/></svg>
                    เปิดใบเบิก PDF
                </a>
            @endif

            @if($isPending)
                @if(auth()->user()?->isAdmin())
                    <form method="post" action="{{ route('requisitions.approve', $requisition) }}" class="inline-block">
                        @csrf
                        <button type="submit" class="btn-success text-xs inline-flex items-center gap-1.5 px-4 py-2">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            อนุมัติและปรับสต็อก
                        </button>
                    </form>
                @else
                    <form method="post" action="{{ route('requisitions.confirm', $requisition) }}" class="inline-block">
                        @csrf
                        <button type="submit" class="btn-primary text-xs inline-flex items-center gap-1.5 px-4 py-2 shadow-lg shadow-blue-500/20">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                            ยืนยันส่งคำขอให้ Admin
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    {{-- Review Step Instruction Banner --}}
    @if($isPending)
    <div class="rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-4 text-xs text-blue-900 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="grid size-9 place-items-center rounded-xl bg-blue-600 text-white font-bold text-sm shrink-0">📄</span>
            <div>
                <strong class="block text-sm font-bold text-blue-950">ตรวจสอบเอกสารใบเบิกพัสดุด้านล่างก่อนส่ง</strong>
                <p class="text-blue-700 mt-0.5">โปรดเช็ครายการและจำนวนสินค้าบนใบเบิกให้ถูกต้องเรียบร้อย จากนั้นกดยืนยันเพื่อส่งให้ Admin อนุมัติ</p>
            </div>
        </div>
        @if(!auth()->user()?->isAdmin())
        <form method="post" action="{{ route('requisitions.confirm', $requisition) }}" class="shrink-0">
            @csrf
            <button type="submit" class="btn-primary text-xs px-5 py-2 font-bold shadow-md shadow-blue-500/25">
                ยืนยันส่งคำขอให้ Admin →
            </button>
        </form>
        @endif
    </div>
    @elseif($isRejected)
    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 flex items-center gap-3">
        <span class="grid size-8 place-items-center rounded-xl bg-rose-600 text-white font-bold shrink-0">✕</span>
        <div>
            <strong class="block text-sm font-bold">ไม่อนุมัติรายการนี้</strong>
            <p class="mt-0.5">เหตุผล: {{ $requisition->rejection_reason ?? '—' }} @if($requisition->rejecter) (โดย {{ $requisition->rejecter->name }}) @endif</p>
        </div>
    </div>
    @endif

    {{-- Embedded PDF Document Paper Sheet --}}
    <div class="mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-8 lg:p-12 shadow-lg relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700"></div>

        {{-- Document Header --}}
        <div class="flex items-start justify-between border-b-2 border-slate-800 pb-6">
            <div class="flex items-center gap-3">
                <div class="grid size-12 place-items-center rounded-xl bg-blue-600 font-bold text-white text-xl shadow-md">W</div>
                <div>
                    <strong class="block text-lg font-bold text-slate-900 leading-tight">WIP Stock</strong>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">INVENTORY MANAGEMENT SYSTEM</span>
                </div>
            </div>
            <div class="text-right">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">ใบเบิกพัสดุ</h1>
                <div class="text-sm font-bold text-blue-600 font-mono mt-1">เลขที่ {{ $requisition->request_no }}</div>
            </div>
        </div>

        {{-- Document Info Grid --}}
        <div class="mt-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">ข้อมูลการเบิก</h2>
            <div class="grid grid-cols-2 gap-px rounded-xl border border-slate-200 bg-slate-200 overflow-hidden text-xs">
                <div class="bg-white p-3">
                    <span class="block text-[11px] font-bold text-slate-400">วันที่เบิก</span>
                    <strong class="block text-slate-800 font-semibold mt-0.5">{{ $requisition->requested_at?->format('d/m/Y H:i') ?? '—' }} น.</strong>
                </div>
                <div class="bg-white p-3">
                    <span class="block text-[11px] font-bold text-slate-400">ชื่อพนักงานผู้เบิก</span>
                    <strong class="block text-slate-800 font-semibold mt-0.5">{{ $requisition->requester?->name ?? '—' }}</strong>
                </div>
                <div class="bg-white p-3">
                    <span class="block text-[11px] font-bold text-slate-400">ประเภทการเบิก</span>
                    <strong class="block text-slate-800 font-semibold mt-0.5">{{ $typeLabel }}</strong>
                </div>
                <div class="bg-white p-3">
                    <span class="block text-[11px] font-bold text-slate-400">คลังสินค้า</span>
                    <strong class="block text-slate-800 font-semibold mt-0.5">{{ $requisition->warehouse?->code ?? '—' }} — {{ $requisition->warehouse?->name ?? '—' }}</strong>
                </div>
            </div>

            @if($requisition->purpose)
            <div class="mt-3 rounded-xl border border-blue-100 bg-blue-50/50 p-3 text-xs">
                <span class="font-bold text-slate-500">วัตถุประสงค์: </span>
                <span class="font-semibold text-slate-800">{{ $requisition->purpose }}</span>
            </div>
            @endif

            @if($requisition->targetProduct)
            <div class="mt-3 rounded-xl border border-purple-200 bg-purple-50 p-3 text-xs text-purple-950">
                <strong class="font-bold">ผลลัพธ์ที่เพิ่มเข้าสต็อก: </strong>
                {{ $requisition->targetProduct->code }} — {{ $requisition->targetProduct->name }}
                จำนวน <strong>{{ \App\Support\Quantity::format($requisition->target_quantity) }} {{ $requisition->targetProduct->unit?->name ?? '' }}</strong>
            </div>
            @endif
        </div>

        {{-- Document Items Table --}}
        <div class="mt-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">รายการที่ขอเบิก</h2>
            <div class="overflow-hidden rounded-xl border border-slate-300">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-800 text-white font-bold uppercase text-[11px]">
                        <tr>
                            <th class="px-4 py-2.5 text-center w-12 border-r border-slate-700">ลำดับ</th>
                            <th class="px-4 py-2.5 w-36 border-r border-slate-700">รหัสสินค้า</th>
                            <th class="px-4 py-2.5 border-r border-slate-700">รายการสินค้า</th>
                            <th class="px-4 py-2.5 text-center w-24 border-r border-slate-700">ประเภท</th>
                            <th class="px-4 py-2.5 text-right w-28 border-r border-slate-700">จำนวนเบิก</th>
                            <th class="px-4 py-2.5 min-w-[120px]">หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($requisition->items ?? [] as $index => $item)
                        <tr class="{{ $loop->even ? 'bg-slate-50/60' : '' }}">
                            <td class="px-4 py-2.5 text-center font-semibold text-slate-500 border-r border-slate-200">{{ $index + 1 }}</td>
                            <td class="px-4 py-2.5 font-bold font-mono text-slate-800 border-r border-slate-200">{{ $item->product?->code ?? '—' }}</td>
                            <td class="px-4 py-2.5 font-medium text-slate-900 border-r border-slate-200">{{ $item->product?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-center border-r border-slate-200">
                                <span class="inline-block rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 border border-slate-200">{{ $item->product?->product_type?->value ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold text-slate-900 border-r border-slate-200">
                                {{ \App\Support\Quantity::format($item->quantity) }} <span class="font-normal text-slate-500 text-[10px]">{{ $item->product?->unit?->name ?? '' }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-500">{{ $item->note ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Document Signatures Section --}}
        <div class="mt-12 pt-8 border-t border-slate-200 grid grid-cols-2 gap-8 text-center text-xs">
            <div>
                <div class="h-16 flex items-center justify-center">
                    <span class="font-bold text-slate-800 border-b border-slate-400 px-8 py-1">{{ $requisition->requester?->name ?? '—' }}</span>
                </div>
                <div class="font-bold text-slate-700">ผู้ขอเบิกพัสดุ</div>
                <div class="text-[10px] text-slate-400 mt-0.5">วันที่ {{ $requisition->requested_at?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div>
                <div class="h-16 flex items-center justify-center">
                    @if($requisition->approver)
                        <span class="font-bold text-slate-800 border-b border-slate-400 px-8 py-1">{{ $requisition->approver->name }}</span>
                    @else
                        <span class="text-slate-300 italic">(รออนุมัติ)</span>
                    @endif
                </div>
                <div class="font-bold text-slate-700">ผู้อนุมัติ (Admin)</div>
                <div class="text-[10px] text-slate-400 mt-0.5">
                    {{ $requisition->approved_at ? 'วันที่ ' . $requisition->approved_at->format('d/m/Y') : 'ยังไม่อนุมัติ' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Action Buttons --}}
    @if($isPending)
    <div class="flex items-center justify-center gap-4 py-4">
        <a href="{{ route('requisitions.index') }}" class="btn-secondary px-6 py-2.5 text-sm font-bold">กลับไปประวัติ</a>
        @if(auth()->user()?->isAdmin())
            <form method="post" action="{{ route('requisitions.approve', $requisition) }}">
                @csrf
                <button type="submit" class="btn-success px-8 py-2.5 text-sm font-bold shadow-lg shadow-emerald-500/20">
                    ✓ อนุมัติและปรับสต็อก
                </button>
            </form>
        @else
            <form method="post" action="{{ route('requisitions.confirm', $requisition) }}">
                @csrf
                <button type="submit" class="btn-primary px-8 py-2.5 text-sm font-bold shadow-lg shadow-blue-500/25">
                    🚀 ยืนยันส่งคำขอให้ Admin
                </button>
            </form>
        @endif
    </div>
    @endif
</div>
@endsection
