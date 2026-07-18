@extends('layouts.app')

@section('title', 'สร้างวิชใหม่')
@section('header', 'สร้างวิชใหม่')

@section('content')
@php
    $partOptions = $parts->map(fn ($part) => [
        'id' => $part->id,
        'code' => $part->code,
        'name' => $part->name,
        'unit' => $part->unit->name,
        'balance' => \App\Support\Quantity::format($part->balances_sum_quantity ?? 0),
        'balances' => $part->balances->mapWithKeys(fn ($balance) => [
            (string) $balance->warehouse_id => \App\Support\Quantity::format($balance->quantity),
        ]),
    ])->values();
    $savedWipOptions = $savedWips->map(fn ($wip) => [
        'id' => $wip->id,
        'code' => $wip->code,
        'name' => $wip->name,
        'components' => $wip->components->map(fn ($component) => [
            'product_id' => $component->id,
            'quantity' => \App\Support\Quantity::format($component->pivot->quantity),
        ])->values(),
    ])->values();
@endphp

<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="flex items-start gap-4">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-violet-500 to-purple-700 text-white shadow-lg shadow-violet-500/20">
                <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21V10l5 3V9l5 3V4h4v17M3 21h18"/></svg>
            </span>
            <div>
                <h2 class="page-title">สร้างวิชจากอะไหล่</h2>
                <p class="page-subtitle">ตั้งชื่อวิช เลือกรายการอะไหล่ และระบุจำนวนได้ในหน้าเดียว</p>
            </div>
        </div>
        <a href="{{ route('requisitions.production') }}" class="btn-secondary">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
            กลับไปเลือกงานผลิต
        </a>
    </div>

    <form method="post" action="{{ route('requisitions.wip.store') }}" class="space-y-6" id="wip-form">
        @csrf

        <section class="panel overflow-hidden">
            <div class="panel-header">
                <div><h3 class="text-xl font-bold text-slate-950">1. เลือกหรือตั้งชื่อวิช</h3><p class="mt-0.5 text-sm text-slate-500">ใช้สูตรเดิมที่เคยบันทึกไว้ หรือสร้างวิชใหม่</p></div>
            </div>
            <div class="border-b border-slate-100 bg-slate-50/70 p-5 sm:p-6">
                <label class="block max-w-2xl">
                    <span class="label">เลือกวิชที่เคยสร้าง</span>
                    <select name="existing_wip_id" id="existing-wip" class="select bg-white">
                        <option value="">+ สร้างวิชใหม่และบันทึกเป็นสูตร</option>
                        @foreach($savedWips as $savedWip)
                            <option value="{{ $savedWip->id }}" @selected((string) old('existing_wip_id') === (string) $savedWip->id)>[{{ $savedWip->code }}] {{ $savedWip->name }}</option>
                        @endforeach
                    </select>
                    <small class="mt-2 block text-slate-500">เมื่อเลือกวิชเดิม ระบบจะเติมชื่อและรายการอะไหล่ให้อัตโนมัติ</small>
                </label>
            </div>
            <div class="grid lg:grid-cols-[1fr_280px]">
                <label class="border-b border-slate-100 p-5 lg:border-b-0 lg:border-r sm:p-6">
                    <span class="label">ชื่อวิช <span class="text-rose-500">*</span></span>
                    <input name="wip_name" id="wip-name" class="input text-lg font-bold" value="{{ old('wip_name') }}" placeholder="เช่น วิชลำโพง 40 × 40" required autofocus>
                    <small id="wip-name-help" class="mt-2 block text-slate-500">วิชใหม่จะถูกบันทึกไว้ให้เลือกใช้ในครั้งถัดไป</small>
                </label>
                <label class="p-5 sm:p-6">
                    <span class="label">จำนวนที่สร้าง <span class="text-rose-500">*</span></span>
                    <div class="relative">
                        <input name="output_quantity" type="number" min="0.0001" step="0.0001" class="input pr-16 text-lg font-bold" value="{{ old('output_quantity', 1) }}" required>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 font-semibold text-slate-500">{{ $unit->name }}</span>
                    </div>
                </label>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div><h3 class="text-xl font-bold text-slate-950">2. รายการเบิกอะไหล่</h3><p class="mt-0.5 text-sm text-slate-500">เลือกอะไหล่และจำนวนที่ใช้ต่อวิช 1 {{ $unit->name }}</p></div>
                <div class="flex flex-wrap items-end gap-3">
                    <label class="min-w-64"><span class="label text-sm">คลังที่ตัดสต็อก <span class="text-rose-500">*</span></span><select name="warehouse_id" id="warehouse" class="select bg-white" required>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->code }} — {{ $warehouse->name }}</option>@endforeach</select></label>
                    <button type="button" id="add-part" class="btn-primary"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>เพิ่มรายการเบิก</button>
                </div>
            </div>
            <div class="panel-body">
                <div class="mb-2 hidden grid-cols-[minmax(0,1fr)_150px_190px_64px] gap-3 px-3 text-sm font-bold text-slate-500 md:grid">
                    <span>สินค้า</span><span>สต็อกคงเหลือ</span><span>จำนวนที่ใช้ต่อวิช</span><span></span>
                </div>
                <div id="part-list" class="space-y-3"></div>
            </div>
        </section>

        <div class="sticky bottom-4 z-20 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-xl backdrop-blur sm:p-5">
            <div class="flex items-center gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-violet-100 text-violet-700"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"/></svg></span>
                <div><strong class="block text-lg text-slate-950">ตรวจรายการแล้วกดสร้างวิช</strong><span class="text-sm text-slate-500">สูตรวิชจะถูกบันทึกอัตโนมัติ และส่งรายการให้แอดมินอนุมัติตัดสต็อก</span></div>
            </div>
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-purple-700 px-7 py-3 text-base font-semibold text-white shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 focus:outline-none focus:ring-4 focus:ring-violet-100">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                สร้างวิช
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const parts = @json($partOptions);
const savedWips = @json($savedWipOptions);
const oldRows = @json(old('components'));
let rows = Array.isArray(oldRows) ? oldRows : [{ product_id: '', quantity: 1 }];
const list = document.getElementById('part-list');
const warehouse = document.getElementById('warehouse');
const existingWip = document.getElementById('existing-wip');
const wipName = document.getElementById('wip-name');
const wipNameHelp = document.getElementById('wip-name-help');

