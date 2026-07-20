@extends('layouts.app')
@section('title', $config['title'])
@section('header', $config['title'])
@section('content')
<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div>
        <span class="mb-2 inline-flex rounded-full bg-blue-50 px-3 py-1 text-sm font-bold text-blue-700 ring-1 ring-blue-200">งานสต็อก</span>
        <h2 class="page-title">{{$config['title']}}</h2>
        <p class="page-subtitle">{{$config['subtitle']}}</p>
    </div>
    <div class="flex gap-2">
        @if($operation === 'claim')<a href="{{route('operations.create','waste')}}" class="btn-danger">บันทึกของเสีย</a>@endif
        <a href="{{route('dashboard')}}" class="btn-secondary">กลับหน้าหลัก</a>
    </div>
</div>

<form id="operation-form" method="post" action="{{route('operations.store', $operation)}}" class="space-y-6">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{$idempotencyKey}}">
    <section class="panel">
        <div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">ข้อมูลเอกสาร</h3><p class="text-sm text-slate-500">ระบุวันที่ คลัง และข้อมูลอ้างอิง</p></div><span class="{{$config['direction']==='in'?'badge-green':'badge-amber'}}">{{$config['direction']==='in'?'เข้า':'ออก'}}สต็อก</span></div>
        <div class="panel-body grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <label><span class="label">วันที่ *</span><input class="input" type="date" name="document_date" value="{{old('document_date', today()->format('Y-m-d'))}}" required></label>
            <label><span class="label">คลัง *</span><select class="select" name="warehouse_id" id="warehouse" required><option value="">— เลือกคลัง —</option>@foreach($warehouses as $warehouse)<option value="{{$warehouse->id}}" @selected(old('warehouse_id')==$warehouse->id)>{{$warehouse->code}} — {{$warehouse->name}}</option>@endforeach</select></label>
            <label><span class="label">{{$config['party_label']}} {{$config['party_required']?'*':''}}</span><input class="input" name="contact_name" value="{{old('contact_name')}}" {{$config['party_required']?'required':''}} placeholder="ชื่อ{{$config['party_label']}}"></label>
            <label><span class="label">เลขอ้างอิง</span><input class="input" name="reference_no" value="{{old('reference_no')}}" placeholder="PO / Invoice / เลขเคลม"></label>
            <label class="md:col-span-2 xl:col-span-4"><span class="label">รายละเอียด {{$config['note_required']?'*':''}}</span><textarea class="input" name="note" rows="3" {{$config['note_required']?'required':''}} placeholder="ระบุรายละเอียดที่ใช้ตรวจสอบย้อนหลัง">{{old('note')}}</textarea></label>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">รายการสินค้า</h3><p class="text-sm text-slate-500">เลือกสินค้าและกรอกจำนวนจริง</p></div><button type="button" id="add-item" class="btn-primary">+ เพิ่มรายการ</button></div>
        <div class="panel-body">
            <div class="table-wrap rounded-xl border border-slate-200">
                <table class="data-table">
                    <thead><tr><th class="min-w-80">สินค้า</th><th>ประเภท</th><th class="text-right">คงเหลือ</th><th class="min-w-40">จำนวน</th>@if($config['cost_input'])<th class="min-w-40">ต้นทุน/หน่วย</th>@endif @if($config['price_input'])<th class="min-w-40">ราคาขาย/หน่วย</th><th class="text-right">รวม</th>@endif<th></th></tr></thead>
                    <tbody id="item-rows"></tbody>
                </table>
            </div>
            <div id="empty-items" class="rounded-b-xl border-x border-b border-dashed border-slate-200 p-8 text-center text-slate-500">ยังไม่มีรายการ กด “เพิ่มรายการ” เพื่อเริ่มต้น</div>
        </div>
    </section>

    <div class="sticky bottom-4 flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-xl backdrop-blur">
        <p class="text-sm text-slate-500">ระบบตรวจยอดคงเหลือและป้องกันสต็อกติดลบอัตโนมัติ</p>
        <button class="{{$config['direction']==='in'?'btn-success':'btn-primary'}}">ยืนยัน{{$config['title']}}</button>
    </div>
</form>
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
function refreshRow(row) {
    const product=currentProduct(row), balance=product?.balances?.[operationWarehouse.value] ?? '0';
    row.querySelector('.product-type').textContent=product?.type ?? '—';
    row.querySelector('.product-balance').textContent=product ? `${balance} ${product.unit}` : '—';
    const cost=row.querySelector('.unit-cost'); if(cost && !cost.dataset.edited) cost.value=product?.cost ?? '0';
    const price=row.querySelector('.unit-price'); if(price && !price.dataset.edited) price.value=product?.price ?? '0';
    refreshTotal(row);
}
function refreshTotal(row) {
    const total=row.querySelector('.line-total'); if(!total)return;
    const quantity=Number(row.querySelector('.quantity').value||0), price=Number(row.querySelector('.unit-price').value||0);
    total.textContent=(quantity*price).toLocaleString('th-TH',{minimumFractionDigits:2,maximumFractionDigits:2});
}
function addOperationRow() {
    const row=document.createElement('tr'), index=operationIndex++;
    row.innerHTML=`<td><select class="select product-select" name="items[${index}][product_id]" required>${productOptions()}</select></td><td><span class="product-type badge-slate">—</span></td><td class="product-balance text-right font-bold">—</td><td><input class="input quantity" type="number" min="0.0001" step="0.0001" name="items[${index}][quantity]" value="1" required></td>${hasCost?`<td><input class="input unit-cost" type="number" min="0" step="0.0001" name="items[${index}][unit_cost]" value="0" required></td>`:''}${hasPrice?`<td><input class="input unit-price" type="number" min="0" step="0.0001" name="items[${index}][unit_price]" value="0" required></td><td class="line-total text-right font-bold">0.00</td>`:''}<td><button type="button" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" aria-label="ลบรายการ">✕</button></td>`;
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
