@extends('layouts.app')
@section('title','รายการรออนุมัติ')
@section('header','รายการรออนุมัติ')
@section('content')
<div class="mb-7"><span class="badge-amber mb-3">{{$pendingCount}} รายการรอตรวจ</span><h2 class="page-title">ศูนย์อนุมัติการเบิกและผลิต</h2><p class="page-subtitle">ตรวจและอนุมัติรายการ ระบบจะตัด/เพิ่มสต็อก และออกใบเบิกสำหรับลงนาม</p></div>
<div class="grid gap-4">
@forelse($rows as $r)
<a href="{{route('requisitions.show',$r)}}" class="panel group flex flex-wrap items-center gap-5 p-5 hover:border-blue-300">
@php($displayProduct = $r->targetProduct ?? $r->items->first()?->product)<x-product-image :product="$displayProduct" size="lg" />
<div class="min-w-60 flex-1"><div class="flex flex-wrap items-center gap-2"><strong class="text-lg text-slate-950">{{$r->request_no}}</strong><span class="{{$r->status->badgeClass()}}">{{$r->status->label()}}</span></div><p class="mt-1 font-semibold">{{$r->request_type->label()}} · {{$r->requester->name}}</p><p class="text-slate-500">{{$r->purpose}}</p></div>
<div class="text-right"><p class="font-semibold">{{$r->requested_at->format('d/m/Y H:i')}}</p><span class="text-blue-600 group-hover:underline">เปิดตรวจ →</span></div></a>
@empty <div class="panel empty-state">✓ ไม่มีรายการรออนุมัติ</div> @endforelse
</div><div class="mt-5">{{$rows->links()}}</div>
@endsection
