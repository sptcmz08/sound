@extends('layouts.app')

@php
    $isProduction = $formMode === 'production';
    $pageTitle = $isProduction ? 'ผลิตเข้า WIP / FG' : 'เบิก-จ่ายสินค้า';
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

@section('title', $pageTitle)
@section('header', $pageTitle)

@section('content')
<div class="space-y-5">
    <div class="page-head">
        <div>
            <span class="page-kicker">{{ $isProduction ? 'เพิ่มสินค้าที่ผลิตเสร็จเข้าสต็อก' : 'สร้างใบขอเบิกจากสต็อก' }}</span>
            <h2 class="page-title">{{ $pageTitle }}</h2>
            <p class="page-subtitle">{{ $isProduction ? 'เลือกสินค้าที่กำหนดสูตรไว้ ระบบคำนวณวัตถุดิบและรับผลผลิตเข้าสต็อกให้อัตโนมัติ' : 'ดูสินค้าทั้งหมด เลือกรายการที่ต้องการเบิก แล้วส่งให้ Admin อนุมัติ' }}</p>
        </div>
        <div class="flex gap-2"><a href="{{ route('requisitions.index') }}" class="btn-secondary">ประวัติรายการ</a>@unless($isProduction)<button type="button" id="open-requisition-cart" class="btn-primary">รายการเบิก <span id="cart-count" class="ml-1 rounded-full bg-white/20 px-2 py-0.5">0</span></button>@endunless</div>
    </div>

    <form method="post" action="{{ route('requisitions.store') }}" id="requisition-form" class="{{ $isProduction ? 'grid items-start gap-4 xl:grid-cols-[340px_minmax(0,1fr)]' : 'space-y-4' }}">
        @csrf
        @if($isProduction)
        <aside class="space-y-4">
            <section class="panel">
                <div class="panel-header"><div><h3 class="section-title">ข้อมูลรายการ</h3><p class="section-subtitle">กรอกข้อมูลหลักให้ครบ</p></div></div>
                <div class="panel-body space-y-4">
                    <div>
                        <span class="label">ประเภท *</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($types as $type)
                            <label class="cursor-pointer">
                                <input class="peer sr-only" type="radio" name="request_type" value="{{ $type->value }}" @checked(old('request_type', $selectedType->value) === $type->value)>
                                <span class="block rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-center text-xs font-semibold text-slate-600 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700">{{ $type->label() }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <label><span class="label">คลังสินค้า *</span><select name="warehouse_id" id="warehouse" class="select" required><option value="">— เลือกคลัง —</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $warehouses->first()?->id) === (string) $warehouse->id)>{{ $warehouse->code }} — {{ $warehouse->name }}</option>@endforeach</select></label>
                    <label><span class="label">แผนก / หน่วยงาน</span><input class="input" name="department_name" value="{{ old('department_name') }}" placeholder="เช่น ฝ่ายผลิต"></label>
                    <label><span class="label">วัตถุประสงค์</span><input class="input" name="purpose" value="{{ old('purpose') }}" placeholder="ระบบจะใช้ชื่อประเภทหากไม่ระบุ"></label>
                    <label><span class="label">หมายเหตุ</span><textarea class="input" name="note" rows="3" placeholder="รายละเอียดเพิ่มเติม">{{ old('note') }}</textarea></label>
                </div>
            </section>

            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-xs leading-5 text-blue-800">
                @if(auth()->user()->isAdmin())
                    <strong class="block">Admin ทำรายการ</strong>
                    ระบบจะอนุมัติและปรับสต็อกทันทีหลังบันทึก
                @else
                    <strong class="block">ขั้นตอนหลังส่งคำขอ</strong>
                    Admin อนุมัติ → ระบบตัดสต็อก → เปิดดูใบเบิกที่อนุมัติแล้ว
                @endif
            </div>
        </aside>
        @else
        <input type="hidden" name="request_type" id="request-type" value="{{ old('request_type', $selectedType->value) }}">
        @endif

        <section class="panel min-w-0">
            <div class="panel-header">
                <div><h3 class="section-title">{{ $isProduction ? 'ผลผลิตและสูตรที่ใช้' : 'รายการสินค้าทั้งหมด' }}</h3><p class="section-subtitle" id="items-help"></p></div>
            </div>
            <div class="panel-body">
                @if($isProduction)
                <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_180px]">
                    <label><span class="label">สินค้าที่ผลิต *</span><select class="select" name="target_product_id" id="target-product" required><option value="">— เลือกสินค้าที่มีสูตร —</option></select></label>
                    <label><span class="label">จำนวนที่ผลิต *</span><input class="input text-right font-semibold" name="target_quantity" id="target-quantity" type="number" min="0.0001" step="0.0001" value="{{ old('target_quantity', 1) }}" required></label>
                </div>
                <div id="bom-preview" class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-sm text-slate-500"></div>
                @else
                <div class="mb-4 grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_240px]">
                    <label><span class="label">ค้นหาสินค้า</span><input id="product-search" class="input" placeholder="ค้นหารหัสหรือชื่อสินค้า"></label>
                    <label><span class="label">ประเภทสินค้า</span><select id="product-type-filter" class="select"><option value="">ทั้งหมด</option><option value="PART">PART</option><option value="SUPPLY">สิ้นเปลือง</option><option value="WIP">WIP</option><option value="FG">FG</option></select></label>
                    <label><span class="label">คลังสินค้า</span><select name="warehouse_id" id="warehouse" class="select" required>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $warehouses->first()?->id) === (string) $warehouse->id)>{{ $warehouse->code }} — {{ $warehouse->name }}</option>@endforeach</select></label>
                </div>
                <div class="table-wrap rounded-xl border border-slate-200">
                    <table class="data-table"><thead><tr><th>สินค้า</th><th>ประเภท</th><th class="text-right">คงเหลือ</th><th class="text-right">จัดการ</th></tr></thead><tbody id="product-catalog"></tbody></table>
                </div>
                <div id="catalog-empty" class="hidden rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">ไม่พบสินค้าที่ค้นหา</div>
                @endif
            </div>
            @if($isProduction)
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                <p class="text-xs text-slate-500">ตรวจสอบสินค้า คลัง และจำนวนก่อนยืนยัน</p>
                <button class="btn-primary px-6">ยืนยันการผลิต</button>
            </div>
            @endif
        </section>

        @unless($isProduction)
        <div id="requisition-cart-backdrop" class="fixed inset-0 z-[60] hidden bg-slate-950/40 backdrop-blur-sm"></div>
        <aside id="requisition-cart" class="fixed inset-y-0 right-0 z-[70] flex w-full max-w-2xl translate-x-full flex-col bg-white shadow-2xl transition-transform duration-300" role="dialog" aria-modal="true" aria-labelledby="requisition-cart-title">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><h3 id="requisition-cart-title" class="text-lg font-bold text-slate-900">รายการเบิกสินค้า</h3><p class="text-xs text-slate-500">ตรวจสินค้า จำนวน และข้อมูลใบเบิกก่อนส่ง</p></div><button type="button" id="close-requisition-cart" class="grid size-10 place-items-center rounded-lg text-xl text-slate-500 hover:bg-slate-100" aria-label="ปิดรายการเบิก">×</button></div>
            <div class="flex-1 space-y-5 overflow-y-auto p-5">
                <section class="rounded-xl border border-slate-200 p-4">
                    <div class="mb-4"><span class="label">ประเภทใบเบิก</span><strong id="selected-type-label" class="block text-sm text-slate-800">เลือกสินค้าจากรายการ</strong></div>
                    <div class="grid gap-3 sm:grid-cols-2"><label><span class="label">แผนก / หน่วยงาน</span><input class="input" name="department_name" value="{{ old('department_name') }}" placeholder="เช่น ฝ่ายผลิต"></label><label><span class="label">วัตถุประสงค์</span><input class="input" name="purpose" value="{{ old('purpose') }}" placeholder="ระบบจะใช้ชื่อประเภทหากไม่ระบุ"></label></div>
                    <label class="mt-3 block"><span class="label">หมายเหตุ</span><textarea class="input" name="note" rows="2" placeholder="รายละเอียดเพิ่มเติม">{{ old('note') }}</textarea></label>
                </section>
                <section><div class="mb-3 flex items-center justify-between"><div><h4 class="text-sm font-semibold text-slate-800">สินค้าที่เลือก</h4><p class="text-xs text-slate-400">แก้ไขจำนวนหรือลบรายการได้</p></div><span class="badge-blue"><span id="drawer-cart-count">0</span> รายการ</span></div><div id="item-list" class="space-y-2"></div><div id="empty-items" class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">ยังไม่ได้เลือกสินค้า กด “เบิก” จากรายการสินค้า</div></section>
            </div>
            <div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4"><button type="button" id="continue-shopping" class="btn-secondary">เลือกสินค้าต่อ</button><button class="btn-primary px-6">ส่งคำขอเบิก</button></div>
        </aside>
        @endunless
    </form>
