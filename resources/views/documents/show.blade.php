@extends('layouts.app')
@section('title', $document->document_no)
@section('header', 'รายละเอียดเอกสาร (Document Details)')

@section('content')
<div class="space-y-6">
    {{-- Top Header Bar --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ url()->previous() }}" class="mb-2 inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-blue-600 transition-colors">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                ย้อนกลับ
            </a>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="page-title">{{ $document->document_no }}</h2>
                <span class="{{ $document->status->value==='POSTED' ? 'badge-green' : ($document->status->value==='CANCELLED' ? 'badge-slate' : 'badge-amber') }}">
                    ● {{ $document->status->value }}
                </span>
            </div>
            <p class="page-subtitle">{{ $document->document_type->label() }}</p>
        </div>
        <div class="rounded-3xl bg-slate-900 p-5 text-right text-white shadow-xl shadow-slate-900/10">
            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">วันที่ทำรายการ</span>
            <strong class="text-xl font-bold tracking-tight text-white">{{ $document->document_date->format('d/m/Y') }}</strong>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
        <div class="space-y-6">
            {{-- Document Items List --}}
            <div class="table-shell">
                <div class="panel-header flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">รายการสินค้าในเอกสาร</h3>
                        <p class="text-xs text-slate-500">รวมสินค้าหลักและรายการ Option ที่ตัดสต็อก</p>
                    </div>
                    <span class="badge-blue">{{ $document->items->count() }} รายการ</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>สินค้า & ตัวเลือกเสริม (Option)</th>
                                <th class="text-right">จำนวนตัดสต็อก</th>
                                <th>หน่วยนับ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($document->items as $i)
                            <tr>
                                <td>
                                    <div class="flex items-start gap-3">
                                        <x-product-image :product="$i->product" size="sm" />
                                        <div>
                                            <strong class="block text-sm font-bold text-slate-900">{{ $i->product->name }}</strong>
                                            <span class="font-mono text-xs text-slate-400 font-semibold">{{ $i->product->code }}</span>
                                            
                                            @if($i->options->count() > 0)
                                            <div class="mt-2 rounded-2xl border border-violet-100 bg-violet-50/70 p-3 space-y-1 text-xs">
                                                <span class="block font-bold text-violet-900">⚙️ ตัวเลือกเสริมที่เลือกและถูกตัดสต็อก:</span>
                                                @foreach($i->options as $opt)
                                                <div class="flex items-center gap-1.5 text-violet-700 font-medium">
                                                    <span>• {{ $opt->optionItem->group->name }}:</span>
                                                    <strong class="text-violet-950">{{ $opt->optionItem->optionProduct->name }}</strong>
                                                    @if($opt->optionItem->additional_price > 0)
                                                    <span class="text-xs text-violet-500">(+@quantity($opt->optionItem->additional_price) ฿)</span>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <strong class="text-lg font-black text-slate-900">@quantity($i->quantity)</strong>
                                </td>
                                <td class="font-medium text-slate-600">{{ $i->product->unit->name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($document->reversal)
            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-2xl bg-amber-500 text-white font-bold text-lg">!</span>
                    <div>
                        <p class="text-sm font-bold text-amber-900">เอกสารนี้มีรายการ Reversal ยกเลิกแล้ว</p>
                        <a class="mt-1 inline-flex items-center gap-1 text-xs font-bold text-amber-700 hover:underline" href="{{ route('documents.show', $document->reversal) }}">
                            ดูเอกสาร Reversal {{ $document->reversal->document_no }} →
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar Info Card --}}
        <aside class="space-y-6">
            <div class="panel">
                <div class="panel-header">
                    <h3 class="text-lg font-bold text-slate-900">ข้อมูลสำคัญของเอกสาร</h3>
                </div>
                <div class="panel-body space-y-4">
                    <dl class="space-y-3.5 text-xs">
                        @foreach([
                            ['ประเภทเอกสาร', $document->document_type->label()],
                            ['คลังสินค้า', $document->warehouse->name],
                            ['ผู้สร้างเอกสาร', $document->creator->name],
                            ['ผู้บันทึก POST', $document->poster?->name ?? '-'],
                            ['เลขอ้างอิง', $document->reference_no ?: '-']
                        ] as [$label, $value])
                        <div class="flex justify-between items-center gap-4 border-b border-slate-100 pb-2.5">
                            <dt class="font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</dt>
                            <dd class="text-right font-bold text-slate-800">{{ $value }}</dd>
                        </div>
                        @endforeach
                    </dl>

                    @if($document->note)
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-4">
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">หมายเหตุ</span>
                        <p class="text-xs text-slate-700 leading-relaxed">{{ $document->note }}</p>
                    </div>
                    @endif
                </div>
            </div>

            @if(auth()->user()->isAdmin() && $document->status->value === 'POSTED' && $document->document_type->value !== 'REVERSAL')
            <form method="post" action="{{ route('documents.cancel', $document) }}" class="panel border-rose-200 p-6 space-y-3" onsubmit="return confirm('ยืนยันยกเลิกเอกสาร? ระบบจะสร้าง Reversal คืนสต็อกเข้าคลัง')">
                @csrf
                <h3 class="font-bold text-rose-700 flex items-center gap-2 text-sm">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    ยกเลิกเอกสารนี้
                </h3>
                <p class="text-xs text-slate-500 leading-relaxed">การยกเลิกจะสร้างรายการ Reversal คืนสต็อกกลับเข้าคลัง และไม่ลบประวัติเดิม</p>
                <textarea class="input min-h-20 text-xs" name="reason" required placeholder="ระบุเหตุผลการยกเลิกเอกสาร..."></textarea>
                <button class="btn-danger w-full text-xs font-bold shadow-lg shadow-rose-500/20">
                    ✕ ยืนยันยกเลิกเอกสาร
                </button>
            </form>
            @endif
        </aside>
    </div>
</div>
@endsection

