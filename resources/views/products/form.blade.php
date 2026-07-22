@extends('layouts.app')
@section('title', $product->exists ? 'แก้ไขสินค้า' : 'เพิ่มสินค้า')
@section('header', $product->exists ? 'แก้ไขสินค้าและสูตร' : 'เพิ่มสินค้าและสูตร')
@section('content')
@php
    $currentType = old('product_type', $product->product_type?->value ?? 'PART');
    $oldComponents = old('components', $product->exists ? $product->components->map(fn($c) => ['product_id' => $c->id, 'quantity' => $c->pivot->quantity])->values()->all() : []);
    $componentJson = $componentProducts->map(fn($p) => ['id' => $p->id, 'code' => $p->code, 'name' => $p->name, 'type' => $p->product_type->value, 'unit' => $p->unit->name])->values()->toJson();
    $oldOptionGroups = old('option_groups', $product->exists ? $product->optionGroups->map(fn($g) => [
        'id' => $g->id,
        'name' => $g->name,
        'is_required' => $g->is_required,
        'items' => $g->items->map(fn($i) => [
            'id' => $i->id,
            'option_product_id' => $i->option_product_id,
            'quantity' => $i->quantity,
            'additional_price' => $i->additional_price,
            'is_default' => $i->is_default,
        ])->values()->all()
    ])->values()->all() : []);
@endphp
<div class="mb-7 flex items-start justify-between gap-4">
    <div><span class="page-kicker">ข้อมูลหลัก {{ $currentType }}</span><h2 class="page-title">{{ $product->exists ? 'แก้ไข '.$currentType : 'เพิ่ม '.$currentType }}</h2><p class="page-subtitle">PART คือชิ้นส่วนที่ระบุจำนวนใน BOM ได้ ส่วน SUPPLY คือวัสดุสิ้นเปลืองที่ไม่ผูกจำนวนต่อชิ้นงาน</p></div>
    <a href="{{route('products.index')}}" class="btn-secondary">กลับ</a>
