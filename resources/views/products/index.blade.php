@extends('layouts.app')

@section('title', 'รายการสินค้า')
@section('header', 'รายการสินค้า')

@section('content')
@php
    $selectedType = request('type', 'PART');
    $tabs = [
        'PART' => ['PART', 'อะไหล่ผลิต', 'badge-part'],
        'SUPPLY' => ['SUPPLY', 'วัสดุสิ้นเปลือง', 'badge-supply'],
        'WIP' => ['WIP', 'งานระหว่างประกอบ', 'badge-wip'],
        'FG' => ['FG', 'สินค้าสำเร็จรูป', 'badge-fg'],
    ];
@endphp

<div class="space-y-5">
    <div class="page-head">
        <div><span class="page-kicker">ข้อมูลหลัก</span><h2 class="page-title">รายการสินค้า</h2><p class="page-subtitle">จัดการ PART, SUPPLY, WIP และ FG จากหน้ากลางเดียว</p></div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()->canOperateStock())<a href="{{ route('operations.create', 'supplier-receive') }}" class="btn-success">รับสินค้าเข้า</a>@endif
            @if(auth()->user()->isAdmin())<a href="{{ route('products.import.form') }}" class="btn-secondary">Import</a><a href="{{ route('products.create', ['type' => $selectedType]) }}" class="btn-primary">+ เพิ่ม {{ $selectedType }}</a>@endif
        </div>
    </div>

    <div class="panel overflow-hidden">
        <div class="flex overflow-x-auto border-b border-slate-100 px-3 pt-2">
            @foreach($tabs as $value => [$label, $description, $badge])
            <a href="{{ route('products.index', ['type' => $value]) }}" class="min-w-36 border-b-2 px-4 py-3 text-center transition {{ $selectedType === $value ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-400 hover:text-slate-700' }}">
                <strong class="block text-xs">{{ $label }}</strong><small class="mt-0.5 block text-[10px]">{{ $description }}</small>
            </a>
            @endforeach
        </div>
        <form class="grid gap-2 border-b border-slate-100 p-4 sm:grid-cols-[1fr_auto]">
            <input type="hidden" name="type" value="{{ $selectedType }}">
            <input class="input" name="q" value="{{ request('q') }}" placeholder="ค้นหารหัส ชื่อสินค้า หรือ Barcode">
            <button class="btn-primary">ค้นหา</button>
        </form>
    </div>

    <div class="table-shell">
        <div class="panel-header"><div><h3 class="section-title">{{ $tabs[$selectedType][0] ?? $selectedType }}</h3><p class="section-subtitle">{{ $tabs[$selectedType][1] ?? 'รายการสินค้า' }} · {{ $products->total() }} รายการ</p></div></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>สินค้า</th><th>ประเภท</th><th class="text-right">คงเหลือรวม</th><th class="text-right">ต้นทุน</th><th class="text-right">ราคาขาย</th><th>สูตร / Option</th><th>สถานะ</th><th class="text-right">จัดการ</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                    @php
                        $badge = match($product->product_type->value) {'PART' => 'badge-part', 'SUPPLY' => 'badge-supply', 'WIP' => 'badge-wip', default => 'badge-fg'};
                    @endphp
                    <tr>
                        <td><div class="flex items-center gap-3"><x-product-image :product="$product" size="sm" /><div><strong class="block text-xs text-slate-800">{{ $product->code }}</strong><span class="text-[11px] text-slate-400">{{ $product->name }}</span></div></div></td>
                        <td><span class="{{ $badge }}">{{ $product->product_type->value }}</span></td>
                        <td class="text-right"><strong class="text-xs text-slate-800">{{ \App\Support\Quantity::format($product->balances_sum_quantity ?? 0) }}</strong> <span class="text-[10px] text-slate-400">{{ $product->unit->name }}</span></td>
                        <td class="text-right">฿{{ number_format((float) $product->standard_cost, 2) }}</td>
                        <td class="text-right">฿{{ number_format((float) $product->sale_price, 2) }}</td>
                        <td>
                            @if(in_array($product->product_type->value, ['WIP', 'FG'], true))
                                <span class="badge-blue">BOM {{ $product->components_count }} รายการ</span>
                                @if($product->product_type->value === 'FG' && $product->option_groups_count)<span class="badge-wip">Option {{ $product->option_groups_count }} กลุ่ม</span>@endif
                            @else
                                <span class="text-[10px] text-slate-400">ไม่ใช้สูตร</span>
                            @endif
                        </td>
                        <td><span class="{{ $product->is_active ? 'badge-green' : 'badge-slate' }}">{{ $product->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}</span></td>
                        <td class="text-right">
                            @if(auth()->user()->isAdmin())
                            <div class="flex justify-end gap-1"><a href="{{ route('products.edit', $product) }}" class="btn-ghost">แก้ไข</a><form method="post" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('ยืนยันปิดใช้งานหรือลบสินค้านี้?')">@csrf @method('DELETE')<button class="btn-ghost text-rose-600">ลบ</button></form></div>
                            @else
                            <span class="text-[10px] text-slate-400">ดูอย่างเดียว</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="empty-state">ยังไม่มีสินค้าในประเภทนี้</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $products->links() }}</div>
    </div>
</div>
@endsection
