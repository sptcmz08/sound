@extends('layouts.app')
@section('title', 'ตั้งค่าระบบ')
@section('header', 'ตั้งค่าระบบ')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="page-kicker">การตั้งค่า</span>
            <h2 class="page-title">ข้อมูลพื้นฐานระบบ</h2>
            <p class="page-subtitle">จัดการหน่วยนับ คลังสินค้า และตำแหน่งจัดเก็บพัสดุ (Location / Shelves)</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Section 1: Units --}}
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="font-bold text-slate-900">หน่วยนับ</h3>
                    <p class="text-xs text-slate-400">หน่วยนับพัสดุและสินค้า (PCS, กล่อง, ม้วน)</p>
                </div>
                <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M4 6h16M6 3v6m4-6v3m4-3v6m4-6v3M4 12h16M4 18h16"/></svg>
                </span>
            </div>
            <form class="grid gap-2 border-b border-slate-100 p-4 sm:grid-cols-[100px_1fr_auto]" method="post" action="{{ route('settings.units') }}">
                @csrf
                <input class="input" name="code" placeholder="รหัส" required>
                <input class="input" name="name" placeholder="ชื่อหน่วย" required>
                <button class="btn-primary">+ เพิ่ม</button>
            </form>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>รหัส</th><th>ชื่อหน่วย</th><th>สถานะ</th><th class="text-right">จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($units as $u)
                        <tr>
                            <td class="font-bold text-slate-900">{{ $u->code }}</td>
                            <td>{{ $u->name }}</td>
                            <td><span class="{{ $u->is_active ? 'badge-green' : 'badge-slate' }}">{{ $u->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}</span></td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button" class="btn-ghost px-2 py-1 text-xs" data-id="{{ $u->id }}" data-code="{{ $u->code }}" data-name="{{ $u->name }}" data-active="{{ $u->is_active ? 1 : 0 }}" onclick="editUnit(this)">แก้ไข</button>
                                    <form method="post" action="{{ route('settings.units.destroy', $u) }}" onsubmit="return confirm('ยืนยันลบหน่วยนับนี้?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50" title="ลบ">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="empty-state">ยังไม่มีหน่วยนับ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Section 2: Warehouses --}}
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="font-bold text-slate-900">คลังสินค้า</h3>
                    <p class="text-xs text-slate-400">คลังหลักและคลังย่อยประจำระบบ</p>
                </div>
                <span class="grid size-10 place-items-center rounded-xl bg-violet-50 text-violet-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-5h6v5"/></svg>
                </span>
            </div>
            <form class="grid gap-2 border-b border-slate-100 p-4 sm:grid-cols-2" method="post" action="{{ route('settings.warehouses') }}">
                @csrf
                <input class="input" name="code" placeholder="รหัส เช่น MAIN" required>
                <input class="input" name="name" placeholder="ชื่อคลัง" required>
                <input class="input sm:col-span-2" name="address" placeholder="รายละเอียดคลัง (ไม่บังคับ)">
                <button class="btn-primary sm:col-span-2">+ เพิ่มคลังสินค้า</button>
            </form>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>รหัส/คลัง</th><th>สถานะ</th><th class="text-right">จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($warehouses as $w)
                        <tr>
                            <td>
                                <strong class="block text-slate-900 text-xs">{{ $w->code }} — {{ $w->name }}</strong>
                                <span class="text-[10px] text-slate-400">{{ $w->address ?: 'ไม่ระบุที่อยู่' }}</span>
                            </td>
                            <td><span class="{{ $w->is_active ? 'badge-green' : 'badge-slate' }}">{{ $w->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}</span></td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button" class="btn-ghost px-2 py-1 text-xs" data-id="{{ $w->id }}" data-code="{{ $w->code }}" data-name="{{ $w->name }}" data-address="{{ $w->address }}" data-active="{{ $w->is_active ? 1 : 0 }}" onclick="editWarehouse(this)">แก้ไข</button>
                                    <form method="post" action="{{ route('settings.warehouses.destroy', $w) }}" onsubmit="return confirm('ยืนยันลบคลังนี้?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50" title="ลบ">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="empty-state">ยังไม่มีคลังสินค้า</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Section 3: Warehouse Locations (New Feature from d:\stock) --}}
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="font-bold text-slate-900">ตำแหน่งจัดเก็บ (Location / Shelf)</h3>
                    <p class="text-xs text-slate-400">ระบุโซน/ชั้น/ล็อกประจำคลังสินค้า</p>
                </div>
                <span class="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"/></svg>
                </span>
            </div>
            <form class="grid gap-2 border-b border-slate-100 p-4" method="post" action="{{ route('settings.locations') }}">
                @csrf
                <select class="select text-xs" name="warehouse_id" required>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->code }} — {{ $wh->name }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input class="input" name="code" placeholder="รหัสล็อก เช่น A-01-2" required>
                    <input class="input" name="name" placeholder="ชื่อโซน/ชั้น" required>
                </div>
                <button class="btn-primary">+ เพิ่มตำแหน่งจัดเก็บ</button>
            </form>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>คลัง / รหัสล็อก</th><th>โซน/ชั้น</th><th class="text-right">จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($locations as $loc)
                        <tr>
                            <td>
                                <strong class="block text-xs text-slate-900">{{ $loc->code }}</strong>
                                <span class="text-[10px] text-slate-400">{{ $loc->warehouse?->code }}</span>
                            </td>
                            <td><span class="text-xs text-slate-700 font-medium">{{ $loc->name }}</span></td>
                            <td class="text-right">
                                <form method="post" action="{{ route('settings.locations.destroy', $loc) }}" onsubmit="return confirm('ยืนยันลบตำแหน่งจัดเก็บนี้?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50" title="ลบ">✕</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="empty-state">ยังไม่มีตำแหน่งจัดเก็บล็อก</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

{{-- Dialogs for Editing Units & Warehouses --}}
<dialog id="unit-dialog" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl bg-white p-0 shadow-2xl backdrop:bg-slate-950/60">
    <form id="unit-edit-form" method="post">
        @csrf @method('PUT')
        <div class="panel-header"><h3 class="font-bold text-slate-900">แก้ไขหน่วยนับ</h3><button type="button" onclick="document.getElementById('unit-dialog').close()" class="text-slate-400">✕</button></div>
        <div class="panel-body space-y-4">
            <div><label class="label">รหัส</label><input id="unit-code" class="input" name="code" required></div>
            <div><label class="label">ชื่อหน่วย</label><input id="unit-name" class="input" name="name" required></div>
            <label class="flex items-center gap-3 text-sm"><input id="unit-active" class="size-5 rounded border-slate-300 text-brand-600" type="checkbox" name="is_active" value="1"> เปิดใช้งาน</label>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 p-4"><button type="button" class="btn-secondary" onclick="document.getElementById('unit-dialog').close()">ยกเลิก</button><button class="btn-primary">บันทึก</button></div>
    </form>
</dialog>

<dialog id="warehouse-dialog" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl bg-white p-0 shadow-2xl backdrop:bg-slate-950/60">
    <form id="warehouse-edit-form" method="post">
        @csrf @method('PUT')
        <div class="panel-header"><h3 class="font-bold text-slate-900">แก้ไขคลังสินค้า</h3><button type="button" onclick="document.getElementById('warehouse-dialog').close()" class="text-slate-400">✕</button></div>
        <div class="panel-body space-y-4">
            <div><label class="label">รหัส</label><input id="warehouse-code" class="input" name="code" required></div>
            <div><label class="label">ชื่อคลัง</label><input id="warehouse-name" class="input" name="name" required></div>
            <div><label class="label">ที่อยู่/รายละเอียด</label><textarea id="warehouse-address" class="input min-h-20" name="address"></textarea></div>
            <label class="flex items-center gap-3 text-sm"><input id="warehouse-active" class="size-5 rounded border-slate-300 text-brand-600" type="checkbox" name="is_active" value="1"> เปิดใช้งาน</label>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 p-4"><button type="button" class="btn-secondary" onclick="document.getElementById('warehouse-dialog').close()">ยกเลิก</button><button class="btn-primary">บันทึก</button></div>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
function editUnit(button){const data=button.dataset;const form=document.getElementById('unit-edit-form');form.action=`{{url('/settings/units')}}/${data.id}`;document.getElementById('unit-code').value=data.code;document.getElementById('unit-name').value=data.name;document.getElementById('unit-active').checked=data.active==='1';document.getElementById('unit-dialog').showModal()}
function editWarehouse(button){const data=button.dataset;const form=document.getElementById('warehouse-edit-form');form.action=`{{url('/settings/warehouses')}}/${data.id}`;document.getElementById('warehouse-code').value=data.code;document.getElementById('warehouse-name').value=data.name;document.getElementById('warehouse-address').value=data.address??'';document.getElementById('warehouse-active').checked=data.active==='1';document.getElementById('warehouse-dialog').showModal()}
</script>
@endpush
