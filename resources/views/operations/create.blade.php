@extends('layouts.app')
@section('title', $config['title'])
@section('header', $config['title'])
@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="page-kicker">{{ $config['direction']==='in' ? 'รับสินค้าเข้าสต็อก (Stock In)' : 'จ่ายสินค้าออกจากสต็อก (Stock Out)' }}</span>
            <h2 class="page-title">{{ $config['title'] }}</h2>
            <p class="page-subtitle">{{ $config['subtitle'] }} — บันทึกพัสดุเข้าสต็อกและอัปเดตยอดคงเหลือทันที</p>
        </div>
        <div class="flex gap-2.5">
            @if($operation === 'claim')
                <a href="{{ route('operations.create', 'waste') }}" class="btn-danger">บันทึกของเสีย</a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn-secondary">กลับหน้าหลัก</a>
        </div>
    </div>

    {{-- Type Tabs Filter (for supplier receive) --}}
    @if($operation === 'supplier-receive')
    <div class="flex overflow-x-auto rounded-2xl border border-slate-200/90 bg-white p-1.5 shadow-sm gap-1">
        @foreach(['' => '📦 สินค้าทุกประเภท', 'PART' => '🔩 PART อะไหล่ผลิต', 'SUPPLY' => '🧪 SUPPLY วัสดุสิ้นเปลือง', 'WIP' => '⚙️ WIP งานประกอบ', 'FG' => '🔊 FG สินค้าสำเร็จรูป'] as $typeValue => $typeLabel)
        <a href="{{ route('operations.create', ['operation' => 'supplier-receive', 'type' => $typeValue ?: null]) }}" class="min-w-max flex-1 rounded-xl px-4 py-2.5 text-center text-xs font-bold transition-all duration-200 {{ request('type', '') === $typeValue ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium' }}">
            {{ $typeLabel }}
        </a>
        @endforeach
    </div>
    @endif

    <form id="operation-form" method="post" action="{{ route('operations.store', $operation) }}" class="space-y-6">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

        {{-- Document Header Info Card --}}
        <div class="panel">
            <div class="panel-header border-b border-slate-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="size-9 rounded-xl flex items-center justify-center font-bold text-white shadow-md {{ $config['direction']==='in' ? 'bg-gradient-to-br from-emerald-500 to-teal-600' : 'bg-gradient-to-br from-blue-500 to-indigo-600' }}">
                        {{ $config['direction']==='in' ? '📥' : '📤' }}
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">หัวข้อเอกสารและคลังสินค้า</h3>
                        <p class="text-xs text-slate-400">เลือกคลังและวันที่ทำรายการ (คลังหลักเลือกให้อัตโนมัติ)</p>
                    </div>
                </div>
                <span class="{{ $config['direction']==='in' ? 'badge-green' : 'badge-amber' }} text-xs px-3 py-1">
                    {{ $config['direction']==='in' ? 'STOCK IN · รับเข้า' : 'STOCK OUT · จ่ายออก' }}
                </span>
            </div>
            <div class="panel-body p-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="label">วันที่ทำรายการ *</label>
                        <input class="input" type="date" name="document_date" value="{{ old('document_date', today()->format('Y-m-d')) }}" required>
                    </div>
                    <div>
                        <label class="label">คลังสินค้า *</label>
                        <select class="select" name="warehouse_id" id="warehouse" required>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $warehouses->first()?->id) == $warehouse->id)>
                                🏬 {{ $warehouse->code }} — {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">{{ $config['party_label'] }} {{ $config['party_required'] ? '*' : '' }}</label>
                        <input class="input" name="contact_name" value="{{ old('contact_name') }}" {{ $config['party_required'] ? 'required' : '' }} placeholder="เช่น บริษัท ซัพพลายเออร์ จำกัด">
                    </div>
                    <div>
                        <label class="label">เลขที่ PO / เอกสารอ้างอิง</label>
                        <input class="input" name="reference_no" value="{{ old('reference_no') }}" placeholder="เช่น PO-2026-001">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="label">หมายเหตุ {{ $config['note_required'] ? '*' : '' }}</label>
                        <input class="input" name="note" value="{{ old('note') }}" {{ $config['note_required'] ? 'required' : '' }} placeholder="ระบุรายละเอียดเพิ่มเติมสำหรับการตรวจสอบย้อนหลัง (ถ้ามี)">
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Table Card --}}
        <div class="panel">
            <div class="panel-header border-b border-slate-100 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">รายการพัสดุ/สินค้าที่จะรับเข้า</h3>
                    <p class="text-xs text-slate-400">{{ $operation === 'sale' ? 'เลือก FG และ Option ที่ต้องการบันทึกขาย' : 'กดปุ่มเพิ่มรายการแล้วเลือกสินค้าจากรายชื่อ' }}</p>
                </div>
                <button type="button" id="add-item" class="btn-primary">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    + เพิ่มรายการสินค้า
                </button>
            </div>
            <div class="panel-body p-6">
                <div class="table-wrap rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-sm">
                    <table class="data-table">
                        <thead>
                            <tr class="bg-slate-50/80">
                                <th class="min-w-[340px] py-3.5">{{ $operation === 'sale' ? 'สินค้า FG และ Option' : 'ค้นหา/เลือกสินค้า' }}</th>
                                <th class="py-3.5">ประเภท</th>
                                <th class="py-3.5 text-right">คงเหลือปัจจุบัน</th>
                                <th class="min-w-36 py-3.5">จำนวนที่รับเข้า *</th>
                                @if($config['cost_input'])<th class="min-w-40 py-3.5">ต้นทุนต่อหน่วย (฿) *</th>@endif
                                @if($config['price_input'])<th class="min-w-40 py-3.5">ราคาขายต่อหน่วย (฿) *</th><th class="text-right py-3.5">รวมเป็นเงิน (฿)</th>@endif
                                <th class="py-3.5 text-center">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="item-rows" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
                <div id="empty-items" class="rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center text-xs font-semibold text-slate-400 mt-4 bg-slate-50/50">
                    ยังไม่มีรายการสินค้า — กดปุ่ม <span class="text-blue-600 font-bold">“+ เพิ่มรายการสินค้า”</span> เพื่อเริ่มต้นเพิ่มสินค้าที่จะรับเข้า
                </div>
            </div>
        </div>

        {{-- Summary Action Footer --}}
        <div class="panel flex flex-wrap items-center justify-between gap-4 p-5">
            <div class="flex items-center gap-6 text-xs text-slate-600 font-medium">
                <span>จำนวนรายการ: <strong id="summary-lines" class="ml-1 text-base font-bold text-slate-900">0</strong> รายการ</span>
                @if($config['price_input'])
                    <span>ยอดรวมทั้งสิ้น: <strong id="summary-total" class="ml-1.5 text-xl font-bold text-blue-700">฿0.00</strong></span>
                @endif
            </div>
            <button class="{{ $config['direction']==='in' ? 'btn-success' : 'btn-primary' }} px-8 py-3 text-sm font-bold shadow-lg shadow-emerald-500/20">
                {{ $config['direction']==='in' ? '✔ บันทึกรับสินค้าเข้าสต็อก' : '✔ บันทึกจ่ายสินค้าออก' }}
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
const optionsEnabled = @json($operation === 'sale');
let operationIndex = 0;
const escapeOperation = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[character]));

