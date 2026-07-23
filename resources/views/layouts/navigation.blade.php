@php
    $icon = 'size-5 shrink-0 transition-transform duration-200 group-hover:scale-110';
    $navActive = 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25 rounded-xl font-semibold';
    $navInactive = 'text-slate-300 hover:bg-white/10 hover:text-white rounded-xl font-medium';
    $navLink = fn (bool $active) => 'flex items-center gap-3 px-3 py-2.5 text-xs transition-all duration-200 group ' . ($active ? $navActive : $navInactive);
@endphp

<div class="space-y-4">
    {{-- ภาพรวม --}}
    <div>
        <p class="sidebar-section-title px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">ภาพรวม</p>
        <div class="space-y-0.5">
            <a href="{{ route('dashboard') }}" class="{{ $navLink(request()->routeIs('dashboard')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                <span class="sidebar-label truncate">แดชบอร์ด</span>
            </a>
        </div>
    </div>

    {{-- ข้อมูลหลัก --}}
    <div>
        <p class="sidebar-section-title px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">ข้อมูลหลัก</p>
        <div class="space-y-0.5">
            @if(auth()->user()?->canOperateStock())
            <a href="{{ route('requisitions.withdraw') }}" class="{{ $navLink(request()->routeIs('requisitions.withdraw') || (request()->routeIs('requisitions.create') && !str_starts_with((string) request('type'), 'BUILD_'))) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                <span class="sidebar-label truncate">เบิกออกสต็อก</span>
            </a>
            <a href="{{ route('requisitions.production') }}" class="{{ $navLink(request()->routeIs('requisitions.production', 'requisitions.wip.*') || (request()->routeIs('requisitions.create') && str_starts_with((string) request('type'), 'BUILD_'))) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6.75M9 11.25h6.75M9 15.75h6.75"/></svg>
                <span class="sidebar-label truncate">ผลิต WIP / FG</span>
            </a>
            <a href="{{ route('operations.create', 'supplier-receive') }}" class="{{ $navLink(request()->routeIs('operations.*') && request()->route('operation') === 'supplier-receive') }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12L12 16.5m0 0l4.5-4.5M12 16.5V3"/></svg>
                <span class="sidebar-label truncate">รับเข้าจาก Supplier</span>
            </a>
            <a href="{{ route('operations.create', 'sale') }}" class="{{ $navLink(request()->routeIs('operations.*') && request()->route('operation') === 'sale') }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                <span class="sidebar-label truncate">บันทึกการขาย</span>
            </a>
            <a href="{{ route('operations.create', 'claim') }}" class="{{ $navLink(request()->routeIs('operations.*') && request()->route('operation') === 'claim') }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                <span class="sidebar-label truncate">เคลมสินค้า</span>
            </a>
            @endif
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('products.index') }}" class="{{ $navLink(request()->routeIs('products.*')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                <span class="sidebar-label truncate">รายการสินค้าทั้งหมด</span>
            </a>
            @endif
        </div>
    </div>

    {{-- สต็อกสินค้า --}}
    <div>
        <p class="sidebar-section-title px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">สต็อกสินค้า</p>
        <div class="space-y-0.5">
            <a href="{{ route('reports.balances') }}" class="{{ $navLink(request()->routeIs('reports.balances')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1.5-3m8.25 3l1.5-3m-10.5 0h10.5M6 7.5h12M6 10.5h12"/></svg>
                <span class="sidebar-label truncate">ยอดคงเหลือ</span>
            </a>
            <a href="{{ route('requisitions.index') }}" class="{{ $navLink(request()->routeIs('requisitions.index')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="sidebar-label truncate">ประวัติใบเบิกและผลิต</span>
            </a>
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('requisitions.approvals') }}" class="{{ $navLink(request()->routeIs('requisitions.approvals')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="sidebar-label truncate">รออนุมัติใบเบิก</span>
            </a>
            @endif
        </div>
    </div>

    {{-- รายงาน --}}
    <div>
        <p class="sidebar-section-title px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">รายงาน</p>
        <div class="space-y-0.5">
            <a href="{{ route('reports.cost-profit') }}" class="{{ $navLink(request()->routeIs('reports.cost-profit')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 005.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                <span class="sidebar-label truncate">ต้นทุน - กำไร</span>
            </a>
            <a href="{{ route('reports.issue') }}" class="{{ $navLink(request()->routeIs('reports.issue')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <span class="sidebar-label truncate">เบิก - จ่าย</span>
            </a>
            <a href="{{ route('reports.sales') }}" class="{{ $navLink(request()->routeIs('reports.sales')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                <span class="sidebar-label truncate">สรุปยอดขาย</span>
            </a>
            <a href="{{ route('reports.claims') }}" class="{{ $navLink(request()->routeIs('reports.claims')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751A11.959 11.959 0 0112 2.714z"/></svg>
                <span class="sidebar-label truncate">รายงานเคลม</span>
            </a>
            <a href="{{ route('reports.waste') }}" class="{{ $navLink(request()->routeIs('reports.waste')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                <span class="sidebar-label truncate">รายงานของเสีย</span>
            </a>
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('audits') }}" class="{{ $navLink(request()->routeIs('audits')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="sidebar-label truncate">Audit Logs</span>
            </a>
            @endif
        </div>
    </div>

    {{-- ตั้งค่า --}}
    @if(auth()->user()?->isAdmin())
    <div>
        <p class="sidebar-section-title px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">ตั้งค่า</p>
        <div class="space-y-0.5">
            <a href="{{ route('users.index') }}" class="{{ $navLink(request()->routeIs('users.*')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                <span class="sidebar-label truncate">ผู้ใช้งาน</span>
            </a>
            <a href="{{ route('settings') }}" class="{{ $navLink(request()->routeIs('settings')) }}">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l.546.946c.275.476.17.1.082-1.286-.456l-1.022-.383c-.354-.133-.615-.436-.679-.81a13.313 13.313 0 00-.012-.255c-.014-.38.163-.739.467-.968l1.045-.784a1.125 1.125 0 00.32-1.451l-.547-.947a1.125 1.125 0 00-1.37-.491l-1.216.456c-.355.133-.75.072-1.076-.124a6.57 6.57 0 00-.22-.128c-.331-.183-.581-.495-.644-.869l-.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="sidebar-label truncate">ตั้งค่าระบบ</span>
            </a>
        </div>
    </div>
    @endif
</div>
