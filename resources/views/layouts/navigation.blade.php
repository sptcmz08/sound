@php
    $icon = 'size-[18px] shrink-0';
@endphp

<div class="space-y-5">
    <div>
        <p class="sidebar-section-title">เมนูหลัก</p>
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ $nav(request()->routeIs('dashboard')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12 12 3l9 9M5 10v10h14V10M9 20v-6h6v6"/></svg><span class="sidebar-label">หน้าหลัก</span>
            </a>
            <a href="{{ route('products.index') }}" class="sidebar-link {{ $nav(request()->routeIs('products.*')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 8-9-5-9 5 9 5 9-5ZM3 8v8l9 5 9-5V8"/></svg><span class="sidebar-label">รายการสินค้า</span>
            </a>
            <a href="{{ route('reports.balances') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.balances')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3"/></svg><span class="sidebar-label">สต็อกคงเหลือ</span>
            </a>
        </div>
    </div>

    @if(auth()->user()->canOperateStock())
    <div>
        <p class="sidebar-section-title">ทำรายการ</p>
        <div class="space-y-1">
            <a href="{{ route('operations.create', 'supplier-receive') }}" class="sidebar-link {{ $nav(request()->routeIs('operations.*') && request()->route('operation') === 'supplier-receive') }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"/></svg><span class="sidebar-label">รับเข้าสินค้า</span>
            </a>
            <a href="{{ route('requisitions.withdraw') }}" class="sidebar-link {{ $nav(request()->routeIs('requisitions.withdraw') || (request()->routeIs('requisitions.create') && !in_array(request('type'), ['BUILD_WIP','BUILD_FG']))) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 12h13m0 0-4-4m4 4-4 4M7 5H4v14h3"/></svg><span class="sidebar-label">เบิก-จ่ายสินค้า</span>
            </a>
            <a href="{{ route('requisitions.production') }}" class="sidebar-link {{ $nav(request()->routeIs('requisitions.production', 'requisitions.wip.*') || (request()->routeIs('requisitions.create') && in_array(request('type'), ['BUILD_WIP','BUILD_FG']))) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19h16M6 19V9l4 3V8l4 3V5h4v14"/></svg><span class="sidebar-label">ผลิต WIP / FG</span>
            </a>
            <a href="{{ route('operations.create', 'sale') }}" class="sidebar-link {{ $nav(request()->routeIs('operations.*') && request()->route('operation') === 'sale') }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 7h14l-1 13H6L5 7Zm3 0a4 4 0 0 1 8 0"/></svg><span class="sidebar-label">ขายสินค้า</span>
            </a>
            <a href="{{ route('operations.create', 'claim') }}" class="sidebar-link {{ $nav(request()->routeIs('operations.*') && request()->route('operation') === 'claim') }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v5h5M20 17v-5h-5M6 16a7 7 0 0 0 11 1m1-9A7 7 0 0 0 7 7"/></svg><span class="sidebar-label">เคลม / ของเสีย</span>
            </a>
        </div>
    </div>
    @endif

    <div>
        <p class="sidebar-section-title">รายงาน</p>
        <div class="space-y-1">
            <a href="{{ route('reports.cost-profit') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.cost-profit')) }}"><svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m4 17 5-5 4 3 7-8M16 7h4v4"/></svg><span class="sidebar-label">ต้นทุน - กำไร</span></a>
            <a href="{{ route('reports.issue') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.issue')) }}"><svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M6 3h9l3 3v15H6V3Zm3 6h6m-6 4h6m-6 4h4"/></svg><span class="sidebar-label">รายงานเบิก-จ่าย</span></a>
            <a href="{{ route('reports.sales') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.sales')) }}"><svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M4 5h16v14H4V5Zm3 9 3-3 3 2 4-5"/></svg><span class="sidebar-label">รายงานขาย</span></a>
            <a href="{{ route('reports.claims') }}" class="sidebar-link {{ $nav(request()->routeIs('reports.claims', 'reports.waste')) }}"><svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 3 4 7v5c0 5 3.4 8 8 9 4.6-1 8-4 8-9V7l-8-4Zm-3 9 2 2 4-4"/></svg><span class="sidebar-label">เคลมและของเสีย</span></a>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
    <div>
        <p class="sidebar-section-title">จัดการระบบ</p>
        <div class="space-y-1">
            <a href="{{ route('requisitions.issues') }}" class="sidebar-link {{ $nav(request()->routeIs('requisitions.issues', 'requisitions.approvals')) }}"><svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M9 12.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><span class="sidebar-label flex-1">อนุมัติรายการ</span>@if($pendingRequests)<span class="sidebar-label rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $pendingRequests }}</span>@endif</a>
            <a href="{{ route('users.index') }}" class="sidebar-link {{ $nav(request()->routeIs('users.*')) }}"><svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M16 19v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1m7-8a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-1v6m3-3h-6"/></svg><span class="sidebar-label">ผู้ใช้งาน</span></a>
            <a href="{{ route('settings') }}" class="sidebar-link {{ $nav(request()->routeIs('settings*')) }}"><svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7-3.5 2-1-2-3-2 .5-1.5-1L15 5h-6l-.5 2.5-1.5 1L5 8l-2 3 2 1v2l-2 1 2 3 2-.5 1.5 1L9 21h6l.5-2.5 1.5-1 2 .5 2-3-2-1v-2Z"/></svg><span class="sidebar-label">ตั้งค่าระบบ</span></a>
        </div>
    </div>
    @endif
</div>
