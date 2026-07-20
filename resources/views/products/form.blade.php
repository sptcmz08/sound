@extends('layouts.app')
@section('title', $product->exists ? 'แก้ไขสินค้า' : 'เพิ่มสินค้า')
@section('header', $product->exists ? 'แก้ไขสินค้าและสูตร' : 'เพิ่มสินค้าและสูตร')
@section('content')
@php
    $currentType = old('product_type', $product->product_type?->value ?? 'PART');
    $oldComponents = old('components', $product->exists ? $product->components->map(fn($c) => ['product_id' => $c->id, 'quantity' => $c->pivot->quantity])->values()->all() : []);
    $componentJson = $componentProducts->map(fn($p) => ['id' => $p->id, 'code' => $p->code, 'name' => $p->name, 'type' => $p->product_type->value, 'unit' => $p->unit->name])->values()->toJson();
@endphp
<div class="mb-7 flex items-start justify-between gap-4">
    <div><h2 class="page-title">{{ $product->exists ? 'แก้ไขรายการ' : 'สร้างรายการใหม่' }}</h2><p class="page-subtitle">PART เพิ่มได้ทันที ส่วน WIP และ FG ให้กำหนดสูตรที่ใช้ต่อ 1 ชิ้น</p></div>
    <a href="{{route('products.index')}}" class="btn-secondary">กลับ</a>
</div>
<form method="post" action="{{$product->exists ? route('products.update',$product) : route('products.store')}}" enctype="multipart/form-data" class="space-y-6" id="product-form">
    @csrf @if($product->exists) @method('PUT') @endif
    <section class="panel"><div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">ข้อมูลสินค้า</h3><p class="mt-1 text-slate-500">ไม่ใช้ Barcode — แอดมินเลือกรายการและคีย์จำนวนโดยตรง</p></div></div>
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
            <label><span class="label">ประเภท *</span><select name="product_type" id="product-type" class="select" required><option value="PART" @selected($currentType==='PART')>PART (ชิ้นส่วน/วัสดุสิ้นเปลือง)</option><option value="WIP" @selected($currentType==='WIP')>WIP (งานระหว่างประกอบ)</option><option value="FG" @selected($currentType==='FG')>FG (สินค้าพร้อมขาย)</option></select></label>
            <label><span class="label">หน่วยนับ *</span><select name="unit_id" class="select" required>@foreach($units as $unit)<option value="{{$unit->id}}" @selected(old('unit_id',$product->unit_id)==$unit->id)>{{$unit->name}} ({{$unit->code}})</option>@endforeach</select></label>
            <label><span class="label">จุดเตือนสต็อกต่ำ</span><input type="number" min="0" step="0.0001" name="minimum_stock" class="input" value="{{old('minimum_stock',$product->minimum_stock ?? 0)}}" required></label>
            <label><span class="label">ต้นทุนมาตรฐาน / หน่วย *</span><input type="number" min="0" step="0.0001" name="standard_cost" class="input" value="{{old('standard_cost',$product->standard_cost ?? 0)}}" required></label>
            <label><span class="label">ราคาขายแนะนำ / หน่วย *</span><input type="number" min="0" step="0.0001" name="sale_price" class="input" value="{{old('sale_price',$product->sale_price ?? 0)}}" required></label>
            <label><span class="label">ที่เก็บ</span><input name="location_text" class="input" value="{{old('location_text',$product->location_text)}}" placeholder="เช่น ชั้น A-02"></label>
            <label class="md:col-span-2"><span class="label">หมายเหตุ</span><textarea name="note" class="input" rows="3">{{old('note',$product->note)}}</textarea></label>
            <label class="flex items-center gap-3"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="size-5 rounded" @checked(old('is_active',$product->exists ? $product->is_active : true))><span class="font-semibold">เปิดใช้งาน</span></label>
            <label class="flex items-center gap-3"><input type="hidden" name="is_consumable" value="0"><input type="checkbox" name="is_consumable" value="1" class="size-5 rounded" @checked(old('is_consumable',$product->is_consumable))><span class="font-semibold">เป็นวัสดุสิ้นเปลือง</span></label>
        </div>
    </section>
    <section class="panel" id="recipe-panel">
        <div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">สูตรส่วนประกอบต่อ 1 ชิ้น</h3><p class="mt-1 text-slate-500" id="recipe-help"></p></div><button type="button" id="add-component" class="btn-secondary">+ เพิ่มส่วนประกอบ</button></div>
        <div class="panel-body space-y-3" id="component-list"></div>
    </section>
    <div class="flex justify-end gap-3"><a href="{{route('products.index')}}" class="btn-secondary">ยกเลิก</a><button class="btn-primary">บันทึกสินค้าและสูตร</button></div>
</form>
@endsection
@push('scripts')
<script>
const products = {!! $componentJson !!};
const imageInput=document.getElementById('product-image-input'), newImage=document.getElementById('new-product-image'), currentImage=document.getElementById('current-product-image');
imageInput?.addEventListener('change',()=>{const file=imageInput.files?.[0];if(!file)return;newImage.src=URL.createObjectURL(file);newImage.classList.remove('hidden');currentImage?.classList.add('hidden')});
let rows = @json($oldComponents); const list=document.getElementById('component-list'), type=document.getElementById('product-type'), panel=document.getElementById('recipe-panel');
function options(selected){const allowed=products.filter(p=>type.value==='FG'||p.type==='PART');return '<option value="">เลือกส่วนประกอบ</option>'+allowed.map(p=>`<option value="${p.id}" ${String(selected)===String(p.id)?'selected':''}>${p.code} — ${p.name} (${p.type})</option>`).join('')}
function render(){panel.classList.toggle('hidden',type.value==='PART');document.getElementById('recipe-help').textContent=type.value==='WIP'?'WIP ใช้ PART เป็นส่วนประกอบ':'FG ใช้ได้ทั้ง PART และ WIP';list.innerHTML=rows.map((r,i)=>`<div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1fr_220px_auto]"><select class="select" name="components[${i}][product_id]" required>${options(r.product_id)}</select><input class="input" type="number" min="0.0001" step="0.0001" name="components[${i}][quantity]" value="${r.quantity||1}" placeholder="จำนวนต่อ 1 ชิ้น" required><button type="button" class="btn-danger" onclick="rows.splice(${i},1);render()">ลบ</button></div>`).join('')||'<div class="rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center text-slate-500">ยังไม่มีส่วนประกอบ กด “เพิ่มส่วนประกอบ”</div>'}
type.addEventListener('change',()=>{rows=[];render()});document.getElementById('add-component').addEventListener('click',()=>{rows.push({product_id:'',quantity:1});render()});render();
</script>
@endpush
