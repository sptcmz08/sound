@extends('layouts.app')

@section('title', $selectedType?->label() ?? 'เบิกสินค้า / สร้างอุปกรณ์')
@section('header', $selectedType?->label() ?? 'เบิกสินค้า / สร้างอุปกรณ์')

@section('content')
@php
    $productOptions = $products->map(fn ($product) => [
        'id' => $product->id,
        'code' => $product->code,
        'name' => $product->name,
        'image' => $product->image_path ? route('products.image', $product) : null,
        'type' => $product->product_type->value,
        'unit' => $product->unit->name,
        'balances' => $product->balances->mapWithKeys(fn ($balance) => [
            (string) $balance->warehouse_id => \App\Support\Quantity::format($balance->quantity),
        ]),
        'components' => $product->components->map(fn ($component) => [
            'name' => $component->name,
            'code' => $component->code,
            'quantity' => \App\Support\Quantity::format($component->pivot->quantity),
            'unit' => $component->unit?->name,
        ])->values(),
    ])->values();
@endphp

<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h2 class="page-title">{{ $selectedType?->label() ?? 'เลือกรายการที่ต้องการ' }}</h2>
        <p class="page-subtitle">เลือกสินค้า ระบุจำนวนและคลัง จากนั้นกดยืนยันได้ทันที</p>
    </div>

    <form method="post" action="{{ route('requisitions.store') }}" class="space-y-6" id="req-form">
        @csrf

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="text-xl font-bold text-slate-950">1. ประเภทรายการ</h3>
                    @if($selectedType)
                        <p class="mt-0.5 text-sm text-slate-500">เลือกไว้แล้ว · <a class="font-bold text-blue-600 hover:underline" href="{{ $selectedType->isBuild() ? route('requisitions.production') : route('requisitions.withdraw') }}">เปลี่ยนประเภท</a></p>
                    @endif
                </div>
            </div>
            <div class="panel-body">
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach($types as $type)
                    <label class="cursor-pointer">
                        <input class="peer sr-only" type="radio" name="request_type" value="{{ $type->value }}" @checked(old('request_type', $selectedType?->value ?? 'GENERAL_ISSUE') === $type->value)>
                        <span class="block h-full rounded-xl border-2 border-slate-200 p-4 transition peer-checked:border-blue-600 peer-checked:bg-blue-50">
                            <strong class="block text-lg text-slate-950">{{ $type->label() }}</strong>
                            <small class="mt-1 block text-slate-500">{{ $type->description() }}</small>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div><h3 class="text-xl font-bold text-slate-950">2. รายการที่ต้องการ</h3><p class="mt-0.5 text-sm text-slate-500">เลือกสินค้าและกรอกจำนวนที่ต้องการเบิก</p></div>
                <label class="min-w-64">
                    <span class="label text-sm">คลังสินค้า <span class="text-rose-500">*</span></span>
                    <select name="warehouse_id" id="warehouse" class="select bg-white" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->code }} — {{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="panel-body">
                <div id="build-fields" class="hidden grid gap-5 md:grid-cols-[minmax(0,1fr)_240px]">
                    <label><span class="label">เลือกรายการที่จะสร้าง</span><select class="select" name="target_product_id" id="target-product"><option value="">— เลือก —</option></select></label>
                    <label><span class="label">จำนวนที่จะสร้าง</span><input class="input text-lg font-bold" name="target_quantity" type="number" min="0.0001" step="0.0001" value="{{ old('target_quantity', 1) }}"></label>
                    <div id="bom-preview" class="rounded-xl bg-amber-50 p-4 text-amber-900 md:col-span-2"></div>
                </div>

                <div id="direct-fields">
                    <div class="mb-2 hidden grid-cols-[minmax(0,1fr)_160px_190px_64px] gap-3 px-3 text-sm font-bold text-slate-500 md:grid">
                        <span>สินค้า</span><span>สต็อกคงเหลือ</span><span>จำนวนที่เบิก</span><span></span>
                    </div>
                    <div id="item-list" class="space-y-3"></div>
                    <button type="button" id="add-item" class="btn-secondary mt-4">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                        เพิ่มรายการเบิก
                    </button>
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">หลังยืนยัน รายการจะส่งให้แอดมินตรวจและอนุมัติก่อนตัดสต็อก</p>
            <button class="btn-primary px-8">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"/></svg>
                ยืนยันรายการ
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const products = @json($productOptions);
let rows = @json(old('items', [['product_id' => '', 'quantity' => 1]]));
const radios = [...document.querySelectorAll('[name=request_type]')];
const list = document.getElementById('item-list');
const target = document.getElementById('target-product');
const warehouse = document.getElementById('warehouse');

function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
}

function current() {
    return radios.find(radio => radio.checked)?.value ?? 'GENERAL_ISSUE';
}

function isBuild() {
    return ['BUILD_WIP', 'BUILD_FG'].includes(current());
}

function wantedType() {
    return {
        GENERAL_ISSUE: ['PART', 'SUPPLY'], ISSUE_WIP: ['WIP'], ISSUE_FG: ['FG'],
        BUILD_WIP: ['WIP'], BUILD_FG: ['FG']
    }[current()];
}

function componentTypes() {
    return {
        GENERAL_ISSUE: ['PART', 'SUPPLY'], ISSUE_WIP: ['WIP'], ISSUE_FG: ['FG'],
        BUILD_WIP: ['PART', 'SUPPLY'], BUILD_FG: ['WIP', 'PART', 'SUPPLY']
    }[current()];
}

function matchingProducts() {
    const types = wantedType();
    return products.filter(product => types.includes(product.type));
}

function componentProducts() {
    const types = componentTypes();
    return products.filter(product => types.includes(product.type));
}

function directOptions(selected) {
    const pool = isBuild() ? componentProducts() : matchingProducts();
    return '<option value="">— เลือกสินค้า —</option>' + pool.map(product =>
        `<option value="${product.id}" ${String(product.id) === String(selected) ? 'selected' : ''}>[${escapeHtml(product.type)}] ${escapeHtml(product.code)} — ${escapeHtml(product.name)}</option>`
    ).join('');
}

function balanceFor(productId) {
    const product = products.find(item => String(item.id) === String(productId));
    if (!product) return '<span class="text-slate-400">—</span>';
    const balance = product.balances[String(warehouse.value)] ?? '0';
    return `<strong class="text-lg text-slate-950">${escapeHtml(balance)}</strong> <span class="text-sm text-slate-500">${escapeHtml(product.unit)}</span>`;
}

function productPicture(productId) {
    const product = products.find(item => String(item.id) === String(productId));
    return product?.image ? `<img src="${product.image}" class="size-12 shrink-0 rounded-xl border border-slate-200 bg-white object-cover" alt="รูปสินค้า">` : `<span class="grid size-12 shrink-0 place-items-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">▧</span>`;
}

function renderRows() {
    list.innerHTML = rows.map((row, index) => `
        <div class="grid items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 md:grid-cols-[minmax(0,1fr)_160px_190px_64px]">
            <label><span class="label md:hidden">สินค้า</span><div class="flex items-center gap-3">${productPicture(row.product_id)}<select class="select bg-white" name="items[${index}][product_id]" onchange="updateProduct(${index}, this.value)" required>${directOptions(row.product_id)}</select></div></label>
            <div><span class="label md:hidden">สต็อกคงเหลือ</span><div class="rounded-xl bg-white px-4 py-3 ring-1 ring-slate-200">${balanceFor(row.product_id)}</div></div>
            <label><span class="label md:hidden">จำนวนที่เบิก</span><input class="input bg-white text-lg font-bold" name="items[${index}][quantity]" type="number" min="0.0001" step="0.0001" value="${escapeHtml(row.quantity || 1)}" oninput="updateQuantity(${index}, this.value)" required></label>
            <button type="button" class="grid min-h-12 place-items-center rounded-xl text-rose-600 hover:bg-rose-50" onclick="removeRow(${index})" title="ลบรายการ" aria-label="ลบรายการ"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-9 0V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5"/></svg></button>
        </div>
    `).join('');
}

function updateProduct(index, productId) {
    rows[index].product_id = productId;
    renderRows();
}

function updateQuantity(index, quantity) {
    rows[index].quantity = quantity;
}

function removeRow(index) {
    rows.splice(index, 1);
    renderRows();
}

function render() {
    document.getElementById('build-fields').classList.toggle('hidden', !isBuild());
    document.getElementById('direct-fields').classList.toggle('hidden', isBuild());
    target.innerHTML = '<option value="">— เลือกรายการ —</option>' + matchingProducts()
        .filter(product => product.components.length)
        .map(product => `<option value="${product.id}">[${escapeHtml(product.code)}] ${escapeHtml(product.name)}</option>`).join('');
    renderRows();
    preview();
}

function preview() {
    const product = products.find(item => String(item.id) === target.value);
    document.getElementById('bom-preview').innerHTML = product
        ? `<div class="flex items-center gap-3">${productPicture(product.id)}<div><strong class="block">${escapeHtml(product.code)} — ${escapeHtml(product.name)}</strong><span>ส่วนประกอบต่อ 1 ชิ้น: ${product.components.map(component => `${escapeHtml(component.code)} ${escapeHtml(component.name)} × ${escapeHtml(component.quantity)} ${escapeHtml(component.unit)}`).join(', ')}</span></div></div>`
        : 'กรุณาเลือกรายการ ระบบจะแสดงส่วนประกอบที่ต้องใช้ตรงนี้';
}

radios.forEach(radio => radio.addEventListener('change', () => {
    rows = [{ product_id: '', quantity: 1 }];
    render();
}));
document.getElementById('add-item').addEventListener('click', () => {
    rows.push({ product_id: '', quantity: 1 });
    renderRows();
});
warehouse.addEventListener('change', renderRows);
target.addEventListener('change', preview);
render();
</script>
@endpush
