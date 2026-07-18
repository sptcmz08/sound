@extends('layouts.app')
@section('title','สินค้าและรับเข้าสต็อก') @section('header','สินค้าและรับเข้าสต็อก')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <span class="mb-2 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-sm font-bold text-blue-700 ring-1 ring-blue-200">คลังสินค้า</span>
        <h2 class="page-title">รายการสินค้า</h2>
        <p class="page-subtitle">ดูยอดคงเหลือ รับสินค้าเข้า และจัดการอะไหล่ วิช หรือ FG จากหน้าเดียว</p>
    </div>
    @if(auth()->user()->isAdmin())
    <div class="flex flex-wrap gap-3">
        <button type="button" class="btn-success" data-open-receive>
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0 4-4m-4 4-4-4M5 20h14"/></svg>
            รับสินค้าเข้า
        </button>
        <a href="{{route('products.create')}}" class="btn-primary">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
            เพิ่มสินค้า / สูตร
        </a>
    </div>
    @endif
</div>

<form class="panel mb-5 flex flex-wrap gap-3 p-4">
    <div class="relative min-w-64 flex-1">
        <svg class="pointer-events-none absolute left-4 top-3.5 size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
        <input class="input pl-11" name="q" value="{{request('q')}}" placeholder="ค้นหารหัสหรือชื่อสินค้า">
    </div>
    <select class="select w-full sm:w-56" name="type"><option value="">ทุกประเภท</option><option value="PART" @selected(request('type')==='PART')>อะไหล่ทั่วไป</option><option value="WIP" @selected(request('type')==='WIP')>วิช</option><option value="FG" @selected(request('type')==='FG')>FG</option></select>
    <button class="btn-secondary">ค้นหา</button>
</form>

<div class="table-shell">
    <div class="panel-header flex items-center justify-between gap-3">
        <div><h3 class="text-xl font-bold text-slate-950">สินค้าทั้งหมด</h3><p class="text-sm text-slate-500">พบ {{$products->total()}} รายการ</p></div>
        <span class="badge-slate">อัปเดตยอดแบบ Real-time</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>สินค้า</th><th>ประเภท</th><th class="text-right">คงเหลือ</th><th>หน่วย</th><th>สูตร</th><th>สถานะ</th><th class="text-right">จัดการ</th></tr></thead>
            <tbody>
            @forelse($products as $p)
                <tr>
                    <td><strong class="block text-slate-950">{{$p->name}}</strong><span class="font-mono text-sm text-slate-500">{{$p->code}}</span></td>
                    <td><span class="{{$p->product_type->value==='PART'?'badge-blue':($p->product_type->value==='WIP'?'badge-amber':'badge-green')}}">{{$p->product_type->label()}}</span></td>
                    <td class="text-right"><strong class="text-xl text-slate-950">{{\App\Support\Quantity::format($p->balances_sum_quantity ?? 0)}}</strong></td>
                    <td>{{$p->unit->name}}</td>
                    <td>{{$p->product_type->value==='PART' ? '—' : $p->components_count.' รายการ'}}</td>
                    <td><span class="{{$p->is_active?'badge-green':'badge-slate'}}">{{$p->is_active?'ใช้งาน':'ปิดใช้งาน'}}</span></td>
                    <td class="text-right">
                        @if(auth()->user()->isAdmin())
                        <div class="flex justify-end gap-2">
                            @if($p->is_active)
                            <button type="button" class="btn-success px-3 py-2 text-sm" data-open-receive data-product="{{$p->id}}" data-product-name="{{$p->code}} — {{$p->name}}" data-unit="{{$p->unit->name}}">+ รับเข้า</button>
                            @endif
                            <a class="btn-secondary px-3 py-2 text-sm" href="{{route('products.edit',$p)}}">แก้ไข</a>
                            <form method="post" action="{{route('products.destroy',$p)}}" onsubmit="return confirm('ยืนยันลบหรือปิดใช้งาน?')">@csrf @method('DELETE')<button class="btn-ghost px-3 py-2 text-sm text-rose-600">ลบ</button></form>
                        </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-state">ยังไม่มีสินค้า</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-5">{{$products->links()}}</div>

@if(auth()->user()->isAdmin())
<dialog id="receive-dialog" class="modal-dialog w-full max-w-2xl p-0">
    <form method="post" action="{{route('stock.receive.store')}}" class="overflow-hidden rounded-2xl bg-white">@csrf
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
            <div><div class="mb-2 grid size-11 place-items-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20"><svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M12 3v12m0 0 4-4m-4 4-4-4M5 20h14"/></svg></div><h3 class="text-2xl font-bold text-slate-950">รับสินค้าเข้าสต็อก</h3><p class="mt-1 text-slate-500">เลือกสินค้า คลัง และกรอกจำนวนที่รับจริง</p></div>
            <button type="button" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-close-modal aria-label="ปิด">✕</button>
        </div>
        <div class="grid gap-5 p-6 sm:grid-cols-2">
            <label class="sm:col-span-2"><span class="label">สินค้า *</span><select id="receive-product" class="select" name="product_id" required><option value="">— เลือกสินค้า —</option>@foreach($receiptProducts as $p)<option value="{{$p->id}}" data-unit="{{$p->unit->name}}">{{$p->code}} — {{$p->name}}</option>@endforeach</select></label>
            <label><span class="label">คลังสินค้า *</span><select class="select" name="warehouse_id" required><option value="">— เลือกคลัง —</option>@foreach($warehouses as $warehouse)<option value="{{$warehouse->id}}">{{$warehouse->code}} — {{$warehouse->name}}</option>@endforeach</select></label>
            <label><span class="label">จำนวนที่รับ *</span><div class="relative"><input class="input pr-20" type="number" name="quantity" min="0.0001" step="0.0001" placeholder="เช่น 100" required><span id="receive-unit" class="absolute right-4 top-3.5 font-semibold text-slate-500">หน่วย</span></div></label>
            <label class="sm:col-span-2"><span class="label">หมายเหตุ</span><textarea class="input" name="note" rows="3" placeholder="เลขที่ใบส่งของ หรือรายละเอียดเพิ่มเติม"></textarea></label>
        </div>
        <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4"><button type="button" class="btn-secondary" data-close-modal>ยกเลิก</button><button class="btn-success">✓ ยืนยันรับเข้าสต็อก</button></div>
    </form>
</dialog>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('receive-dialog');
    const product = document.getElementById('receive-product');
    const unit = document.getElementById('receive-unit');
    const updateUnit = () => unit.textContent = product?.selectedOptions[0]?.dataset.unit || 'หน่วย';
    document.querySelectorAll('[data-open-receive]').forEach(button => button.addEventListener('click', () => {
        if (button.dataset.product) product.value = button.dataset.product;
        updateUnit(); dialog?.showModal();
    }));
    document.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', () => button.closest('dialog')?.close()));
    product?.addEventListener('change', updateUnit);
    dialog?.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    @if(request('receive')) dialog?.showModal(); @endif
});
</script>
@endpush
