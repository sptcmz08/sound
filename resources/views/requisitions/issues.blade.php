@extends('layouts.app')
@section('title','จ่ายสินค้า')
@section('header','จ่ายสินค้า')
@section('content')
<div class="mb-8 flex flex-wrap items-end justify-between gap-4"><div><span class="badge-amber mb-3">{{$pendingCount}} คำขอรออนุมัติ</span><h2 class="page-title">ตรวจและจ่ายสินค้า</h2><p class="page-subtitle">เปิดคำขอ ตรวจจำนวน อนุมัติ และพิมพ์ใบเบิกสำหรับลงนาม</p></div><a href="{{route('requisitions.index')}}" class="btn-secondary">ดูคำขอทั้งหมด</a></div>
<div class="grid gap-4">
@forelse($rows as $row)
<a href="{{route('requisitions.show',$row)}}" class="panel group flex flex-wrap items-center gap-5 p-5 hover:border-blue-300">
    @php($displayProduct = $row->targetProduct ?? $row->items->first()?->product)<x-product-image :product="$displayProduct" size="lg" />
    <div class="min-w-64 flex-1"><div class="flex flex-wrap items-center gap-2"><strong class="text-lg text-slate-950">{{$row->request_no}}</strong><span class="{{$row->status->badgeClass()}}">{{$row->status->value === 'PENDING' ? 'รอตรวจ/จ่าย' : 'อนุมัติแล้ว'}}</span></div><p class="mt-1 font-semibold">{{$row->request_type->label()}} · {{$row->requester->name}}</p><p class="text-slate-500">{{$row->purpose}} · {{$row->warehouse->name}}</p></div>
    <div class="text-right"><p class="font-semibold">{{$row->requested_at->format('d/m/Y H:i')}}</p><span class="font-bold text-blue-600 group-hover:underline">{{$row->status->value === 'PENDING' ? 'ตรวจและอนุมัติ' : 'เปิด/พิมพ์ใบเบิก'}} →</span></div>
</a>
@empty <div class="panel empty-state">ยังไม่มีคำขอสำหรับจ่ายสินค้า</div> @endforelse
</div><div class="mt-5">{{$rows->links()}}</div>
@endsection
