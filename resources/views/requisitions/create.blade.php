@extends('layouts.app')

@php
    $isProduction = $formMode === 'production';
    $pageTitle = $isProduction ? 'ผลิตเข้า WIP / FG' : 'เบิกออกจากสต็อก';
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
    $initialProductId = request()->query('product_id');
@endphp

@section('title', $pageTitle)
@section('header', $pageTitle)

@section('content')
<div class="space-y-5">
    {{-- Top Header / Navigation --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('requisitions.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-900 transition-colors">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                กลับ
            </a>
            <h2 class="text-xl font-bold text-slate-900">{{ $pageTitle }}</h2>
        </div>
        @if($isProduction)
        <a href="{{ route('requisitions.index') }}" class="btn-secondary">ประวัติรายการ</a>
        @endif
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

        <section class="panel min-w-0">
            <div class="panel-header">
                <div><h3 class="section-title">ผลผลิตและสูตรที่ใช้</h3><p class="section-subtitle" id="items-help"></p></div>
            </div>
            <div class="panel-body">
                <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_180px]">
                    <label><span class="label">สินค้าที่ผลิต *</span><select class="select" name="target_product_id" id="target-product" required><option value="">— เลือกสินค้าที่มีสูตร —</option></select></label>
                    <label><span class="label">จำนวนที่ผลิต *</span><input class="input text-right font-semibold" name="target_quantity" id="target-quantity" type="number" min="0.0001" step="0.0001" value="{{ old('target_quantity', 1) }}" required></label>
                </div>
                <div id="bom-preview" class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-sm text-slate-500"></div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                <p class="text-xs text-slate-500">ตรวจสอบสินค้า คลัง และจำนวนก่อนยืนยัน</p>
                <button class="btn-primary px-6">ยืนยันการผลิต</button>
            </div>
        </section>
        @else

        {{-- Withdraw Requisition Form Layout (Matches User Screenshot) --}}
        <input type="hidden" name="request_type" id="request-type" value="{{ old('request_type', $selectedType->value) }}">
        <input type="hidden" name="warehouse_id" id="document-warehouse-id" value="{{ old('warehouse_id', $warehouses->first()?->id) }}">

        <section class="panel overflow-hidden border border-slate-200/90 bg-white shadow-sm rounded-2xl">
            {{-- Panel Header --}}
            <div class="panel-header flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="grid size-7 place-items-center rounded-lg bg-amber-50 text-amber-600 font-bold text-sm">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                    </span>
                    <h3 class="text-base font-bold text-slate-900">รายการเบิก</h3>
                </div>
                <button type="button" id="add-item-row" class="btn-secondary inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-blue-600 border-blue-200 bg-blue-50/50 hover:bg-blue-100 transition-colors">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    เพิ่มแถว
                </button>
            </div>

            {{-- Table Body --}}
            <div class="panel-body p-0 overflow-x-auto">
                <table class="data-table min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5 text-left min-w-[340px]">สินค้า *</th>
                            <th class="px-4 py-3.5 text-center w-36">สต็อกคงเหลือ</th>
                            <th class="px-4 py-3.5 text-left w-48">คลัง *</th>
                            <th class="px-4 py-3.5 text-left w-44">จำนวนเบิก / หน่วย *</th>
                            <th class="px-6 py-3.5 text-left min-w-[200px]">เหตุผล</th>
                            <th class="px-4 py-3.5 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="item-list" class="divide-y divide-slate-100 bg-white text-sm">
                    </tbody>
                </table>
                <div id="empty-items" class="hidden p-10 text-center text-sm font-medium text-slate-400">
                    ยังไม่มีรายการเบิก กด <strong>“+ เพิ่มแถว”</strong> เพื่อเริ่มเลือกสินค้า
                </div>
            </div>

            {{-- Panel Footer / Submit Buttons --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                <a href="{{ route('requisitions.index') }}" class="btn-secondary px-5 py-2 text-sm font-bold">ยกเลิก</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 via-amber-600 to-amber-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-500/25 hover:from-amber-600 hover:to-amber-700 active:scale-[0.98] transition-all">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    ยืนยันเบิกออก
                </button>
            </div>
        </section>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
const requisitionProducts = @json($productOptions);
const warehouses = @json($warehouses);
const requisitionMode = @json($formMode);
const oldRows = @json(old('items', []));
const initialProductId = @json($initialProductId);
const isAdmin = @json(auth()->user()->isAdmin());

let requisitionRows = Array.isArray(oldRows) && oldRows.length > 0
    ? oldRows
    : [{ product_id: initialProductId || '', quantity: 1, warehouse_id: warehouses[0]?.id || '', note: '' }];

let selectedTarget = @json((string) old('target_product_id', ''));
const requestTypeInput = document.getElementById('request-type');
const documentWarehouseInput = document.getElementById('document-warehouse-id');
const warehouseInput = document.getElementById('warehouse');
const itemList = document.getElementById('item-list');
const targetInput = document.getElementById('target-product');

const escapeValue = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[character]));
const typeLabels = {PART:'PART (อะไหล่ผลิต)', SUPPLY:'SUPPLY (วัสดุสิ้นเปลือง)', WIP:'WIP (งานประกอบ)', FG:'FG (สินค้าสำเร็จรูป)'};
const requestTypeByProduct = {PART:'ISSUE_PART', SUPPLY:'ISSUE_SUPPLY', WIP:'ISSUE_WIP', FG:'ISSUE_FG'};

const productById = id => requisitionProducts.find(product => String(product.id) === String(id));
const outputPool = () => requisitionProducts.filter(product => (selectedType() === 'BUILD_WIP' ? product.type === 'WIP' : product.type === 'FG') && product.components.length);
const selectedType = () => {
    const radio = document.querySelector('input[type="radio"][name="request_type"]:checked');
    return radio?.value ?? (requisitionMode === 'production' ? 'BUILD_WIP' : 'GENERAL_ISSUE');
};

function getStockBalance(productId, warehouseId) {
    const product = productById(productId);
    const targetWarehouseId = warehouseId || documentWarehouseInput?.value || warehouseInput?.value;
    if (!product || !targetWarehouseId) return '—';
    const balance = product.balances[String(targetWarehouseId)] ?? '0';
    return `${escapeValue(balance)} ${escapeValue(product.unit)}`;
}

function cartProductOptions(selectedId = '') {
    const grouped = { PART: [], SUPPLY: [], WIP: [], FG: [] };
    requisitionProducts.forEach(product => {
        if (grouped[product.type]) grouped[product.type].push(product);
    });

    let html = '<option value="">-- เลือก --</option>';
    ['PART', 'SUPPLY', 'WIP', 'FG'].forEach(type => {
        if (grouped[type].length > 0) {
            html += `<optgroup label="${typeLabels[type]}">`;
            grouped[type].forEach(product => {
                const selected = String(product.id) === String(selectedId) ? 'selected' : '';
                html += `<option value="${product.id}" ${selected}>[${escapeValue(product.code)}] ${escapeValue(product.name)}</option>`;
            });
            html += `</optgroup>`;
        }
    });

    return html;
}

function warehouseOptions(selectedId = '') {
    const defaultId = selectedId || documentWarehouseInput?.value || warehouses[0]?.id;
    return warehouses.map(w =>
        `<option value="${w.id}" ${String(w.id) === String(defaultId) ? 'selected' : ''}>${escapeValue(w.code)} — ${escapeValue(w.name)}</option>`
    ).join('');
}

function syncRequestType() {
    if (!requestTypeInput) return;
    const selectedProducts = requisitionRows.map(row => productById(row.product_id)).filter(Boolean);
    if (selectedProducts.length === 0) {
        requestTypeInput.value = 'GENERAL_ISSUE';
        return;
    }
    const firstType = selectedProducts[0].type;
    const allSameType = selectedProducts.every(p => p.type === firstType);
    requestTypeInput.value = allSameType ? (requestTypeByProduct[firstType] ?? 'GENERAL_ISSUE') : 'GENERAL_ISSUE';
}

function renderItems() {
    if (!itemList) return;
    if (requisitionRows.length === 0) {
        itemList.innerHTML = '';
        document.getElementById('empty-items')?.classList.remove('hidden');
        return;
    }
    document.getElementById('empty-items')?.classList.add('hidden');

    itemList.innerHTML = requisitionRows.map((row, index) => {
        const product = productById(row.product_id);
        const warehouseId = row.warehouse_id || documentWarehouseInput?.value || warehouses[0]?.id;

        return `<tr>
            <td class="px-6 py-3">
                <select class="select item-product-select w-full" data-row="${index}" name="items[${index}][product_id]" required>
                    ${cartProductOptions(row.product_id)}
                </select>
            </td>
            <td class="px-4 py-3 text-center">
                <span class="inline-block font-semibold text-slate-800 text-sm" id="balance-cell-${index}">
                    ${getStockBalance(row.product_id, warehouseId)}
                </span>
            </td>
            <td class="px-4 py-3">
                <select class="select item-warehouse-select w-full" data-row="${index}" name="items[${index}][warehouse_id]">
                    ${warehouseOptions(warehouseId)}
                </select>
            </td>
            <td class="px-4 py-3">
                <div class="flex items-center gap-1.5">
                    <input class="input font-semibold text-right w-24" name="items[${index}][quantity]" data-row="${index}" type="number" min="0.0001" step="0.0001" value="${escapeValue(row.quantity || 1)}" placeholder="0" required>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-2 rounded-xl shrink-0" id="unit-cell-${index}">
                        ${product ? escapeValue(product.unit) : '-- หน่วย --'}
                    </span>
                </div>
            </td>
            <td class="px-6 py-3">
                <input class="input text-sm w-full" name="items[${index}][note]" data-row="${index}" type="text" value="${escapeValue(row.note || '')}" placeholder="เช่น ส่งมอบลูกค้า">
            </td>
            <td class="px-4 py-3 text-center">
                <button type="button" class="grid size-9 place-items-center rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" onclick="removeRow(${index})" title="ลบรายการ">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </td>
        </tr>`;
    }).join('');

    bindEvents();
}

function bindEvents() {
    if (!itemList) return;
    itemList.querySelectorAll('.item-product-select').forEach(select => {
        select.addEventListener('change', event => {
            const index = Number(event.target.dataset.row);
            const productId = event.target.value;
            requisitionRows[index].product_id = productId;

            const product = productById(productId);
            const warehouseId = requisitionRows[index].warehouse_id || documentWarehouseInput?.value || warehouses[0]?.id;

            const balanceEl = document.getElementById(`balance-cell-${index}`);
            if (balanceEl) balanceEl.textContent = getStockBalance(productId, warehouseId);

            const unitEl = document.getElementById(`unit-cell-${index}`);
            if (unitEl) unitEl.textContent = product ? product.unit : '-- หน่วย --';

            syncRequestType();
        });
    });

    itemList.querySelectorAll('.item-warehouse-select').forEach(select => {
        select.addEventListener('change', event => {
            const index = Number(event.target.dataset.row);
            const warehouseId = event.target.value;
            requisitionRows[index].warehouse_id = warehouseId;
            if (index === 0 && documentWarehouseInput) documentWarehouseInput.value = warehouseId;

            const balanceEl = document.getElementById(`balance-cell-${index}`);
            if (balanceEl) balanceEl.textContent = getStockBalance(requisitionRows[index].product_id, warehouseId);
        });
    });

    itemList.querySelectorAll('input[name*="[quantity]"]').forEach(input => {
        input.addEventListener('input', event => {
            const index = Number(event.target.dataset.row);
            requisitionRows[index].quantity = event.target.value;
        });
    });

    itemList.querySelectorAll('input[name*="[note]"]').forEach(input => {
        input.addEventListener('input', event => {
            const index = Number(event.target.dataset.row);
            requisitionRows[index].note = event.target.value;
        });
    });
}

function addRow() {
    const defaultWarehouseId = documentWarehouseInput?.value || warehouses[0]?.id || '';
    requisitionRows.push({ product_id: '', quantity: 1, warehouse_id: defaultWarehouseId, note: '' });
    renderItems();
}

function removeRow(index) {
    requisitionRows.splice(index, 1);
    syncRequestType();
    renderItems();
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
        preview.innerHTML = `ยังไม่มีสินค้าที่กำหนดสูตรไว้ กรุณาเพิ่มหรือแก้ไขสูตรจากเมนู “เพิ่มรายการสินค้า”`;
        return;
    }
    preview.innerHTML = `<div class="flex items-start gap-3"><div><strong class="block text-slate-800">${escapeValue(product.code)} — ${escapeValue(product.name)}</strong><p class="mt-1 text-xs text-slate-500">ใช้ต่อ 1 ชิ้น: ${product.components.map(component => `${escapeValue(component.code)} ${escapeValue(component.name)} × ${escapeValue(component.quantity)} ${escapeValue(component.unit)}`).join(' · ')}</p></div></div>`;
}

document.getElementById('add-item-row')?.addEventListener('click', addRow);
document.getElementById('requisition-form')?.addEventListener('submit', event => {
    if (requisitionMode !== 'production' && requisitionRows.length === 0) {
        event.preventDefault();
        alert('กรุณาเพิ่มรายการสินค้าที่ต้องการเบิกอย่างน้อย 1 รายการ');
    }
});

if (requisitionMode === 'production') {
    document.querySelectorAll('input[type="radio"][name="request_type"]').forEach(radio => radio.addEventListener('change', renderProduction));
    targetInput?.addEventListener('change', event => { selectedTarget = event.target.value; previewFormula(); });
    renderProduction();
} else {
    renderItems();
    syncRequestType();
}
</script>
@endpush
