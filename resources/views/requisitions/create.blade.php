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
            <p class="page-subtitle">{{ $isProduction ? 'เลือกสินค้าที่กำหนดสูตรไว้ ระบบคำนวณวัตถุดิบและรับผลผลิตเข้าสต็อกให้อัตโนมัติ' : 'เลือกประเภท สินค้า และจำนวนในหน้าเดียว จากนั้นส่งให้ Admin อนุมัติ' }}</p>
        </div>
        <a href="{{ route('requisitions.index') }}" class="btn-secondary">ประวัติรายการ</a>
    </div>

    <form method="post" action="{{ route('requisitions.store') }}" id="requisition-form" class="grid items-start gap-4 xl:grid-cols-[340px_minmax(0,1fr)]">
        @csrf
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
                    <label><span class="label">คลังสินค้า *</span><select name="warehouse_id" id="warehouse" class="select" required><option value="">— เลือกคลัง —</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->code }} — {{ $warehouse->name }}</option>@endforeach</select></label>
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
                    Admin อนุมัติ → ระบบปรับสต็อก → พนักงานลงนาม → ดาวน์โหลด PDF
                @endif
            </div>
        </aside>

        <section class="panel min-w-0">
            <div class="panel-header">
                <div><h3 class="section-title">{{ $isProduction ? 'ผลผลิตและสูตรที่ใช้' : 'สินค้าที่ต้องการเบิก' }}</h3><p class="section-subtitle" id="items-help"></p></div>
                @unless($isProduction)<button type="button" id="add-item" class="btn-primary">+ เพิ่มสินค้า</button>@endunless
            </div>
            <div class="panel-body">
                @if($isProduction)
                <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_180px]">
                    <label><span class="label">สินค้าที่ผลิต *</span><select class="select" name="target_product_id" id="target-product" required><option value="">— เลือกสินค้าที่มีสูตร —</option></select></label>
                    <label><span class="label">จำนวนที่ผลิต *</span><input class="input text-right font-semibold" name="target_quantity" id="target-quantity" type="number" min="0.0001" step="0.0001" value="{{ old('target_quantity', 1) }}" required></label>
                </div>
                <div id="bom-preview" class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-sm text-slate-500"></div>
                @else
                <div class="hidden grid-cols-[minmax(0,1fr)_150px_170px_44px] gap-3 px-3 pb-2 text-[11px] font-semibold text-slate-400 md:grid"><span>สินค้า</span><span>คงเหลือ</span><span>จำนวนเบิก</span><span></span></div>
                <div id="item-list" class="space-y-2"></div>
                <div id="empty-items" class="hidden rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">ยังไม่มีรายการ กด “+ เพิ่มสินค้า” เพื่อเริ่มต้น</div>
                @endif
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                <p class="text-xs text-slate-500">ตรวจสอบสินค้า คลัง และจำนวนก่อนยืนยัน</p>
                <button class="btn-primary px-6">{{ $isProduction ? 'ยืนยันการผลิต' : 'ส่งคำขอเบิก' }}</button>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
const requisitionProducts = @json($productOptions);
const requisitionMode = @json($formMode);
const requisitionRows = @json(old('items', [['product_id' => '', 'quantity' => 1]]));
let selectedTarget = @json((string) old('target_product_id', ''));
const typeInputs = [...document.querySelectorAll('[name="request_type"]')];
const warehouseInput = document.getElementById('warehouse');
const itemList = document.getElementById('item-list');
const targetInput = document.getElementById('target-product');

const escapeValue = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[character]));
const selectedType = () => typeInputs.find(input => input.checked)?.value ?? (requisitionMode === 'production' ? 'BUILD_WIP' : 'ISSUE_PART');
const stockType = () => ({ISSUE_PART:'PART', ISSUE_SUPPLY:'SUPPLY', ISSUE_WIP:'WIP', ISSUE_FG:'FG'})[selectedType()];
const outputType = () => selectedType() === 'BUILD_WIP' ? 'WIP' : 'FG';
const productById = id => requisitionProducts.find(product => String(product.id) === String(id));
const productPool = () => requisitionProducts.filter(product => product.type === stockType());
const outputPool = () => requisitionProducts.filter(product => product.type === outputType() && product.components.length);

function imageFor(product) {
    return product?.image
        ? `<img src="${product.image}" class="size-10 shrink-0 rounded-lg border border-slate-200 bg-white object-cover" alt="">`
        : '<span class="grid size-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-400">□</span>';
}

function productOptions(selected = '') {
    return '<option value="">— เลือกสินค้า —</option>' + productPool().map(product => `<option value="${product.id}" ${String(product.id) === String(selected) ? 'selected' : ''}>${escapeValue(product.code)} — ${escapeValue(product.name)}</option>`).join('');
}

function balanceFor(product) {
    if (!product || !warehouseInput.value) return '—';
    return `${escapeValue(product.balances[String(warehouseInput.value)] ?? '0')} ${escapeValue(product.unit)}`;
}

function renderItems() {
    if (!itemList) return;
    itemList.innerHTML = requisitionRows.map((row, index) => {
        const product = productById(row.product_id);
        return `<div class="grid items-center gap-2 rounded-lg border border-slate-200 bg-white p-3 md:grid-cols-[minmax(0,1fr)_150px_170px_44px]">
            <label><span class="label md:hidden">สินค้า</span><div class="flex items-center gap-2">${imageFor(product)}<select class="select" name="items[${index}][product_id]" data-item-product="${index}" required>${productOptions(row.product_id)}</select></div></label>
            <div><span class="label md:hidden">คงเหลือ</span><span class="block rounded-lg bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">${balanceFor(product)}</span></div>
            <label><span class="label md:hidden">จำนวนเบิก</span><input class="input text-right font-semibold" name="items[${index}][quantity]" data-item-quantity="${index}" type="number" min="0.0001" step="0.0001" value="${escapeValue(row.quantity || 1)}" required></label>
            <button type="button" class="grid size-9 place-items-center rounded-lg text-rose-500 hover:bg-rose-50" data-remove-item="${index}" aria-label="ลบ">×</button>
        </div>`;
    }).join('');
    document.getElementById('empty-items')?.classList.toggle('hidden', requisitionRows.length > 0);
    itemList.querySelectorAll('[data-item-product]').forEach(select => select.addEventListener('change', event => { requisitionRows[Number(event.target.dataset.itemProduct)].product_id = event.target.value; renderItems(); }));
    itemList.querySelectorAll('[data-item-quantity]').forEach(input => input.addEventListener('input', event => { requisitionRows[Number(event.target.dataset.itemQuantity)].quantity = event.target.value; }));
    itemList.querySelectorAll('[data-remove-item]').forEach(button => button.addEventListener('click', () => { requisitionRows.splice(Number(button.dataset.removeItem), 1); renderItems(); }));
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
        : `แสดงเฉพาะสินค้า ${stockType()} ที่พร้อมเบิก`;
    if (reset) {
        requisitionRows.splice(0, requisitionRows.length, {product_id:'', quantity:1});
        selectedTarget = '';
    }
    renderItems();
    renderProduction();
}

typeInputs.forEach(input => input.addEventListener('change', () => refreshForm(true)));
warehouseInput.addEventListener('change', renderItems);
document.getElementById('add-item')?.addEventListener('click', () => { requisitionRows.push({product_id:'', quantity:1}); renderItems(); });
targetInput?.addEventListener('change', event => { selectedTarget = event.target.value; previewFormula(); });
refreshForm();
</script>
@endpush