</div>
<form method="post" action="{{$product->exists ? route('products.update',$product) : route('products.store')}}" enctype="multipart/form-data" class="space-y-6" id="product-form">
    @csrf @if($product->exists) @method('PUT') @endif
    <section class="panel"><div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">ข้อมูลสินค้า</h3><p class="mt-1 text-slate-500">เลือกประเภทสินค้าที่ถูกต้องเพื่อเริ่มจัดการ</p></div></div>
        <div class="panel-body grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <span class="label">รูปสินค้า</span>
                <div class="flex flex-wrap items-center gap-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="relative">
                        <x-product-image :product="$product" size="xl" id="current-product-image" />
                        <img id="new-product-image" class="hidden size-24 rounded-xl border border-blue-300 bg-white object-cover shadow-sm" alt="ตัวอย่างรูปสินค้าใหม่">
                    </div>
                    <label class="min-w-64 flex-1"><span class="font-bold text-slate-900">เลือกรูปจากเครื่อง</span><input id="product-image-input" class="mt-2 block w-full text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2.5 file:font-semibold file:text-white hover:file:bg-blue-700" type="file" name="image" accept="image/jpeg,image/png,image/webp"><span class="mt-2 block text-sm text-slate-500">รองรับ JPG, PNG และ WEBP ขนาดไม่เกิน 2 MB</span></label>
                </div>
            </div>
            <label><span class="label">รหัสสินค้า *</span><input name="code" class="input" value="{{old('code',$product->code)}}" required></label>
            <label><span class="label">ชื่อสินค้า *</span><input name="name" class="input" value="{{old('name',$product->name)}}" required></label>
            <label><span class="label">ประเภท *</span><select name="product_type" id="product-type" class="select" required><option value="PART" @selected($currentType==='PART')>PART (อะไหล่/ชิ้นส่วนผลิต - กำหนดจำนวนได้ชัดเจน)</option><option value="SUPPLY" @selected($currentType==='SUPPLY')>SUPPLY (วัสดุสิ้นเปลือง - ไม่ระบุจำนวนต่อชิ้นงาน)</option><option value="WIP" @selected($currentType==='WIP')>WIP (งานระหว่างประกอบ)</option><option value="FG" @selected($currentType==='FG')>FG (สินค้าสำเร็จรูป)</option></select></label>
            <label><span class="label">หน่วยนับ *</span><select name="unit_id" class="select" required>@foreach($units as $unit)<option value="{{$unit->id}}" @selected(old('unit_id',$product->unit_id)==$unit->id)>{{$unit->name}} ({{$unit->code}})</option>@endforeach</select></label>
            <label><span class="label">จุดเตือนสต็อกต่ำ</span><input type="number" min="0" step="0.0001" name="minimum_stock" class="input" value="{{old('minimum_stock',$product->minimum_stock ?? 0)}}" required></label>
            <label><span class="label">ต้นทุนมาตรฐาน / หน่วย *</span><input type="number" min="0" step="0.0001" name="standard_cost" class="input" value="{{old('standard_cost',$product->standard_cost ?? 0)}}" required></label>
            <label><span class="label">ราคาขายแนะนำ / หน่วย *</span><input type="number" min="0" step="0.0001" name="sale_price" class="input" value="{{old('sale_price',$product->sale_price ?? 0)}}" required></label>
            <label><span class="label">ที่เก็บ</span><input name="location_text" class="input" value="{{old('location_text',$product->location_text)}}" placeholder="เช่น ชั้น A-02"></label>
            <label class="md:col-span-2"><span class="label">หมายเหตุ</span><textarea name="note" class="input" rows="3">{{old('note',$product->note)}}</textarea></label>
            <label class="flex items-center gap-3"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="size-5 rounded" @checked(old('is_active',$product->exists ? $product->is_active : true))><span class="font-semibold">เปิดใช้งาน</span></label>
        </div>
    </section>

    <div id="fg-config-tabs" class="hidden rounded-2xl border border-violet-200 bg-violet-50 p-2">
        <div class="grid gap-2 sm:grid-cols-2">
            <button type="button" data-fg-tab="recipe" class="rounded-xl px-5 py-3 text-left font-bold transition">
                สูตรผลิต FG (BOM)
                <small class="mt-1 block font-normal">ส่วนประกอบหลัก WIP/PART ที่ใช้ทุกชิ้น</small>
            </button>
            <button type="button" data-fg-tab="options" class="rounded-xl px-5 py-3 text-left font-bold transition">
                Option สำหรับหน้าขาย
                <small class="mt-1 block font-normal">ตัวเลือก WIP/PART ที่ลูกค้าเลือกเพิ่ม</small>
            </button>
        </div>
    </div>

    <section class="panel" id="recipe-panel">
        <div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">สูตรส่วนประกอบต่อ 1 ชิ้น (BOM)</h3><p class="mt-1 text-slate-500" id="recipe-help"></p></div><button type="button" id="add-component" class="btn-secondary">+ เพิ่มส่วนประกอบ</button></div>
        <div class="panel-body space-y-3" id="component-list"></div>
    </section>

    <section class="panel border-2 border-violet-200" id="options-panel">
        <div class="panel-header bg-violet-50"><div><h3 class="section-title text-violet-950">ตัวเลือกเสริมสำหรับขาย (Option Groups)</h3><p class="section-subtitle text-violet-700">สร้างกลุ่ม เช่น รูปแบบการถือหรือระบบคาราโอเกะ แล้วเลือก WIP/PART ที่ต้องตัดสต็อกเมื่อขาย FG</p></div><button type="button" id="add-option-group" class="btn-primary">+ เพิ่มกลุ่ม Option</button></div>
        <div class="panel-body space-y-6" id="option-group-list"></div>
    </section>

    <div class="flex justify-end gap-3"><a href="{{route('products.index')}}" class="btn-secondary">ยกเลิก</a><button class="btn-primary">บันทึกสินค้าและสูตร</button></div>
</form>
@endsection
@push('scripts')
<script>
const products = {!! $componentJson !!};
const imageInput=document.getElementById('product-image-input'), newImage=document.getElementById('new-product-image'), currentImage=document.getElementById('current-product-image');
imageInput?.addEventListener('change',()=>{const file=imageInput.files?.[0];if(!file)return;newImage.src=URL.createObjectURL(file);newImage.classList.remove('hidden');currentImage?.classList.add('hidden')});

let rows = @json($oldComponents);
const list=document.getElementById('component-list'), type=document.getElementById('product-type'), panel=document.getElementById('recipe-panel');
const fgTabs=document.getElementById('fg-config-tabs');
let activeFgTab='recipe';

function updateConfigPanels(){
    const isFg=type.value==='FG';
    fgTabs.classList.toggle('hidden', !isFg);
    panel.classList.toggle('hidden', ['PART','SUPPLY'].includes(type.value) || (isFg && activeFgTab!=='recipe'));
    optPanel.classList.toggle('hidden', !isFg || activeFgTab!=='options');
    document.querySelectorAll('[data-fg-tab]').forEach(button=>{
        const active=button.dataset.fgTab===activeFgTab;
        button.className=`rounded-xl px-5 py-3 text-left font-bold transition ${active?'bg-white text-violet-900 shadow-sm ring-1 ring-violet-200':'text-violet-700 hover:bg-white/60'}`;
    });
}