function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
}

function options(selected) {
    return '<option value="">— เลือกอะไหล่ —</option>' + parts.map(part =>
        `<option value="${part.id}" ${String(part.id) === String(selected) ? 'selected' : ''}>[${escapeHtml(part.code)}] ${escapeHtml(part.name)}</option>`
    ).join('');
}

function selectedPart(productId) {
    return parts.find(part => String(part.id) === String(productId));
}

function balanceFor(productId) {
    const part = selectedPart(productId);
    if (!part) return '<span class="text-slate-400">—</span>';
    const balance = part.balances[String(warehouse.value)] ?? '0';
    return `<strong class="text-lg text-slate-950">${escapeHtml(balance)}</strong> <span class="text-sm text-slate-500">${escapeHtml(part.unit)}</span>`;
}

function render() {
    list.innerHTML = rows.map((row, index) => `
        <div class="grid items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 md:grid-cols-[minmax(0,1fr)_150px_190px_64px]">
            <label><span class="label md:hidden">สินค้า</span><select class="select bg-white" name="components[${index}][product_id]" onchange="updateProduct(${index}, this.value)" required>${options(row.product_id)}</select></label>
            <div><span class="label md:hidden">สต็อกคงเหลือ</span><div class="rounded-xl bg-white px-4 py-3 ring-1 ring-slate-200">${balanceFor(row.product_id)}</div></div>
            <label><span class="label md:hidden">จำนวนที่ใช้ต่อวิช</span><input class="input bg-white text-lg font-bold" name="components[${index}][quantity]" oninput="updateQuantity(${index}, this.value)" type="number" min="0.0001" step="0.0001" value="${escapeHtml(row.quantity || 1)}" required></label>
            <button type="button" class="grid min-h-12 place-items-center rounded-xl text-rose-600 hover:bg-rose-50" onclick="removeRow(${index})" title="ลบรายการ" aria-label="ลบรายการ">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-9 0V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5"/></svg>
            </button>
        </div>
    `).join('') || '<div class="rounded-2xl border-2 border-dashed border-slate-200 p-10 text-center"><strong class="block text-slate-700">ยังไม่ได้เลือกอะไหล่</strong><span class="text-sm text-slate-500">กด “เพิ่มอะไหล่” เพื่อเริ่มเลือกรายการ</span></div>';
}

function updateProduct(index, productId) {
    rows[index].product_id = productId;
    render();
}

function updateQuantity(index, quantity) {
    rows[index].quantity = quantity;
}

function applySavedWip(resetRows = true) {
    const savedWip = savedWips.find(wip => String(wip.id) === existingWip.value);
    if (savedWip) {
        wipName.value = savedWip.name;
        wipName.readOnly = true;
        wipName.classList.add('bg-slate-100');
        wipNameHelp.textContent = 'กำลังใช้สูตรที่บันทึกไว้ หากแก้รายการอะไหล่ สูตรนี้จะอัปเดตสำหรับครั้งถัดไป';
        if (resetRows) rows = savedWip.components.map(component => ({ ...component }));
    } else {
        wipName.readOnly = false;
        wipName.classList.remove('bg-slate-100');
        wipNameHelp.textContent = 'วิชใหม่จะถูกบันทึกไว้ให้เลือกใช้ในครั้งถัดไป';
        if (resetRows) {
            wipName.value = '';
            rows = [{ product_id: '', quantity: 1 }];
        }
    }
    render();
}

function removeRow(index) {
    rows.splice(index, 1);
    render();
}

document.getElementById('add-part').addEventListener('click', () => {
    rows.push({ product_id: '', quantity: 1 });
    render();
});

warehouse.addEventListener('change', render);
existingWip.addEventListener('change', () => applySavedWip(true));

document.getElementById('wip-form').addEventListener('submit', event => {
    if (!rows.length) {
        event.preventDefault();
        alert('กรุณาเพิ่มอะไหล่อย่างน้อย 1 รายการ');
    }
});

if (existingWip.value) {
    applySavedWip(!Array.isArray(oldRows));
} else {
    render();
}
</script>
@endpush
