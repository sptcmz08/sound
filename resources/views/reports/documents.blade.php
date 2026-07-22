@extends('layouts.app')
@section('title', $title)
@section('header', $title)

@section('content')
<div class="space-y-5">
    <div class="page-head"><div><span class="page-kicker">รายงาน</span><h2 class="page-title">{{ $title }}</h2><p class="page-subtitle">{{ $subtitle }}</p></div><span class="badge-blue">{{ $rows->total() }} เอกสาร</span></div>

    <form class="filter-bar grid gap-3 sm:grid-cols-[1fr_1fr_auto_auto]">
        <label><span class="label">ตั้งแต่วันที่</span><input class="input" type="date" name="date_from" value="{{ request('date_from') }}"></label>
        <label><span class="label">ถึงวันที่</span><input class="input" type="date" name="date_to" value="{{ request('date_to') }}"></label>
        <button class="btn-primary self-end">แสดงรายงาน</button>
        <a href="{{ url()->current() }}" class="btn-secondary self-end">ล้างตัวกรอง</a>
    </form>

    <div class="table-shell">
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>วันที่ / เลขที่เอกสาร</th><th>ผู้ติดต่อ / คลัง</th><th>สินค้า</th><th>ประเภท</th><th class="text-right">จำนวน</th><th class="text-right">มูลค่า</th></tr></thead>
            <tbody>
            @forelse($rows as $document)
                @foreach($document->items as $item)
                @php($lineValue = (float) $item->quantity * (float) ($document->document_type->value === 'SALE_OUT' ? $item->unit_price : $item->unit_cost))
                <tr>
                    <td>@if($loop->first)<span class="block text-[10px] text-slate-400">{{ $document->document_date->format('d/m/Y') }}</span><a href="{{ route('documents.show', $document) }}" class="font-mono text-xs font-semibold text-blue-700">{{ $document->document_no }}</a>@endif</td>
                    <td>@if($loop->first)<strong class="block text-xs text-slate-700">{{ $document->contact_name ?: '—' }}</strong><span class="text-[10px] text-slate-400">{{ $document->warehouse->name }}</span>@endif</td>
                    <td><strong class="block text-xs text-slate-800">{{ $item->product->code }}</strong><span class="text-[10px] text-slate-400">{{ $item->product->name }}</span></td>
                    <td><span class="badge-slate">{{ $item->product->product_type->value }}</span></td>
                    <td class="text-right"><strong>{{ \App\Support\Quantity::format($item->quantity) }}</strong> {{ $item->product->unit->name }}</td>
                    <td class="text-right font-semibold text-slate-800">฿{{ number_format($lineValue, 2) }}</td>
                </tr>
                @endforeach
            @empty
                <tr><td colspan="6" class="empty-state">ยังไม่มีข้อมูลในช่วงวันที่เลือก</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $rows->links() }}</div>
    </div>
</div>
@endsection
