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

    {{-- Workflow Overview --}}
    <div class="mb-8 rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-6">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-blue-700">ขั้นตอนการเบิกสินค้า</h3>
        <div class="flex flex-wrap items-center justify-between gap-3">
            @php
                $flowSteps = [
                    ['icon' => '📋', 'label' => 'เลือกประเภท & กรอกรายการ'],
                    ['icon' => '✍️', 'label' => 'พนักงานลงนามออนไลน์'],
                    ['icon' => '✅', 'label' => 'Admin ตรวจสอบ & อนุมัติ'],
                    ['icon' => '📄', 'label' => 'ได้ PDF ปริ้นส่งแผนกเบิก'],
                ];
            @endphp
            @foreach($flowSteps as $i => $fs)
            <div class="flex items-center gap-2">
                <div class="grid size-10 place-items-center rounded-xl bg-white text-lg shadow-sm ring-1 ring-blue-200">{{$fs['icon']}}</div>
                <span class="text-sm font-bold text-slate-700">{{$fs['label']}}</span>
            </div>
            @if(!$loop->last)<span class="hidden text-blue-400 lg:block">→</span>@endif
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach($types as $type)
        @php
            $card = match($type->value) {
                'GENERAL_ISSUE' => ['PART', 'เบิกชิ้นส่วนหรือวัสดุสิ้นเปลืองออกจากสต็อก', 'P', 'bg-blue-100 text-blue-700'],
                'ISSUE_WIP' => ['WIP', 'เบิก WIP ที่ผลิตเสร็จแล้วออกไปใช้งาน', 'W', 'bg-amber-100 text-amber-700'],
                'ISSUE_FG' => ['FG พร้อมขาย', 'เบิกสินค้าสำเร็จรูปออกไปขายหรือส่งมอบ', 'FG', 'bg-emerald-100 text-emerald-700'],
                'BUILD_WIP' => ['ผลิต WIP', 'เลือกสูตร WIP ตัด PART และเพิ่ม WIP เข้าสต็อก', '+W', 'bg-amber-100 text-amber-700'],
                'BUILD_FG' => ['ผลิต FG', 'เลือกสูตร FG ตัด WIP / PART และเพิ่ม FG เข้าสต็อก', '+FG', 'bg-emerald-100 text-emerald-700'],
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
</div>
@endsection
