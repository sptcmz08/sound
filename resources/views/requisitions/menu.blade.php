@extends('layouts.app')

@section('title', $title)
@section('header', $title)

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
    <div class="page-head">
        <div><span class="page-kicker">{{ $mode === 'withdraw' ? 'เบิกจากสต็อก' : 'งานผลิต' }}</span><h2 class="page-title">{{ $title }}</h2><p class="page-subtitle">{{ $subtitle }}</p></div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">กลับหน้าหลัก</a>
    </div>

    <div class="panel p-4">
        <div class="grid gap-3 sm:grid-cols-4">
            @foreach([
                ['1', 'เลือกรายการ', 'ระบุสินค้าและจำนวน'],
                ['2', 'Admin อนุมัติ', 'ตรวจสอบและปรับสต็อก'],
                ['3', 'พนักงานลงนาม', 'ยืนยันการรับทราบ'],
                ['4', 'เอกสาร PDF', 'พิมพ์ส่งแผนกเบิก'],
            ] as [$number, $stepTitle, $stepDesc])
            <div class="flex items-start gap-3 rounded-lg bg-slate-50 p-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-blue-600 text-[10px] font-bold text-white">{{ $number }}</span><div><strong class="block text-[11px] text-slate-700">{{ $stepTitle }}</strong><small class="mt-0.5 block text-[9px] text-slate-400">{{ $stepDesc }}</small></div></div>
            @endforeach
        </div>
    </div>

    <section>
        <div class="mb-3"><h3 class="section-title">เลือกประเภทที่ต้องการ</h3><p class="section-subtitle">ระบบจะแสดงเฉพาะสินค้าที่ตรงกับประเภทที่เลือก</p></div>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach($types as $type)
            @php
                $card = match($type->value) {
                    'ISSUE_PART' => ['เบิก PART', 'อะไหล่และชิ้นส่วนผลิตที่กำหนดจำนวนได้', 'bg-blue-50 text-blue-600', 'M4 12h13m0 0-4-4m4 4-4 4M7 5H4v14h3'],
                    'ISSUE_SUPPLY' => ['เบิก SUPPLY', 'วัสดุสิ้นเปลืองที่เบิกแยกจาก PART', 'bg-cyan-50 text-cyan-600', 'M12 3v3m6 1-2 2m5 3h-3M6 7l2 2m-5 3h3m1 5h10l-1 4H8l-1-4Z'],
                    'ISSUE_WIP' => ['เบิก WIP', 'งานระหว่างประกอบที่ผลิตและเก็บในสต็อกแล้ว', 'bg-violet-50 text-violet-600', 'M4 19h16M6 19V9l4 3V8l4 3V5h4v14'],
                    'ISSUE_FG' => ['เบิก FG พร้อมขาย', 'สินค้าสำเร็จรูปสำหรับส่งมอบหรือใช้งาน', 'bg-emerald-50 text-emerald-600', 'M5 8h14v11H5V8Zm3 0V5h8v3'],
                    'BUILD_WIP' => ['ผลิต WIP', 'ตัด PART ตามสูตรและเพิ่ม WIP เข้าสต็อก', 'bg-violet-50 text-violet-600', 'M4 19h16M6 19V9l4 3V8l4 3V5h4v14'],
                    'BUILD_FG' => ['ผลิต FG', 'ตัด WIP/PART ตามสูตรและเพิ่ม FG เข้าสต็อก', 'bg-emerald-50 text-emerald-600', 'M5 8h14v11H5V8Zm3 0V5h8v3'],
                };
            @endphp
            <a href="{{ $type->value === 'BUILD_WIP' ? route('requisitions.wip.create') : route('requisitions.create', ['type' => $type->value]) }}" class="group flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 transition hover:border-blue-200 hover:shadow-sm">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl {{ $card[2] }}"><svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $card[3] }}"/></svg></span>
                <div class="min-w-0 flex-1"><h3 class="text-sm font-semibold text-slate-800">{{ $card[0] }}</h3><p class="mt-1 text-xs leading-relaxed text-slate-400">{{ $card[1] }}</p></div>
                <span class="text-lg text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500">→</span>
            </a>
            @endforeach
        </div>
    </section>
</div>
@endsection