function options(selected){const allowed=products.filter(p=>type.value==='WIP'?p.type==='PART':['PART','WIP'].includes(p.type));return '<option value="">เลือกส่วนประกอบ</option>'+allowed.map(p=>`<option value="${p.id}" ${String(selected)===String(p.id)?'selected':''}>${p.code} — ${p.name} (${p.type})</option>`).join('')}
function render(){document.getElementById('recipe-help').textContent=type.value=='WIP'?'WIP ใช้ PART เป็นส่วนประกอบ':'FG ใช้ได้ทั้ง PART และ WIP';list.innerHTML=rows.map((r,i)=>`<div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1fr_220px_auto]"><select class="select" name="components[${i}][product_id]" required>${options(r.product_id)}</select><input class="input" type="number" min="0.0001" step="0.0001" name="components[${i}][quantity]" value="${r.quantity||1}" placeholder="จำนวนต่อ 1 ชิ้น" required><button type="button" class="btn-danger" onclick="rows.splice(${i},1);render()">ลบ</button></div>`).join('')||'<div class="rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center text-slate-500">ยังไม่มีส่วนประกอบ กด “เพิ่มส่วนประกอบ”</div>';updateConfigPanels()}
type.addEventListener('change',()=>{rows=[];optionGroups=[];activeFgTab='recipe';render();renderOptions()});document.getElementById('add-component').addEventListener('click',()=>{rows.push({product_id:'',quantity:1});render()});

let optionGroups = @json($oldOptionGroups);
const optGroupList = document.getElementById('option-group-list'), optPanel = document.getElementById('options-panel');

function optionProductsOptions(selectedId) {
    return '<option value="">เลือกตัวเลือก (WIP/PART)</option>' + products.map(p => 
        `<option value="${p.id}" ${String(selectedId) === String(p.id) ? 'selected' : ''}>${p.code} — ${p.name} (${p.type})</option>`
    ).join('');
}

function renderOptions() {
    if (type.value !== 'FG') {
        optGroupList.innerHTML = '';
        updateConfigPanels();
        return;
    }
    
    optGroupList.innerHTML = optionGroups.map((g, gIdx) => {
        const itemsHtml = (g.items || []).map((item, iIdx) => `
            <div class="grid gap-3 items-end rounded-xl border border-slate-200 bg-white p-3 md:grid-cols-[1fr_120px_160px_100px_auto]">
                <input type="hidden" name="option_groups[${gIdx}][items][${iIdx}][id]" value="${item.id || ''}">
                <label>
                    <span class="text-xs text-slate-500 font-bold block mb-1">ตัวเลือกสินค้า *</span>
                    <select class="select" name="option_groups[${gIdx}][items][${iIdx}][option_product_id]" required>
                        ${optionProductsOptions(item.option_product_id)}
                    </select>
                </label>
                <label>
                    <span class="text-xs text-slate-500 font-bold block mb-1">จำนวนตัดสต็อก *</span>
                    <input class="input" type="number" min="0.0001" step="0.0001" name="option_groups[${gIdx}][items][${iIdx}][quantity]" value="${item.quantity || 1}" required>
                </label>
                <label>
                    <span class="text-xs text-slate-500 font-bold block mb-1">บวกราคาเพิ่ม (บาท) *</span>
                    <input class="input" type="number" min="0" step="0.01" name="option_groups[${gIdx}][items][${iIdx}][additional_price]" value="${item.additional_price || 0}" required>
                </label>
                <label class="flex flex-col items-center justify-center h-full">
                    <span class="text-xs text-slate-500 font-bold block mb-2 text-center w-full">เริ่มต้น</span>
                    <input type="hidden" name="option_groups[${gIdx}][items][${iIdx}][is_default]" value="0">
                    <input type="checkbox" name="option_groups[${gIdx}][items][${iIdx}][is_default]" value="1" ${item.is_default ? 'checked' : ''} class="size-5 rounded" onclick="handleDefaultCheck(${gIdx}, ${iIdx}, this)">
                </label>
                <div class="flex items-center h-full pt-6">
                    <button type="button" class="btn-danger py-2 px-3" onclick="removeOptionItem(${gIdx}, ${iIdx})">ลบ</button>
                </div>
            </div>
        `).join('') || '<div class="text-center text-slate-400 py-3">ยังไม่มีตัวเลือกในกลุ่มนี้</div>';

        return `
            <div class="rounded-2xl border-2 border-slate-200 bg-slate-50 p-5 space-y-4">
                <input type="hidden" name="option_groups[${gIdx}][id]" value="${g.id || ''}">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-3">
                    <div class="flex-1 min-w-[250px] flex items-center gap-4">
                        <label class="flex-1">
                            <span class="text-sm font-bold text-slate-900">ชื่อกลุ่ม Option *</span>
                            <input class="input mt-1" name="option_groups[${gIdx}][name]" value="${g.name || ''}" placeholder="เช่น มือจับ, สไตล์หิ้ว, คาราโอเกะ" required>
                        </label>
                        <label class="flex items-center gap-2 pt-6">
                            <input type="hidden" name="option_groups[${gIdx}][is_required]" value="0">
                            <input type="checkbox" name="option_groups[${gIdx}][is_required]" value="1" ${g.is_required ? 'checked' : ''} class="size-5 rounded">
                            <span class="text-sm font-bold text-slate-700">บังคับเลือก</span>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="btn-secondary text-sm px-3 py-1.5" onclick="addOptionItem(${gIdx})">+ เพิ่มตัวเลือก</button>
                        <button type="button" class="btn-danger text-sm px-3 py-1.5" onclick="removeOptionGroup(${gIdx})">ลบกลุ่มนี้</button>
                    </div>
                </div>
                <div class="space-y-3">
                    ${itemsHtml}
                </div>
            </div>
        `;
    }).join('') || '<div class="rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center text-slate-500">ยังไม่มีกลุ่ม Option สำหรับสินค้าสำเร็จรูปนี้</div>';
    updateConfigPanels();
}

