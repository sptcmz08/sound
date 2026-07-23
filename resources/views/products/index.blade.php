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

<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="page-kicker">ข้อมูลหลัก</span>
            <h2 class="page-title">รายการสินค้า</h2>
            <p class="page-subtitle">จัดการข้อมูลสินค้า สูตรการผลิต BOM และ Option เพิ่มและแก้ไขสะดวกในหน้าเดียว</p>
        </div>
        <div class="flex flex-wrap gap-2.5">
            @if(auth()->user()?->canOperateStock())
                <a href="{{ route('operations.create', 'supplier-receive') }}" class="btn-success">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12L12 16.5m0 0l4.5-4.5M12 16.5V3"/></svg>
                    รับสินค้าเข้า
                </a>
            @endif
            @if(auth()->user()?->isAdmin())
                <a href="{{ route('products.import.form') }}" class="btn-secondary">Import Excel</a>
                <button type="button" onclick="openProductModal('{{ route('products.create', ['type' => $selectedType]) }}', 'เพิ่มสินค้า {{ $selectedType }}ใหม่')" class="btn-primary">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    + เพิ่ม {{ $selectedType }}
                </button>
            @endif
        </div>
    </div>

    {{-- Tabs & Search --}}
    <div class="panel overflow-hidden">
        <div class="flex overflow-x-auto border-b border-slate-200/80 px-3 pt-2">
            @foreach($tabs as $value => [$label, $description, $badge])
            <a href="{{ route('products.index', ['type' => $value]) }}" class="min-w-36 border-b-2 px-5 py-3.5 text-center transition-all duration-200 {{ $selectedType === $value ? 'border-blue-600 text-blue-700 font-bold' : 'border-transparent text-slate-400 hover:text-slate-700 font-medium' }}">
                <strong class="block text-xs uppercase tracking-wider">{{ $label }}</strong>
                <small class="mt-0.5 block text-[10px] text-slate-500 font-normal">{{ $description }}</small>
            </a>
            @endforeach
        </div>
        <form class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-[1fr_auto]">
            <input type="hidden" name="type" value="{{ $selectedType }}">
            <input class="input" name="q" value="{{ request('q') }}" placeholder="ค้นหารหัส ชื่อสินค้า หรือ Barcode...">
            <button class="btn-primary">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                ค้นหา
            </button>
        </form>
    </div>

    {{-- Product Table --}}
    <div class="table-shell">
        <div class="panel-header">
            <div>
                <h3 class="section-title">{{ $tabs[$selectedType][0] ?? $selectedType }}</h3>
                <p class="section-subtitle">{{ $tabs[$selectedType][1] ?? 'รายการสินค้า' }} · ทั้งหมด {{ $products->total() }} รายการ</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="py-4">รูป & สินค้า</th>
                        <th class="py-4">ประเภท</th>
                        <th class="py-4 text-right">คงเหลือรวม</th>
                        <th class="py-4 text-right">ต้นทุน</th>
                        <th class="py-4 text-right">ราคาขาย</th>
                        <th class="py-4">สูตร / Option</th>
                        <th class="py-4">สถานะ</th>
                        <th class="py-4 text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    @php
                        $badge = match($product->product_type->value) {'PART' => 'badge-part', 'SUPPLY' => 'badge-supply', 'WIP' => 'badge-wip', default => 'badge-fg'};
                    @endphp
                    <tr>
                        <td class="py-4">
                            <div class="flex items-center gap-4">
                                {{-- Large Product Thumbnail (size="md" / 64px x 64px) --}}
                                <x-product-image :product="$product" size="md" class="size-16 rounded-2xl border-2 border-slate-200 shadow-md object-cover shrink-0" />
                                <div class="min-w-0">
                                    <strong class="block text-sm font-bold text-slate-900 leading-tight">{{ $product->code }}</strong>
                                    <span class="text-xs text-slate-500 font-medium block mt-0.5">{{ $product->name }}</span>
                                    @if($product->location_text)
                                        <span class="inline-block text-[10px] text-slate-400 mt-1 bg-slate-100 px-2 py-0.5 rounded-md">📍 {{ $product->location_text }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><span class="{{ $badge }}">{{ $product->product_type->value }}</span></td>
                        <td class="text-right">
                            <strong class="text-sm font-bold text-slate-900 block">{{ \App\Support\Quantity::format($product->balances_sum_quantity ?? 0) }}</strong>
                            <span class="text-[10px] text-slate-400">{{ $product->unit->name }}</span>
                        </td>
                        <td class="text-right font-medium">฿{{ number_format((float) $product->standard_cost, 2) }}</td>
                        <td class="text-right font-bold text-blue-700">฿{{ number_format((float) $product->sale_price, 2) }}</td>
                        <td>
                            @if(in_array($product->product_type->value, ['WIP', 'FG'], true))
                                <span class="badge-blue">BOM {{ $product->components_count }} รายการ</span>
                                @if($product->product_type->value === 'FG' && $product->option_groups_count)
                                    <span class="badge-wip mt-1 block w-max">Option {{ $product->option_groups_count }} กลุ่ม</span>
                                @endif
                            @else
                                <span class="text-xs text-slate-400 font-normal">ไม่ใช้สูตร</span>
                            @endif
                        </td>
                        <td><span class="{{ $product->is_active ? 'badge-green' : 'badge-slate' }}">{{ $product->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}</span></td>
                        <td class="text-right">
                            @if(auth()->user()?->isAdmin())
                            <div class="flex justify-end gap-1.5">
                                <button type="button" onclick="openProductModal('{{ route('products.edit', $product) }}', 'แก้ไข {{ $product->code }} — {{ $product->name }}')" class="btn-ghost font-bold text-blue-600 hover:bg-blue-50">
                                    แก้ไข
                                </button>
                                <form method="post" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('ยืนยันปิดใช้งานหรือลบสินค้านี้?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-ghost text-rose-600 hover:bg-rose-50">ลบ</button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-slate-400">ดูอย่างเดียว</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="empty-state">ยังไม่มีสินค้าในประเภทนี้</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $products->links() }}</div>
    </div>
</div>

{{-- Product Add/Edit Popup Modal --}}
<dialog id="product-modal" class="native-modal max-w-5xl w-full rounded-3xl border-0 p-0 shadow-2xl bg-white overflow-hidden">
    <div class="flex items-center justify-between border-b border-slate-800 bg-slate-900 px-6 py-4 text-white">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold shadow-md">
                📦
            </div>
            <div>
                <h3 id="modal-title" class="text-base font-bold leading-tight">จัดการสินค้า</h3>
                <p id="modal-subtitle" class="text-xs text-slate-400">กรอกข้อมูล เลือกรูปจากเครื่อง และตั้งค่าสูตรการผลิต</p>
            </div>
        </div>
        <button type="button" onclick="closeProductModal()" class="rounded-xl p-2 text-slate-400 hover:bg-white/10 hover:text-white transition-colors">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="relative bg-slate-100 p-2 sm:p-4">
        <div id="modal-spinner" class="absolute inset-0 z-10 flex items-center justify-center bg-white/80 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-2">
                <div class="size-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
                <span class="text-xs font-bold text-slate-600">กำลังโหลดข้อมูล...</span>
            </div>
        </div>
        <iframe id="product-modal-frame" class="w-full min-h-[680px] rounded-2xl border-0 bg-white shadow-sm" src=""></iframe>
    </div>
</dialog>

@push('scripts')
<script>
function openProductModal(url, title) {
    const modal = document.getElementById('product-modal');
    const frame = document.getElementById('product-modal-frame');
    const titleEl = document.getElementById('modal-title');
    const spinner = document.getElementById('modal-spinner');

    if (title) titleEl.innerText = title;
    spinner.classList.remove('hidden');

    const modalUrl = url + (url.includes('?') ? '&' : '?') + 'modal=1';
    frame.src = modalUrl;

    frame.onload = function() {
        spinner.classList.add('hidden');
    };

    modal.showModal();
}

function closeProductModal() {
    const modal = document.getElementById('product-modal');
    const frame = document.getElementById('product-modal-frame');
    frame.src = '';
    modal.close();
}
</script>
@endpush
@endsection
