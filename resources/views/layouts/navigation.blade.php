@php $icon = 'size-[18px] shrink-0'; @endphp

<div class="space-y-5">
    <div>
        <p class="sidebar-section-title">รายการ</p>
        <div class="space-y-1">
            @if(auth()->user()->canOperateStock())
            <a href="{{ route('requisitions.withdraw') }}" class="sidebar-link {{ $nav(request()->routeIs('requisitions.withdraw') || (request()->routeIs('requisitions.create') && !str_starts_with((string) request('type'), 'BUILD_'))) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 12h13m0 0-4-4m4 4-4 4M7 5H4v14h3"/></svg>
                <span class="sidebar-label">เบิก-จ่าย (จากสต็อก)</span>
            </a>
            <a href="{{ route('requisitions.production') }}" class="sidebar-link {{ $nav(request()->routeIs('requisitions.production', 'requisitions.wip.*') || (request()->routeIs('requisitions.create') && str_starts_with((string) request('type'), 'BUILD_'))) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19h16M6 19V9l4 3V8l4 3V5h4v14"/></svg>
                <span class="sidebar-label">ส่งเข้า WIP / FG</span>
            </a>
            @endif

            <a href="{{ route('reports.balances') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.balances')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3"/></svg>
                <span class="sidebar-label">สต็อกคงเหลือ</span>
            </a>

            @if(auth()->user()->canOperateStock())
            <a href="{{ route('operations.create', 'supplier-receive') }}" class="sidebar-link {{ $nav(request()->routeIs('operations.*') && request()->route('operation') === 'supplier-receive') }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"/></svg>
                <span class="sidebar-label">รับเข้า (Supplier)</span>
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('products.index') }}" class="sidebar-link {{ $nav(request()->routeIs('products.*')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14M5 12h14m9-5-9-5-9 5 9 5 9-5Z"/></svg>
                <span class="sidebar-label">เพิ่ม PART / WIP / FG / สิ้นเปลือง</span>
            </a>
            @endif
            <a href="{{ route('operations.create', 'sale') }}" class="sidebar-link {{ $nav(request()->routeIs('operations.*') && request()->route('operation') === 'sale') }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 7h14l-1 13H6L5 7Zm3 0a4 4 0 0 1 8 0"/></svg>
                <span class="sidebar-label">ขาย</span>
            </a>
            <a href="{{ route('operations.create', 'claim') }}" class="sidebar-link {{ $nav(request()->routeIs('operations.*') && request()->route('operation') === 'claim') }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v5h5M20 17v-5h-5M6 16a7 7 0 0 0 11 1m1-9A7 7 0 0 0 7 7"/></svg>
                <span class="sidebar-label">เคลม</span>
            </a>
            @endif
        </div>
    </div>

    <div>
        <p class="sidebar-section-title">รายงาน</p>
        <div class="space-y-1">
            <a href="{{ route('reports.cost-profit') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.cost-profit')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m4 17 5-5 4 3 7-8M16 7h4v4"/></svg>
                <span class="sidebar-label">ต้นทุน - กำไร</span>
            </a>
            <a href="{{ route('reports.issue') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.issue')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M6 3h9l3 3v15H6V3Zm3 6h6m-6 4h6m-6 4h4"/></svg>
                <span class="sidebar-label">เบิก - จ่าย</span>
            </a>
            <a href="{{ route('reports.sales') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.sales')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M4 5h16v14H4V5Zm3 9 3-3 3 2 4-5"/></svg>
                <span class="sidebar-label">ขาย</span>
            </a>
            <a href="{{ route('reports.claims') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.claims')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 3 4 7v5c0 5 3.4 8 8 9 4.6-1 8-4 8-9V7l-8-4Zm-3 9 2 2 4-4"/></svg>
                <span class="sidebar-label">เคลม (จากลูกค้า)</span>
            </a>
            <a href="{{ route('reports.waste') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.waste')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m5 5 14 14M8 3h8l1 4H7l1-4Zm-2 4h12l-1 14H7L6 7Z"/></svg>
                <span class="sidebar-label">ของเสีย (ลูกค้า + การผลิต)</span>
            </a>
        </div>
    </div>
</div>
