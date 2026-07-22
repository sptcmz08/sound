@extends('layouts.app')
@section('title', $config['title'])
@section('header', $config['title'])
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="{{ $config['direction']==='in' ? 'badge-green' : 'badge-amber' }}">
                    {{ $config['direction']==='in' ? '↑ รับเข้าสต็อก' : '↓ จ่ายออกจากสต็อก' }}
                </span>
                <span class="text-xs text-slate-400 font-semibold">• บันทึกประวัติและปรับปรุงยอดคงเหลือทันที</span>
            </div>
            <h2 class="page-title">{{ $config['title'] }}</h2>
            <p class="page-subtitle">{{ $config['subtitle'] }}</p>
        </div>
        <div class="flex gap-2">
            @if($operation === 'claim')
                <a href="{{ route('operations.create', 'waste') }}" class="btn-danger shadow-md shadow-rose-500/20">บันทึกของเสีย</a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn-secondary">กลับหน้าหลัก</a>
        </div>
    </div>

    <form id="operation-form" method="post" action="{{ route('operations.store', $operation) }}" class="space-y-6">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
        
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">ข้อมูลเอกสารการ{{ $config['title'] }}</h3>
                    <p class="text-xs text-slate-500">ระบุวันที่ คลังสินค้า และผู้ติดต่ออ้างอิง</p>
                </div>
                <span class="{{ $config['direction']==='in' ? 'badge-green' : 'badge-amber' }}">
                    {{ $config['direction']==='in' ? 'STOCK IN' : 'STOCK OUT' }}
                </span>
            </div>
            <div class="panel-body grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <label>
                    <span class="label">วันที่ทำรายการ *</span>
                    <input class="input" type="date" name="document_date" value="{{ old('document_date', today()->format('Y-m-d')) }}" required>
                </label>
                <label>
                    <span class="label">คลังสินค้าทำรายการ *</span>
                    <select class="select" name="warehouse_id" id="warehouse" required>
                        <option value="">— เลือกคลัง —</option>
                        @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id')==$warehouse->id)>
                            {{ $warehouse->code }} — {{ $warehouse->name }}
                        </option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="label">{{ $config['party_label'] }} {{ $config['party_required'] ? '*' : '' }}</span>
                    <input class="input" name="contact_name" value="{{ old('contact_name') }}" {{ $config['party_required'] ? 'required' : '' }} placeholder="ระบุชื่อ{{ $config['party_label'] }}">
                </label>
                <label>
                    <span class="label">เลขที่เอกสารอ้างอิง</span>
                    <input class="input" name="reference_no" value="{{ old('reference_no') }}" placeholder="เช่น PO-001 / INV-1234">
                </label>
                <label class="md:col-span-2 xl:col-span-4">
                    <span class="label">หมายเหตุ {{ $config['note_required'] ? '*' : '' }}</span>
                    <textarea class="input" name="note" rows="2" {{ $config['note_required'] ? 'required' : '' }} placeholder="ระบุรายละเอียดเพิ่มเติมสำหรับการตรวจสอบย้อนหลัง...">{{ old('note') }}</textarea>
                </label>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">รายการสินค้า</h3>
                    <p class="text-xs text-slate-500">เลือกสินค้าและ Option เสริม (ระบบจะตัดสต็อก WIP/PART ของ Option อัตโนมัติ)</p>
                </div>
                <button type="button" id="add-item" class="btn-primary shadow-lg shadow-blue-500/20">
                    + เพิ่มรายการสินค้า
                </button>
            </div>
            <div class="panel-body">
                <div class="table-wrap rounded-2xl border border-slate-200/80 bg-white">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="min-w-[340px]">สินค้า & ตัวเลือกเสริม (Option)</th>
                                <th>ประเภท</th>
                                <th class="text-right">คงเหลือในคลัง</th>
                                <th class="min-w-36">จำนวน</th>
                                @if($config['cost_input'])<th class="min-w-40">ต้นทุน/หน่วย (฿)</th>@endif
                                @if($config['price_input'])<th class="min-w-40">ราคาขาย/หน่วย (฿)</th><th class="text-right">รวมเป็นเงิน (฿)</th>@endif
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="item-rows"></tbody>
                    </table>
                </div>
                <div id="empty-items" class="rounded-2xl border-2 border-dashed border-slate-200/80 p-10 text-center text-slate-400 font-medium">
                    ยังไม่มีรายการสินค้า กดปุ่ม “+ เพิ่มรายการสินค้า” เพื่อเริ่มต้นบันทึก
                </div>
            </div>
        </section>

        {{-- Floating Sticky Footer --}}
        <div class="sticky bottom-6 z-20 flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-slate-200/90 bg-white/95 p-5 shadow-2xl backdrop-blur-md">
            <div class="flex items-center gap-3">
                <span class="inline-flex size-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <strong class="block text-xs uppercase tracking-wider text-slate-600">ระบบตรวจสอบอัตโนมัติ</strong>
                    <span class="text-xs text-slate-500">ตรวจสอบคงเหลือและป้องกันสต็อกติดลบทันทีที่บันทึก</span>
                </div>
            </div>
            <button class="{{ $config['direction']==='in' ? 'btn-success shadow-lg shadow-emerald-500/25' : 'btn-primary shadow-lg shadow-blue-500/25' }}">
                ✓ ยืนยัน{{ $config['title'] }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const operationProducts = @json($productOptions);
const operationRows = document.getElementById('item-rows');
const operationEmpty = document.getElementById('empty-items');
const operationWarehouse = document.getElementById('warehouse');
const hasCost = @json($config['cost_input']);
const hasPrice = @json($config['price_input']);
let operationIndex = 0;

function productOptions(selected='') {
    return '<option value="">— เลือกสินค้า —</option>'+operationProducts.map(product => `<option value="${product.id}" ${String(selected)===String(product.id)?'selected':''}>${product.code} — ${product.name}</option>`).join('');
}
function currentProduct(row) { return operationProducts.find(product => String(product.id)===String(row.querySelector('.product-select').value)); }

function renderRowOptions(row, index) {
    const product = currentProduct(row);
    const container = row.querySelector('.options-container');
    if (!container) return;
    
    // Capture current selected values to preserve them
    const selectedValues = {};
    container.querySelectorAll('.option-select').forEach((sel, gIdx) => {
        selectedValues[gIdx] = sel.value;
    });
    
    if (!product || !product.optionGroups || product.optionGroups.length === 0) {
        container.innerHTML = '';
        container.classList.add('hidden');
        return;
    }
    
    const warehouseId = operationWarehouse.value;
    container.classList.remove('hidden');
    
    container.innerHTML = '<div class="text-xs font-bold text-slate-500 mb-1 border-t border-slate-100 pt-2 mt-2">เลือกตัวเลือกเสริม:</div>' + product.optionGroups.map((group, gIdx) => {
        const requiredAttr = group.is_required ? 'required' : '';
        const savedValue = selectedValues[gIdx];
        const hasSelected = savedValue !== undefined && savedValue !== '';
        
        const defaultOption = group.is_required 
            ? `<option value="" disabled ${!hasSelected && !group.items.some(i => i.is_default) ? 'selected' : ''}>— เลือก ${group.name} * —</option>` 
            : `<option value="" ${!hasSelected && !group.items.some(i => i.is_default) ? 'selected' : ''}>— ไม่เลือก ${group.name} —</option>`;
            
        const optionsHtml = group.items.map(item => {
            const balance = item.balances[warehouseId] ?? '0';
            const priceLabel = Number(item.additional_price) > 0 ? ` (+${Number(item.additional_price).toLocaleString()} ฿)` : '';
            const isSelected = hasSelected ? (String(savedValue) === String(item.id)) : item.is_default;
            const selectedAttr = isSelected ? 'selected' : '';
            return `<option value="${item.id}" data-price="${item.additional_price}" ${selectedAttr}>${item.name} (${item.code}) [คงเหลือ: ${balance}]${priceLabel}</option>`;
        }).join('');
        
        return `
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs font-semibold text-slate-600 min-w-24">${group.name}:</span>
                <select class="select text-xs p-1 h-8 option-select flex-1" name="items[${index}][options][${gIdx}][product_option_item_id]" ${requiredAttr}>
                    ${defaultOption}
                    ${optionsHtml}
                </select>
            </div>
        `;
    }).join('');
    
    const recalculatePrice = () => {
        if (!hasPrice) return;
        let additionalPrice = 0;
        container.querySelectorAll('.option-select').forEach(sel => {
            const opt = sel.selectedOptions[0];
            if (opt && opt.dataset.price) {
                additionalPrice += Number(opt.dataset.price);
            }
        });
        const priceInput = row.querySelector('.unit-price');
        if (priceInput && !priceInput.dataset.edited) {
            const basePrice = Number(product.price || 0);
            priceInput.value = (basePrice + additionalPrice).toFixed(2);
            priceInput.dispatchEvent(new Event('input'));
        }
    };
    
    container.querySelectorAll('.option-select').forEach(select => {
        select.addEventListener('change', recalculatePrice);
    });
    
    recalculatePrice();
}

function refreshRow(row) {
    const product=currentProduct(row), balance=product?.balances?.[operationWarehouse.value] ?? '0';
    row.querySelector('.product-type').textContent=product?.type ?? '—';
    row.querySelector('.product-balance').textContent=product ? `${balance} ${product.unit}` : '—';
    const cost=row.querySelector('.unit-cost'); if(cost && !cost.dataset.edited) cost.value=product?.cost ?? '0';
    const price=row.querySelector('.unit-price'); if(price && !price.dataset.edited) price.value=product?.price ?? '0';
    
    const index = row.dataset.index;
    renderRowOptions(row, index);
    
    refreshTotal(row);
}
function refreshTotal(row) {
    const total=row.querySelector('.line-total'); if(!total)return;
    const quantity=Number(row.querySelector('.quantity').value||0), price=Number(row.querySelector('.unit-price').value||0);
    total.textContent=(quantity*price).toLocaleString('th-TH',{minimumFractionDigits:2,maximumFractionDigits:2});
}
function addOperationRow() {
    const row=document.createElement('tr'), index=operationIndex++;
    row.dataset.index = index;
    row.innerHTML=`<td>
        <select class="select product-select" name="items[${index}][product_id]" required>${productOptions()}</select>
        <div class="options-container hidden"></div>
    </td><td><span class="product-type badge-slate">—</span></td><td class="product-balance text-right font-bold">—</td><td><input class="input quantity" type="number" min="0.0001" step="0.0001" name="items[${index}][quantity]" value="1" required></td>${hasCost?`<td><input class="input unit-cost" type="number" min="0" step="0.0001" name="items[${index}][unit_cost]" value="0" required></td>`:''}${hasPrice?`<td><input class="input unit-price" type="number" min="0" step="0.0001" name="items[${index}][unit_price]" value="0" required></td><td class="line-total text-right font-bold">0.00</td>`:''}<td><button type="button" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" aria-label="ลบรายการ">✕</button></td>`;
    operationRows.appendChild(row); operationEmpty.classList.add('hidden');
    row.querySelector('.product-select').addEventListener('change',()=>refreshRow(row));
    row.querySelector('.quantity').addEventListener('input',()=>refreshTotal(row));
    row.querySelector('.unit-cost')?.addEventListener('input',event=>event.target.dataset.edited='1');
    row.querySelector('.unit-price')?.addEventListener('input',event=>{event.target.dataset.edited='1';refreshTotal(row)});
    row.querySelector('button').addEventListener('click',()=>{row.remove();operationEmpty.classList.toggle('hidden',operationRows.children.length>0)});
    row.querySelector('.product-select').focus();
}
document.getElementById('add-item').addEventListener('click',addOperationRow);
operationWarehouse.addEventListener('change',()=>[...operationRows.children].forEach(refreshRow));
document.getElementById('operation-form').addEventListener('submit',event=>{if(!operationRows.children.length){event.preventDefault();alert('กรุณาเพิ่มสินค้าอย่างน้อย 1 รายการ')}});
addOperationRow();
</script>
@endpush
