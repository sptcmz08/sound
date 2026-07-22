@extends('layouts.app')
@section('title', $title)
@section('header', $title)
@section('content')
<div class="mb-7"><h2 class="page-title">{{$title}}</h2><p class="page-subtitle">{{$subtitle}}</p></div>
<form class="panel mb-5 grid gap-4 p-4 sm:grid-cols-[1fr_1fr_auto]">
    <label><span class="label">ตั้งแต่วันที่</span><input class="input" type="date" name="date_from" value="{{request('date_from')}}"></label>
    <label><span class="label">ถึงวันที่</span><input class="input" type="date" name="date_to" value="{{request('date_to')}}"></label>
    <button class="btn-primary self-end">แสดงรายงาน</button>
</form>
<div class="space-y-4">
@forelse($rows as $document)
    <article class="panel overflow-hidden">
        <div class="panel-header flex flex-wrap items-start justify-between gap-3">
            <div><a class="font-mono text-lg font-bold text-blue-700 hover:underline" href="{{route('documents.show',$document)}}">{{$document->document_no}}</a><p class="text-sm text-slate-500">{{$document->document_date->format('d/m/Y')}} · {{$document->warehouse->name}}</p></div>
            <div class="text-right"><span class="badge-slate">{{$document->document_type->label()}}</span><p class="mt-2 text-sm text-slate-500">{{$document->contact_name ?: '—'}}</p></div>
        </div>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>สินค้า</th><th>ประเภท</th><th class="text-right">จำนวน</th><th class="text-right">มูลค่า</th></tr></thead><tbody>
        @foreach($document->items as $item) @php($lineValue=(float)$item->quantity*(float)($document->document_type->value==='SALE_OUT'?$item->unit_price:$item->unit_cost))<tr><td><strong>{{$item->product->code}}</strong><span class="ml-2 text-slate-500">{{$item->product->name}}</span></td><td>{{$item->product->product_type->value}}</td><td class="text-right">{{\App\Support\Quantity::format($item->quantity)}} {{$item->product->unit->name}}</td><td class="text-right">{{number_format($lineValue,2)}}</td></tr>@endforeach
        </tbody></table></div>
        @if($document->note)<div class="border-t border-slate-100 px-5 py-3 text-sm text-slate-600">{{$document->note}}</div>@endif
    </article>
@empty <div class="panel empty-state">ยังไม่มีข้อมูลในช่วงวันที่เลือก</div> @endforelse
</div>
<div class="mt-5">{{$rows->links()}}</div>
@endsection
