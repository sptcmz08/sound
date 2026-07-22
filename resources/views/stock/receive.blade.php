@extends('layouts.app')
@section('title','รับสินค้าเข้า') @section('header','รับสินค้าเข้า')
@section('content')
<div class="mx-auto max-w-4xl"><div class="mb-7"><span class="badge-blue mb-3">ขั้นตอนที่ 1 · เลือกและคีย์จำนวน</span><h2 class="page-title">รับเข้าแบบคีย์ด้วยมือ</h2><p class="page-subtitle">ไม่มีการสแกน Barcode เลือกสินค้า เช่น “น็อต 1/4 นิ้ว” แล้วกรอกจำนวนได้เลย</p></div>
<form method="post" action="{{route('stock.receive.store')}}" class="panel">@csrf
<div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">รายการรับเข้า</h3><p class="mt-1 text-slate-500">เมื่อกดบันทึก ยอดคงเหลือจะเพิ่มทันที</p></div><a href="{{route('products.create')}}" class="btn-secondary">+ สร้างสินค้าใหม่</a></div>
<div class="panel-body grid gap-6 md:grid-cols-2">
<label class="md:col-span-2"><span class="label">สินค้า *</span><select name="product_id" class="select" required><option value="">— เลือกสินค้า —</option>@foreach($products->groupBy(fn($p)=>$p->product_type->label()) as $label=>$items)<optgroup label="{{$label}}">@foreach($items as $p)<option value="{{$p->id}}" @selected(old('product_id')==$p->id)>{{$p->code}} — {{$p->name}} ({{$p->unit->name}})</option>@endforeach</optgroup>@endforeach</select></label>
<label><span class="label">คลัง *</span><select name="warehouse_id" class="select" required>@foreach($warehouses as $w)<option value="{{$w->id}}" @selected(old('warehouse_id')==$w->id)>{{$w->code}} — {{$w->name}}</option>@endforeach</select></label>
<label><span class="label">จำนวนที่รับเข้า *</span><input type="number" min="0.0001" step="0.0001" name="quantity" value="{{old('quantity')}}" class="input text-xl font-bold" placeholder="เช่น 100" required autofocus></label>
<label class="md:col-span-2"><span class="label">หมายเหตุ</span><textarea class="input" rows="3" name="note" placeholder="เลขที่ใบส่งของ หรือรายละเอียดอื่น">{{old('note')}}</textarea></label>
</div><div class="flex justify-end border-t border-slate-100 p-6"><button class="btn-success px-8">✓ ยืนยันรับเข้าสต็อก</button></div></form></div>
@endsection
