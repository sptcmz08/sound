@extends('layouts.app')
@section('title','ต้นทุน - กำไร')
@section('header','รายงานต้นทุน - กำไร')
@section('content')
@php $revenue=(float)$totals->revenue; $cost=(float)$totals->cost; $profit=$revenue-$cost; @endphp
<div class="mb-7"><h2 class="page-title">ต้นทุน - กำไร</h2><p class="page-subtitle">คำนวณจากราคาขายและต้นทุนที่บันทึกในวันที่ขาย</p></div>
<form class="panel mb-5 grid gap-4 p-4 sm:grid-cols-[1fr_1fr_auto]"><label><span class="label">ตั้งแต่วันที่</span><input class="input" type="date" name="date_from" value="{{request('date_from')}}"></label><label><span class="label">ถึงวันที่</span><input class="input" type="date" name="date_to" value="{{request('date_to')}}"></label><button class="btn-primary self-end">คำนวณ</button></form>
<div class="mb-6 grid gap-4 md:grid-cols-3">
    <div class="panel p-5"><span class="text-sm font-bold text-slate-500">ยอดขาย</span><strong class="mt-2 block text-3xl text-blue-700">฿{{number_format($revenue,2)}}</strong></div>
    <div class="panel p-5"><span class="text-sm font-bold text-slate-500">ต้นทุนขาย</span><strong class="mt-2 block text-3xl text-amber-700">฿{{number_format($cost,2)}}</strong></div>
    <div class="panel p-5"><span class="text-sm font-bold text-slate-500">กำไรขั้นต้น</span><strong class="mt-2 block text-3xl {{$profit>=0?'text-emerald-700':'text-rose-700'}}">฿{{number_format($profit,2)}}</strong></div>
</div>
<div class="table-shell"><div class="panel-header"><h3 class="text-xl font-bold">รายละเอียดการขาย</h3></div><div class="table-wrap"><table class="data-table"><thead><tr><th>วันที่ / เอกสาร</th><th>สินค้า</th><th class="text-right">จำนวน</th><th class="text-right">รายได้</th><th class="text-right">ต้นทุน</th><th class="text-right">กำไร</th></tr></thead><tbody>
@forelse($rows as $item) @php $lineRevenue=(float)$item->quantity*(float)$item->unit_price; $lineCost=(float)$item->quantity*(float)$item->unit_cost; @endphp
<tr><td>{{$item->document->document_date->format('d/m/Y')}}<a class="ml-2 font-mono text-blue-700" href="{{route('documents.show',$item->document)}}">{{$item->document->document_no}}</a></td><td><strong>{{$item->product->code}}</strong> {{$item->product->name}}</td><td class="text-right">{{\App\Support\Quantity::format($item->quantity)}}</td><td class="text-right">{{number_format($lineRevenue,2)}}</td><td class="text-right">{{number_format($lineCost,2)}}</td><td class="text-right font-bold {{$lineRevenue-$lineCost>=0?'text-emerald-700':'text-rose-700'}}">{{number_format($lineRevenue-$lineCost,2)}}</td></tr>
@empty <tr><td colspan="6" class="empty-state">ยังไม่มีรายการขาย</td></tr> @endforelse
</tbody></table></div></div><div class="mt-5">{{$rows->links()}}</div>
@endsection
