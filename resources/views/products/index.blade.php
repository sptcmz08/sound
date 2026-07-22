@extends('layouts.app')

@section('title', 'แคตตาล็อกสินค้า & การรับเข้าสต็อก')
@section('header', 'แคตตาล็อกสินค้า (Product Catalog)')

@section('content')
<div class="space-y-6">
    {{-- Header Title Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="badge-blue">Inventory Master</span>
                <span class="text-xs text-slate-400 font-semibold">• พบ {{ $products->total() }} รายการ</span>
            </div>
            <h2 class="page-title">รายการสินค้าทั้งหมด</h2>
            <p class="page-subtitle">บริหารจัดการ PART (อะไหล่), SUPPLY (วัสดุสิ้นเปลือง), WIP (งานประกอบ) และ FG (สินค้าสำเร็จรูป)</p>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('operations.create', ['operation' => 'supplier-receive', 'type' => 'PART']) }}" class="btn-success shadow-lg shadow-emerald-500/20">
                📦 รับ PART เข้า (อะไหล่)
            </a>
            <a href="{{ route('operations.create', ['operation' => 'supplier-receive', 'type' => 'SUPPLY']) }}" class="btn-secondary shadow-sm">
                🧪 รับ SUPPLY เข้า (สิ้นเปลือง)
            </a>
            <a href="{{ route('products.create') }}" class="btn-primary shadow-lg shadow-blue-500/20">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                + เพิ่มสินค้าใหม่ / สูตร BOM
            </a>
        </div>
        @endif
    </div>

    {{-- Product Type Tabs --}}
    <div class="grid grid-cols-2 gap-2 rounded-3xl bg-slate-200/60 p-1.5 sm:grid-cols-4">
        <a href="{{ route('products.index', ['type' => 'PART']) }}" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-xs font-bold transition-all sm:text-sm no-underline text-decoration-none {{ request('type', 'PART') === 'PART' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900' }}">
            <span class="size-2 rounded-full bg-blue-500"></span>
            PART (อะไหล่ผลิต)
        </a>
        <a href="{{ route('products.index', ['type' => 'SUPPLY']) }}" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-xs font-bold transition-all sm:text-sm no-underline text-decoration-none {{ request('type') === 'SUPPLY' ? 'bg-white text-slate-900 shadow-sm ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900' }}">
            <span class="size-2 rounded-full bg-slate-500"></span>
            SUPPLY (วัสดุสิ้นเปลือง)
        </a>
        <a href="{{ route('products.index', ['type' => 'WIP']) }}" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-xs font-bold transition-all sm:text-sm no-underline text-decoration-none {{ request('type') === 'WIP' ? 'bg-white text-violet-700 shadow-sm ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900' }}">
            <span class="size-2 rounded-full bg-violet-500"></span>
            WIP (งานประกอบ)
        </a>
        <a href="{{ route('products.index', ['type' => 'FG']) }}" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-xs font-bold transition-all sm:text-sm no-underline text-decoration-none {{ request('type') === 'FG' ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900' }}">
            <span class="size-2 rounded-full bg-emerald-500"></span>
            FG (สินค้าสำเร็จรูป)
        </a>
    </div>

    {{-- Search & Filter Bar --}}
    <form class="panel flex flex-wrap items-center gap-3 p-4">
        <div class="relative min-w-64 flex-1">
            <svg class="pointer-events-none absolute left-4 top-3.5 size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
            <input class="input pl-11" name="q" value="{{ request('q') }}" placeholder="พิมพ์ค้นหารหัส SKU หรือชื่อสินค้า...">
        </div>
        <select class="select w-full sm:w-56" name="type">
            <option value="PART" @selected(request('type','PART')==='PART')>PART (อะไหล่ผลิต)</option>
            <option value="SUPPLY" @selected(request('type')==='SUPPLY')>SUPPLY (วัสดุสิ้นเปลือง)</option>
            <option value="WIP" @selected(request('type')==='WIP')>WIP (งานประกอบ)</option>
            <option value="FG" @selected(request('type')==='FG')>FG (สินค้าสำเร็จรูป)</option>
        </select>
        <button class="btn-secondary">ค้นหา</button>
    </form>

    {{-- Products Table --}}
    <div class="table-shell">
        <div class="panel-header flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-slate-900">รายการสินค้า</h3>
                <p class="text-xs text-slate-500">แสดงผลยอดคงเหลือรวมทุกคลังสินค้า</p>
            </div>
            <span class="badge-slate">Real-time Balance</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>สินค้า (SKU / Code)</th>
                        <th>ประเภท</th>
                        <th class="text-right">คงเหลือรวม</th>
                        <th>หน่วยนับ</th>
                        <th>สูตร BOM</th>
                        <th>สถานะ</th>
                        <th class="text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-product-image :product="$p" />
                                <div>
                                    <strong class="block text-slate-900 font-bold hover:text-blue-600 transition-colors">{{ $p->name }}</strong>
                                    <span class="font-mono text-xs font-semibold text-slate-400">{{ $p->code }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="{{ $p->product_type->value==='PART' ? 'badge-part' : ($p->product_type->value==='SUPPLY' ? 'badge-supply' : ($p->product_type->value==='WIP' ? 'badge-wip' : 'badge-fg')) }}">
                                {{ $p->product_type->label() }}
                            </span>
                        </td>
                        <td class="text-right">
                            <strong class="text-lg font-black text-slate-900">
                                {{ \App\Support\Quantity::format($p->balances_sum_quantity ?? 0) }}
                            </strong>
                        </td>
                        <td class="font-medium text-slate-600">{{ $p->unit->name }}</td>
                        <td>
                            @if(in_array($p->product_type->value, ['PART', 'SUPPLY'], true))
                                <span class="text-slate-300">—</span>
                            @else
                                <span class="badge-slate">{{ $p->components_count }} รายการส่วนประกอบ</span>
                            @endif
                        </td>
                        <td>
                            <span class="{{ $p->is_active ? 'badge-green' : 'badge-slate' }}">
                                {{ $p->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if(auth()->user()->isAdmin())
                            <div class="flex justify-end items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3 font-semibold flex items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#quickImageModal" data-product-id="{{ $p->id }}" data-product-name="{{ $p->code }} — {{ $p->name }}" data-image-url="{{ $p->image_path ? route('products.image', $p) : '' }}" title="อัปโหลด/เปลี่ยนรูปภาพสินค้า">
                                    <i class="bi bi-camera-fill"></i>
                                    <span>รูปภาพ</span>
                                </button>
                                @if($p->is_active && in_array($p->product_type->value, ['PART', 'SUPPLY'], true))
                                <button type="button" class="btn btn-sm btn-success rounded-3 font-semibold px-3 py-1.5 shadow-sm flex items-center gap-1" data-open-receive data-bs-toggle="modal" data-bs-target="#receiveModal" data-product="{{ $p->id }}">
                                    <i class="bi bi-plus-lg"></i> รับเข้า
                                </button>
                                @endif
                                <a class="btn-secondary px-3 py-1.5 text-xs font-bold" href="{{ route('products.edit', $p) }}">แก้ไข</a>
                                <form method="post" action="{{ route('products.destroy', $p) }}" onsubmit="return confirm('ยืนยันลบหรือปิดใช้งานสินค้า?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-ghost px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50">ลบ</button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <strong class="block text-slate-700">ไม่พบรายการสินค้า</strong>
                            <span class="text-xs text-slate-400">ยังไม่มีสินค้าประเภทนี้ หรือลองค้นหาด้วยคำอื่น</span>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>

    {{-- Receive Stock Quick Modal (Bootstrap 5) --}}
    @if(auth()->user()->isAdmin())
    <div class="modal fade" id="receiveModal" tabindex="-1" aria-labelledby="receiveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <form method="post" action="{{ route('stock.receive.store') }}" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                @csrf
                <div class="modal-header bg-emerald-600 bg-gradient text-white p-4">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-xl bg-white/20 text-white">
                            <i class="bi bi-box-arrow-in-down fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-bold text-white mb-0" id="receiveModalLabel">รับสินค้าเข้าสต็อก (Supplier)</h5>
                            <small class="text-emerald-100">เลือกสินค้า คลังสินค้าปลายทาง และกรอกจำนวนที่รับจริง</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 space-y-4">
                    {{-- Type Selector Tabs (Separating PART and SUPPLY) --}}
                    <div class="nav nav-pills nav-fill bg-slate-100 p-1 rounded-3" id="receive-type-toggle">
                        <button type="button" class="nav-link active rounded-3 font-bold py-2 text-sm" data-type="PART">
                            📦 รับ PART (อะไหล่ผลิต)
                        </button>
                        <button type="button" class="nav-link rounded-3 font-bold py-2 text-sm text-slate-600" data-type="SUPPLY">
                            🧪 รับ SUPPLY (สิ้นเปลือง)
                        </button>
                    </div>
                    <div id="receive-type-help" class="text-xs font-semibold px-3 py-2 rounded-2xl bg-blue-50 text-blue-700 border border-blue-200">
                        📦 <strong>รับเข้า PART:</strong> อะไหล่/ชิ้นส่วนผลิตสำหรับระบุในสูตร BOM
                    </div>

                    <div>
                        <label class="label">สินค้าที่ต้องการรับเข้า *</label>
                        <select id="receive-product" class="select" name="product_id" required>
                            <option value="">— เลือกสินค้า —</option>
                            @foreach($receiptProducts as $p)
                            <option value="{{ $p->id }}" data-type="{{ $p->product_type->value }}" data-unit="{{ $p->unit->name }}" data-image="{{ $p->image_path ? route('products.image', $p) : '' }}">
                                [{{ $p->product_type->value }}] {{ $p->code }} — {{ $p->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="receive-product-preview" class="hidden items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <img class="size-14 rounded-xl border border-slate-200 bg-white object-cover shadow-sm" alt="รูปสินค้าที่เลือก">
                        <div>
                            <strong class="block text-sm text-slate-900">รูปภาพสินค้า</strong>
                            <span class="text-xs text-slate-500">ตรวจสอบรายละเอียดสินค้าก่อนบันทึกรับเข้าคลัง</span>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label">คลังสินค้าปลายทาง *</label>
                            <select class="select" name="warehouse_id" required>
                                <option value="">— เลือกคลัง —</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->code }} — {{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">จำนวนที่รับเข้าจริง *</label>
                            <div class="relative">
                                <input class="input pr-16" type="number" name="quantity" min="0.0001" step="0.0001" placeholder="เช่น 100" required>
                                <span id="receive-unit" class="absolute right-4 top-3.5 font-bold text-slate-400 text-xs uppercase">หน่วย</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="label">หมายเหตุ / เลขที่ใบส่งสินค้า</label>
                        <textarea class="input" name="note" rows="2" placeholder="ระบุเลขที่ใบส่งของ หรือรายละเอียดการรับเข้า..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary rounded-3 px-4 font-bold" data-bs-dismiss="modal">ยกเลิก</button>
                    <button class="btn btn-success rounded-3 px-4 font-bold">
                        <i class="bi bi-check-lg me-1"></i> ยืนยันรับเข้าสต็อก
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Quick Image Upload Modal (Bootstrap 5) --}}
    @if(auth()->user()->isAdmin())
    <div class="modal fade" id="quickImageModal" tabindex="-1" aria-labelledby="quickImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="quick-image-form" method="post" action="" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                @csrf
                <div class="modal-header bg-primary text-white p-4">
                    <h5 class="modal-title font-bold flex items-center gap-2" id="quickImageModalLabel">
                        <i class="bi bi-camera-fill"></i> อัปโหลดรูปภาพสินค้า
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p id="quick-image-product-name" class="fw-bold text-dark fs-5 mb-3"></p>
                    <div class="mb-4">
                        <img id="quick-image-preview" src="" class="img-thumbnail rounded-4 shadow-sm mx-auto d-block" style="max-height: 200px; display: none;" alt="รูปตัวอย่าง">
                        <div id="quick-image-placeholder" class="p-5 border border-2 border-dashed rounded-4 bg-light text-muted">
                            <i class="bi bi-cloud-arrow-up text-primary display-4 d-block mb-2"></i>
                            <span>เลือกรูปภาพสินค้า (JPG, PNG, WEBP)</span>
                        </div>
                    </div>
                    <div class="input-group">
                        <input type="file" name="image" id="quick-image-input" class="form-control rounded-3" accept="image/jpeg,image/png,image/jpg,image/webp" required>
                    </div>
                    <small class="text-muted d-block mt-2">ขนาดไฟล์ไม่เกิน 2 MB</small>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary rounded-3 px-4 font-bold" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 font-bold">
                        <i class="bi bi-check-lg me-1"></i> บันทึกรูปภาพ
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Receive Stock Modal JS
    const receiveModalEl = document.getElementById('receiveModal');
    if (receiveModalEl) {
        const productSelect = document.getElementById('receive-product');
        const unitLabel = document.getElementById('receive-unit');
        const previewEl = document.getElementById('receive-product-preview');
        const typeToggle = document.getElementById('receive-type-toggle');
        const typeHelp = document.getElementById('receive-type-help');
        let activeType = 'PART';

        const filterProducts = () => {
            if (!productSelect) return;
            const options = productSelect.querySelectorAll('option[data-type]');
            options.forEach(opt => {
                opt.style.display = opt.dataset.type === activeType ? '' : 'none';
                if (opt.dataset.type !== activeType && opt.selected) opt.selected = false;
            });
            productSelect.value = '';
            updateReceiveUnit();
        };

        if (typeToggle) {
            typeToggle.querySelectorAll('button[data-type]').forEach(btn => {
                btn.addEventListener('click', () => {
                    activeType = btn.dataset.type;
                    typeToggle.querySelectorAll('button').forEach(b => {
                        b.classList.toggle('active', b === btn);
                        b.classList.toggle('text-slate-600', b !== btn);
                    });
                    if (typeHelp) {
                        typeHelp.className = activeType === 'SUPPLY'
                            ? 'text-xs font-semibold px-3 py-2 rounded-2xl bg-slate-50 text-slate-700 border border-slate-200'
                            : 'text-xs font-semibold px-3 py-2 rounded-2xl bg-blue-50 text-blue-700 border border-blue-200';
                        typeHelp.innerHTML = activeType === 'SUPPLY'
                            ? '🧪 <strong>รับเข้า SUPPLY:</strong> วัสดุสิ้นเปลือง (เช่น กาว, น้ำยา, เทป) ไม่ต้องระบุจำนวนต่อ BOM'
                            : '📦 <strong>รับเข้า PART:</strong> อะไหล่/ชิ้นส่วนผลิตสำหรับระบุในสูตร BOM';
                    }
                    filterProducts();
                });
            });
        }

        const updateReceiveUnit = () => {
            const selected = productSelect?.selectedOptions[0];
            if (unitLabel) unitLabel.textContent = selected?.dataset.unit || 'หน่วย';
            const src = selected?.dataset.image;
            previewEl?.classList.toggle('hidden', !src);
            previewEl?.classList.toggle('flex', !!src);
            if (src && previewEl) previewEl.querySelector('img').src = src;
        };

        receiveModalEl.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const productId = button?.getAttribute('data-product');
            if (productId && productSelect) {
                const opt = productSelect.querySelector(`option[value="${productId}"]`);
                if (opt && opt.dataset.type) {
                    activeType = opt.dataset.type;
                    typeToggle?.querySelectorAll('button').forEach(b => {
                        b.classList.toggle('active', b.dataset.type === activeType);
                        b.classList.toggle('text-slate-600', b.dataset.type !== activeType);
                    });
                }
                filterProducts();
                productSelect.value = productId;
            } else {
                filterProducts();
            }
            updateReceiveUnit();
        });

        productSelect?.addEventListener('change', updateReceiveUnit);
        @if(request('receive'))
        new bootstrap.Modal(receiveModalEl).show();
        @endif
        filterProducts();
    }

    // Quick Image Modal JS
    const quickImageModal = document.getElementById('quickImageModal');
    if (quickImageModal) {
        quickImageModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const productId = button.getAttribute('data-product-id');
            const productName = button.getAttribute('data-product-name');
            const imageUrl = button.getAttribute('data-image-url');

            document.getElementById('quick-image-product-name').textContent = productName;
            document.getElementById('quick-image-form').action = `/products/${productId}/quick-image`;

            const previewImg = document.getElementById('quick-image-preview');
            const placeholder = document.getElementById('quick-image-placeholder');
            if (imageUrl) {
                previewImg.src = imageUrl;
                previewImg.style.display = 'block';
                placeholder.style.display = 'none';
            } else {
                previewImg.style.display = 'none';
                placeholder.style.display = 'block';
            }
        });

        document.getElementById('quick-image-input')?.addEventListener('change', event => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    const previewImg = document.getElementById('quick-image-preview');
                    const placeholder = document.getElementById('quick-image-placeholder');
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    placeholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endpush