</div>
@endsection

@push('scripts')
<script>
const requisitionProducts = @json($productOptions);
const requisitionMode = @json($formMode);
const requisitionRows = @json(old('items', []));
let selectedTarget = @json((string) old('target_product_id', ''));
const typeInputs = [...document.querySelectorAll('input[type="radio"][name="request_type"]')];
const requestTypeInput = document.getElementById('request-type');
const warehouseInput = document.getElementById('warehouse');
const itemList = document.getElementById('item-list');
const targetInput = document.getElementById('target-product');
const catalogBody = document.getElementById('product-catalog');
const searchInput = document.getElementById('product-search');
const typeFilter = document.getElementById('product-type-filter');
const cartDrawer = document.getElementById('requisition-cart');
const cartBackdrop = document.getElementById('requisition-cart-backdrop');
const issueQueueUrl = @json(route('requisitions.issues'));
const isAdmin = @json(auth()->user()->isAdmin());

const escapeValue = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[character]));
const requestTypeByProduct = {PART:'ISSUE_PART', SUPPLY:'ISSUE_SUPPLY', WIP:'ISSUE_WIP', FG:'ISSUE_FG'};
const typeLabels = {PART:'PART', SUPPLY:'สิ้นเปลือง', WIP:'WIP', FG:'FG'};
const requestLabels = {ISSUE_PART:'เบิก PART', ISSUE_SUPPLY:'เบิกสิ้นเปลือง', ISSUE_WIP:'เบิก WIP', ISSUE_FG:'เบิก FG'};
const selectedType = () => requestTypeInput?.value ?? typeInputs.find(input => input.checked)?.value ?? (requisitionMode === 'production' ? 'BUILD_WIP' : 'ISSUE_PART');
const outputType = () => selectedType() === 'BUILD_WIP' ? 'WIP' : 'FG';
const productById = id => requisitionProducts.find(product => String(product.id) === String(id));
const outputPool = () => requisitionProducts.filter(product => product.type === outputType() && product.components.length);

