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
                    ['icon' => '✅', 'label' => 'Admin ตรวจสอบ & อนุมัติ'],
                    ['icon' => '✍️', 'label' => 'พนักงานลงนามออนไลน์'],
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
                'ISSUE_PART' => ['title' => 'เบิก PART', 'desc' => 'เบิกอะไหล่หรือชิ้นส่วนผลิตที่กำหนดจำนวนได้ชัดเจน', 'icon' => '<svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/></svg>', 'colors' => 'bg-blue-100 text-blue-700', 'ring' => 'hover:ring-blue-300'],
                'ISSUE_SUPPLY' => ['title' => 'เบิก SUPPLY', 'desc' => 'เบิกวัสดุสิ้นเปลืองแยกจาก PART และไม่ผูกจำนวนต่อชิ้นงาน', 'icon' => '<svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m6.364-.364-2.122 2.122M21 12h-3M5.636 5.636l2.122 2.122M3 12h3m6-3a3 3 0 100 6 3 3 0 000-6zm-5 8h10l-1 4H8l-1-4z"/></svg>', 'colors' => 'bg-cyan-100 text-cyan-700', 'ring' => 'hover:ring-cyan-300'],
                'ISSUE_WIP' => ['title' => 'เบิก WIP', 'desc' => 'เบิก WIP ที่ผลิตเสร็จแล้วออกไปใช้งาน', 'icon' => '<svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.42 3.04V6.03l5.42 3.04m0 6.1V9.07m0 6.1l5.42 3.04V6.03l-5.42 3.04"/></svg>', 'colors' => 'bg-amber-100 text-amber-700', 'ring' => 'hover:ring-amber-300'],
                'ISSUE_FG' => ['title' => 'เบิก FG พร้อมขาย', 'desc' => 'เบิกสินค้าสำเร็จรูปออกไปขายหรือส่งมอบ', 'icon' => '<svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H18.75M2.25 14.25h3.86a2.25 2.25 0 011.591.659l1.902 1.902M18.75 14.25H9.86a2.25 2.25 0 00-1.591.659L6.367 16.81"/></svg>', 'colors' => 'bg-emerald-100 text-emerald-700', 'ring' => 'hover:ring-emerald-300'],
                'BUILD_WIP' => ['title' => 'ผลิต WIP', 'desc' => 'ตัด PART ตามสูตร BOM แล้วสร้าง WIP เข้าสต็อก', 'icon' => '<svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.049.58.025 1.193-.14 1.743"/></svg>', 'colors' => 'bg-violet-100 text-violet-700', 'ring' => 'hover:ring-violet-300'],
                'BUILD_FG' => ['title' => 'ผลิต FG', 'desc' => 'ตัด WIP + PART ตามสูตร BOM แล้วสร้าง FG เข้าสต็อก', 'icon' => '<svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>', 'colors' => 'bg-emerald-100 text-emerald-700', 'ring' => 'hover:ring-emerald-300'],
            };
        @endphp
        <a href="{{$type->value === 'BUILD_WIP' ? route('requisitions.wip.create') : route('requisitions.create',['type'=> $type->value])}}" class="panel group relative overflow-hidden p-6 hover:-translate-y-1 hover:shadow-lg ring-2 ring-transparent {{$card['ring']}} transition-all">
            <div class="mb-5 grid size-16 place-items-center rounded-2xl {{$card['colors']}} shadow-sm">{!! $card['icon'] !!}</div>
            <h3 class="text-2xl font-bold text-slate-950">{{$card['title']}}</h3>
            <p class="mt-2 min-h-14 text-base text-slate-500">{{$card['desc']}}</p>
            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4 font-bold text-blue-600">
                <span>{{$mode === 'withdraw' ? 'เริ่มเบิกรายการนี้' : 'เริ่มสร้างรายการนี้'}}</span><span class="text-xl transition-transform group-hover:translate-x-1">→</span>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
