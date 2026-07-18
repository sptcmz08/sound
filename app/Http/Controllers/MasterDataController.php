<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MasterDataController extends Controller
{
    public function index()
    {
        return view('settings.index', ['units' => Unit::orderBy('code')->get(), 'warehouses' => Warehouse::orderBy('code')->get()]);
    }

    public function unit(Request $r, AuditLogService $audit)
    {
        $data = $r->validate(['code' => ['required', 'max:50', 'unique:units,code'], 'name' => ['required', 'max:255']]);
        $m = Unit::create($data + ['is_active' => true]);
        $audit->record($r->user(), 'CREATE', 'unit', $m->id, null, $m->toArray());

        return back()->with('success', 'เพิ่มหน่วยนับแล้ว');
    }

    public function warehouse(Request $r, AuditLogService $audit)
    {
        $data = $r->validate(['code' => ['required', 'max:50', 'unique:warehouses,code'], 'name' => ['required', 'max:255'], 'address' => ['nullable', 'max:1000']]);
        $m = Warehouse::create($data + ['is_active' => true]);
        $audit->record($r->user(), 'CREATE', 'warehouse', $m->id, null, $m->toArray());

        return back()->with('success', 'เพิ่มคลังแล้ว');
    }

    public function updateUnit(Request $request, Unit $unit, AuditLogService $audit)
    {
        $data = $request->validate([
            'code' => ['required', 'max:50', Rule::unique('units')->ignore($unit->id)],
            'name' => ['required', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $old = $unit->toArray();
        $data['is_active'] = $request->boolean('is_active');
        $unit->update($data);
        $audit->record($request->user(), 'UPDATE', 'unit', $unit->id, $old, $unit->fresh()->toArray());

        return back()->with('success', 'แก้ไขหน่วยนับแล้ว');
    }

    public function destroyUnit(Request $request, Unit $unit, AuditLogService $audit)
    {
        $old = $unit->toArray();
        if ($unit->products()->exists()) {
            $unit->update(['is_active' => false]);
            $action = 'DEACTIVATE';
            $message = 'หน่วยนับมีสินค้าใช้งานอยู่ จึงเปลี่ยนเป็นปิดใช้งานแทนการลบ';
        } else {
            $unit->delete();
            $action = 'DELETE';
            $message = 'ลบหน่วยนับแล้ว';
        }
        $audit->record($request->user(), $action, 'unit', $unit->id, $old);

        return back()->with('success', $message);
    }

    public function updateWarehouse(Request $request, Warehouse $warehouse, AuditLogService $audit)
    {
        $data = $request->validate([
            'code' => ['required', 'max:50', Rule::unique('warehouses')->ignore($warehouse->id)],
            'name' => ['required', 'max:255'],
            'address' => ['nullable', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if (! $data['is_active'] && $warehouse->is_active && Warehouse::where('is_active', true)->count() <= 1) {
            throw ValidationException::withMessages(['is_active' => 'ต้องมีคลังที่เปิดใช้งานอย่างน้อย 1 แห่ง']);
        }
        $old = $warehouse->toArray();
        $warehouse->update($data);
        $audit->record($request->user(), 'UPDATE', 'warehouse', $warehouse->id, $old, $warehouse->fresh()->toArray());

        return back()->with('success', 'แก้ไขคลังสินค้าแล้ว');
    }

    public function destroyWarehouse(Request $request, Warehouse $warehouse, AuditLogService $audit)
    {
        if ($warehouse->is_active && Warehouse::where('is_active', true)->count() <= 1) {
            throw ValidationException::withMessages(['warehouse' => 'ไม่สามารถลบคลังที่เปิดใช้งานแห่งสุดท้ายได้']);
        }
        $old = $warehouse->toArray();
        if ($warehouse->documents()->exists()) {
            $warehouse->update(['is_active' => false]);
            $action = 'DEACTIVATE';
            $message = 'คลังมีประวัติเอกสาร จึงเปลี่ยนเป็นปิดใช้งานแทนการลบ';
        } else {
            $warehouse->delete();
            $action = 'DELETE';
            $message = 'ลบคลังสินค้าแล้ว';
        }
        $audit->record($request->user(), $action, 'warehouse', $warehouse->id, $old);

        return back()->with('success', $message);
    }
}