function imageFor(product) {
    return product?.image
        ? `<img src="${product.image}" class="size-10 shrink-0 rounded-lg border border-slate-200 bg-white object-cover" alt="">`
        : '<span class="grid size-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-400">□</span>';
}

function balanceFor(product) {
    if (!product || !warehouseInput.value) return '—';
    return `${escapeValue(product.balances[String(warehouseInput.value)] ?? '0')} ${escapeValue(product.unit)}`;
}

function syncRequestType() {
    if (!requestTypeInput) return;
    const firstProduct = productById(requisitionRows[0]?.product_id);
    requestTypeInput.value = firstProduct ? requestTypeByProduct[firstProduct.type] : 'ISSUE_PART';
    const label = document.getElementById('selected-type-label');
    if (label) label.textContent = firstProduct ? requestLabels[requestTypeInput.value] : 'เลือกสินค้าจากรายการ';
}

function openCart() {
    if (!cartDrawer) return;
    cartDrawer.classList.remove('translate-x-full');
    cartBackdrop?.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeCart() {
    if (!cartDrawer) return;
    cartDrawer.classList.add('translate-x-full');
    cartBackdrop?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function renderCatalog() {
    if (!catalogBody) return;
    const keyword = String(searchInput?.value ?? '').trim().toLocaleLowerCase('th');
    const filterType = typeFilter?.value ?? '';
    const products = requisitionProducts.filter(product => {
        const matchesKeyword = !keyword || `${product.code} ${product.name}`.toLocaleLowerCase('th').includes(keyword);
        return matchesKeyword && (!filterType || product.type === filterType);
    });
    const selectedIds = new Set(requisitionRows.map(row => String(row.product_id)));

    catalogBody.innerHTML = products.map(product => {
        const selected = selectedIds.has(String(product.id));
        return `<tr>
            <td><div class="flex min-w-52 items-center gap-3">${imageFor(product)}<div><strong class="block text-xs text-slate-800">${escapeValue(product.code)}</strong><span class="text-[11px] text-slate-500">${escapeValue(product.name)}</span></div></div></td>
            <td><span class="badge-slate">${escapeValue(typeLabels[product.type] ?? product.type)}</span></td>
            <td class="text-right"><strong class="text-sm text-slate-800">${balanceFor(product)}</strong></td>
            <td><div class="flex justify-end gap-2"><button type="button" class="${selected ? 'btn-secondary opacity-60' : 'btn-primary'}" data-add-product="${product.id}" ${selected ? 'disabled' : ''}>${selected ? 'เลือกแล้ว' : 'เบิก'}</button>${isAdmin ? `<a href="${issueQueueUrl}?product_id=${product.id}" class="btn-secondary">จ่าย</a>` : ''}</div></td>
        </tr>`;
    }).join('');
    document.getElementById('catalog-empty')?.classList.toggle('hidden', products.length > 0);
    catalogBody.closest('.table-wrap')?.classList.toggle('hidden', products.length === 0);
    catalogBody.querySelectorAll('[data-add-product]').forEach(button => button.addEventListener('click', () => addProduct(button.dataset.addProduct)));
}

function addProduct(productId) {
    const product = productById(productId);
    if (!product || requisitionRows.some(row => String(row.product_id) === String(productId))) return;
    const firstProduct = productById(requisitionRows[0]?.product_id);
    if (firstProduct && firstProduct.type !== product.type) {
        alert(`หนึ่งใบเบิกเลือกสินค้าได้ประเภทเดียวกัน กรุณาส่งใบเบิก ${typeLabels[firstProduct.type]} ก่อน`);
        return;
    }
    requisitionRows.push({product_id: product.id, quantity: 1});
    syncRequestType();
    renderItems();
    renderCatalog();
    openCart();
}

function renderItems() {
    if (!itemList) return;
    itemList.innerHTML = requisitionRows.map((row, index) => {
        const product = productById(row.product_id);
        return `<div class="grid items-center gap-2 rounded-lg border border-slate-200 bg-white p-3 md:grid-cols-[minmax(0,1fr)_150px_170px_44px]">
            <div><span class="label md:hidden">สินค้า</span><input type="hidden" name="items[${index}][product_id]" value="${escapeValue(product?.id)}"><div class="flex items-center gap-2">${imageFor(product)}<div><strong class="block text-xs text-slate-800">${escapeValue(product?.code)}</strong><span class="text-[10px] text-slate-400">${escapeValue(product?.name)}</span></div></div></div>
            <div><span class="label md:hidden">คงเหลือ</span><span class="block rounded-lg bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">${balanceFor(product)}</span></div>
            <label><span class="label md:hidden">จำนวนเบิก</span><input class="input text-right font-semibold" name="items[${index}][quantity]" data-item-quantity="${index}" type="number" min="0.0001" step="0.0001" value="${escapeValue(row.quantity || 1)}" required></label>
            <button type="button" class="grid size-9 place-items-center rounded-lg text-rose-500 hover:bg-rose-50" data-remove-item="${index}" aria-label="ลบ">×</button>
        </div>`;
    }).join('');
    document.getElementById('empty-items')?.classList.toggle('hidden', requisitionRows.length > 0);
    document.getElementById('cart-count')?.replaceChildren(document.createTextNode(String(requisitionRows.length)));
    document.getElementById('drawer-cart-count')?.replaceChildren(document.createTextNode(String(requisitionRows.length)));
    itemList.querySelectorAll('[data-item-quantity]').forEach(input => input.addEventListener('input', event => { requisitionRows[Number(event.target.dataset.itemQuantity)].quantity = event.target.value; }));
    itemList.querySelectorAll('[data-remove-item]').forEach(button => button.addEventListener('click', () => {
        requisitionRows.splice(Number(button.dataset.removeItem), 1);
        syncRequestType();
        renderItems();
        renderCatalog();
    }));
}

function renderProduction() {
    if (!targetInput) return;
    const options = outputPool();
    targetInput.innerHTML = '<option value="">— เลือกสินค้าที่มีสูตร —</option>' + options.map(product => `<option value="${product.id}">${escapeValue(product.code)} — ${escapeValue(product.name)}</option>`).join('');
    if (options.some(product => String(product.id) === selectedTarget)) targetInput.value = selectedTarget;
    previewFormula();
}

function previewFormula() {
    if (!targetInput) return;
    const product = productById(targetInput.value);
    const preview = document.getElementById('bom-preview');
    if (!product) {
        preview.innerHTML = `ยังไม่มี ${outputType()} ที่กำหนดสูตรไว้ กรุณาเพิ่มหรือแก้ไขสูตรจากเมนู “เพิ่มรายการสินค้า”`;
        return;
    }
    preview.innerHTML = `<div class="flex items-start gap-3">${imageFor(product)}<div><strong class="block text-slate-800">${escapeValue(product.code)} — ${escapeValue(product.name)}</strong><p class="mt-1 text-xs text-slate-500">ใช้ต่อ 1 ชิ้น: ${product.components.map(component => `${escapeValue(component.code)} ${escapeValue(component.name)} × ${escapeValue(component.quantity)} ${escapeValue(component.unit)}`).join(' · ')}</p></div></div>`;
}

function refreshForm(reset = false) {
    document.getElementById('items-help').textContent = requisitionMode === 'production'
        ? `${outputType()} จะถูกเพิ่มเข้าสต็อก และวัตถุดิบตามสูตรจะถูกตัดเมื่อ Admin อนุมัติ`
        : `แสดงสินค้า PART, สิ้นเปลือง, WIP และ FG ที่พร้อมใช้งาน`;
    if (reset) {
        requisitionRows.splice(0, requisitionRows.length);
        selectedTarget = '';
    }
    syncRequestType();
    renderItems();
    renderCatalog();
    renderProduction();
}

typeInputs.forEach(input => input.addEventListener('change', () => refreshForm(true)));
warehouseInput.addEventListener('change', () => { renderItems(); renderCatalog(); });
searchInput?.addEventListener('input', renderCatalog);
typeFilter?.addEventListener('change', renderCatalog);
targetInput?.addEventListener('change', event => { selectedTarget = event.target.value; previewFormula(); });
document.getElementById('open-requisition-cart')?.addEventListener('click', openCart);
document.getElementById('close-requisition-cart')?.addEventListener('click', closeCart);
document.getElementById('continue-shopping')?.addEventListener('click', closeCart);
cartBackdrop?.addEventListener('click', closeCart);
document.addEventListener('keydown', event => { if (event.key === 'Escape') closeCart(); });
document.getElementById('requisition-form').addEventListener('submit', event => {
    if (requisitionMode === 'withdraw' && requisitionRows.length === 0) {
        event.preventDefault();
        alert('กรุณาเลือกสินค้าที่ต้องการเบิกอย่างน้อย 1 รายการ');
    }
});
refreshForm();
</script>
@endpush