function productOptions(selected='') {
    return '<option value="">— เลือกสินค้า —</option>'+operationProducts.map(product => `<option value="${product.id}" ${String(selected)===String(product.id)?'selected':''}>[${escapeOperation(product.type)}] ${escapeOperation(product.code)} — ${escapeOperation(product.name)}</option>`).join('');
}
function currentProduct(row) { return operationProducts.find(product => String(product.id)===String(row.querySelector('.product-select').value)); }

function renderRowOptions(row, index) {
    const product = currentProduct(row);
    const container = row.querySelector('.options-container');
    if (!container) return;
    
    const selectedValues = {};
    container.querySelectorAll('.option-select').forEach((sel, gIdx) => {
        selectedValues[gIdx] = sel.value;
    });
    
    if (!optionsEnabled || !product || !product.optionGroups || product.optionGroups.length === 0) {
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
            return `<option value="${item.id}" data-price="${item.additional_price}" ${selectedAttr}>${escapeOperation(item.name)} (${escapeOperation(item.code)}) [คงเหลือ: ${escapeOperation(balance)}]${priceLabel}</option>`;
        }).join('');
        
        return `
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs font-semibold text-slate-600 min-w-24">${escapeOperation(group.name)}:</span>
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
    const typeBadge = product ? `<span class="badge-${product.type.toLowerCase()}">${product.type}</span>` : '—';
    row.querySelector('.product-type').innerHTML = typeBadge;
    row.querySelector('.product-balance').textContent = product ? `${balance} ${product.unit}` : '—';
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
    refreshSummary();
}
function refreshSummary() {
    document.getElementById('summary-lines').textContent=operationRows.children.length;
    const totalElement=document.getElementById('summary-total');
    if(totalElement){
        const total=[...operationRows.children].reduce((sum,row)=>sum+(Number(row.querySelector('.quantity')?.value||0)*Number(row.querySelector('.unit-price')?.value||0)),0);
        totalElement.textContent=`฿${total.toLocaleString('th-TH',{minimumFractionDigits:2,maximumFractionDigits:2})}`;
    }
}
function addOperationRow() {
    const row=document.createElement('tr'), index=operationIndex++;
    row.dataset.index = index;
    row.innerHTML=`<td class="py-3">
        <select class="select product-select text-xs font-bold" name="items[${index}][product_id]" required>${productOptions()}</select>
        <div class="options-container hidden"></div>
    </td>
    <td class="py-3"><span class="product-type">—</span></td>
    <td class="product-balance text-right font-bold text-slate-800 py-3">—</td>
    <td class="py-3"><input class="input quantity font-bold text-slate-900" type="number" min="0.0001" step="0.0001" name="items[${index}][quantity]" value="1" required></td>
    ${hasCost?`<td class="py-3"><input class="input unit-cost font-semibold" type="number" min="0" step="0.0001" name="items[${index}][unit_cost]" value="0" required></td>`:''}
    ${hasPrice?`<td class="py-3"><input class="input unit-price font-semibold" type="number" min="0" step="0.0001" name="items[${index}][unit_price]" value="0" required></td><td class="line-total text-right font-bold text-blue-700 py-3">0.00</td>`:''}
    <td class="text-center py-3"><button type="button" class="rounded-xl p-2 text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition-colors" title="ลบรายการ">✕</button></td>`;
    operationRows.appendChild(row); operationEmpty.classList.add('hidden'); refreshSummary();
    row.querySelector('.product-select').addEventListener('change',()=>refreshRow(row));
    row.querySelector('.quantity').addEventListener('input',()=>refreshTotal(row));
    row.querySelector('.unit-cost')?.addEventListener('input',event=>event.target.dataset.edited='1');
    row.querySelector('.unit-price')?.addEventListener('input',event=>{event.target.dataset.edited='1';refreshTotal(row)});
    row.querySelector('button').addEventListener('click',()=>{row.remove();operationEmpty.classList.toggle('hidden',operationRows.children.length>0);refreshSummary()});
    row.querySelector('.product-select').focus();
}
document.getElementById('add-item').addEventListener('click',addOperationRow);
operationWarehouse.addEventListener('change',()=>[...operationRows.children].forEach(refreshRow));
document.getElementById('operation-form').addEventListener('submit',event=>{if(!operationRows.children.length){event.preventDefault();alert('กรุณาเพิ่มสินค้าอย่างน้อย 1 รายการ')}});
addOperationRow();
</script>
@endpush
