@extends('layouts.app')
@section('title',$title)
@section('header',$title)
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-8">
        <span class="{{$mode === 'withdraw' ? 'badge-blue' : 'badge-amber'}} mb-3">{{$mode === 'withdraw' ? 'งานเบิก' : 'งานผลิต'}}</span>
        <h2 class="page-title">{{$title}}</h2>
        <p class="page-subtitle">{{$subtitle}}</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach($types as $type)
        @php
            $card = match($type->value) {
                'GENERAL_ISSUE' => ['อะไหล่ทั่วไป', 'เบิกน็อต สาย แผ่นเหล็ก หรืออะไหล่ทั่วไปออกไปใช้', 'ก', 'bg-blue-100 text-blue-700'],
                'ISSUE_WIP' => ['วิช', 'เบิกวิชที่ผลิตเสร็จแล้วออกไปใช้งาน', 'ว', 'bg-amber-100 text-amber-700'],
                'ISSUE_FG' => ['FG พร้อมขาย', 'เบิกสินค้าสำเร็จรูปออกไปขายหรือส่งมอบ', 'FG', 'bg-emerald-100 text-emerald-700'],
                'BUILD_WIP' => ['สร้างวิช', 'เลือกสูตรวิช ตัดอะไหล่ และเพิ่มวิชเข้าสต็อก', '+ว', 'bg-amber-100 text-amber-700'],
                'BUILD_FG' => ['สร้าง FG', 'เลือกสูตร FG ตัดวิช/อะไหล่ และเพิ่ม FG เข้าสต็อก', '+FG', 'bg-emerald-100 text-emerald-700'],
            };
        @endphp
        <a href="{{$type->value === 'BUILD_WIP' ? route('requisitions.wip.create') : route('requisitions.create',['type'=>$type->value])}}" class="panel group relative overflow-hidden p-6 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg">
            <div class="mb-5 grid size-16 place-items-center rounded-2xl text-xl font-bold {{$card[3]}}">{{$card[2]}}</div>
            <h3 class="text-2xl font-bold text-slate-950">{{$card[0]}}</h3>
            <p class="mt-2 min-h-14 text-base text-slate-500">{{$card[1]}}</p>
            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4 font-bold text-blue-600">
                <span>{{$mode === 'withdraw' ? 'เริ่มเบิกรายการนี้' : 'เริ่มสร้างรายการนี้'}}</span><span class="text-xl transition-transform group-hover:translate-x-1">→</span>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-8 rounded-2xl border border-blue-200 bg-blue-50 p-5 text-blue-900">
        <strong>ขั้นตอน:</strong> เลือกประเภท → กรอกรายการ → ผู้ขอลงนามออนไลน์ → แอดมินอนุมัติ → ดาวน์โหลด PDF ที่ลงนามแล้ว
    </div>
</div>
@endsection