function handleDefaultCheck(gIdx, iIdx, checkbox) {
    syncStateFromDOM();
    if (checkbox.checked) {
        optionGroups[gIdx].items.forEach((item, index) => {
            item.is_default = (index === iIdx);
        });
    } else {
        optionGroups[gIdx].items[iIdx].is_default = false;
    }
    renderOptions();
}

function addOptionGroup() {
    syncStateFromDOM();
    optionGroups.push({ id: '', name: '', is_required: false, items: [] });
    renderOptions();
}

function removeOptionGroup(gIdx) {
    syncStateFromDOM();
    optionGroups.splice(gIdx, 1);
    renderOptions();
}

function addOptionItem(gIdx) {
    syncStateFromDOM();
    if (!optionGroups[gIdx].items) optionGroups[gIdx].items = [];
    optionGroups[gIdx].items.push({ id: '', option_product_id: '', quantity: 1, additional_price: 0, is_default: false });
    renderOptions();
}

function removeOptionItem(gIdx, iIdx) {
    syncStateFromDOM();
    optionGroups[gIdx].items.splice(iIdx, 1);
    renderOptions();
}

function syncStateFromDOM() {
    optionGroups.forEach((g, gIdx) => {
        const nameInput = document.querySelector(`input[name="option_groups[${gIdx}][name]"]`);
        if (nameInput) g.name = nameInput.value;
        const reqInput = document.querySelector(`input[name="option_groups[${gIdx}][is_required]"][type="checkbox"]`);
        if (reqInput) g.is_required = reqInput.checked;

        (g.items || []).forEach((item, iIdx) => {
            const prodInput = document.querySelector(`select[name="option_groups[${gIdx}][items][${iIdx}][option_product_id]"]`);
            if (prodInput) item.option_product_id = prodInput.value;
            const qtyInput = document.querySelector(`input[name="option_groups[${gIdx}][items][${iIdx}][quantity]"]`);
            if (qtyInput) item.quantity = qtyInput.value;
            const priceInput = document.querySelector(`input[name="option_groups[${gIdx}][items][${iIdx}][additional_price]"]`);
            if (priceInput) item.additional_price = priceInput.value;
            const defInput = document.querySelector(`input[name="option_groups[${gIdx}][items][${iIdx}][is_default]"][type="checkbox"]`);
            if (defInput) item.is_default = defInput.checked;
        });
    });
}

document.getElementById('add-option-group').addEventListener('click', addOptionGroup);
document.querySelectorAll('[data-fg-tab]').forEach(button=>button.addEventListener('click',()=>{activeFgTab=button.dataset.fgTab;updateConfigPanels()}));

// Initial run
render();
renderOptions();
</script>
@endpush
