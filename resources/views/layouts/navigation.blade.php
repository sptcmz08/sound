<div class="mb-4 px-2">
    <a href="{{route('dashboard')}}" class="sidebar-link {{$nav(request()->routeIs('dashboard'))}}" title="ภาพรวมระบบ">
        <svg class="size-5 shrink-0" style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span class="sidebar-label">ภาพรวมระบบ</span>
    </a>
</div>

<details open class="sidebar-group mb-4">
    <summary class="sidebar-group-title flex cursor-pointer list-none [&::-webkit-details-marker]:hidden [&::marker]:hidden items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-slate-200">
        <span>รายการสินค้า & สต็อก</span>
        <svg class="size-3.5" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        @if(auth()->user()->isAdmin())
        <a href="{{route('products.index')}}" class="sidebar-link {{$nav(request()->routeIs('products.*'))}}" title="แคตตาล็อกสินค้า">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span class="sidebar-label">สินค้าทั้งหมด</span>
        </a>
        @endif
        <a href="{{route('reports.balances')}}" class="sidebar-link {{$nav(request()->routeIs('reports.balances'))}}" title="สต็อกคงเหลือ">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="sidebar-label">สต็อกคงเหลือ</span>
        </a>
        @if(auth()->user()->canOperateStock())
        <a href="{{route('requisitions.withdraw')}}" class="sidebar-link {{$nav(request()->routeIs('requisitions.withdraw') || (request()->routeIs('requisitions.create') && !in_array(request('type'),['BUILD_WIP','BUILD_FG'])))}}" title="เบิก-จ่าย">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span class="sidebar-label">เบิก-จ่ายสินค้า</span>
        </a>
        <a href="{{route('requisitions.production')}}" class="sidebar-link {{$nav(request()->routeIs('requisitions.production','requisitions.wip.*') || (request()->routeIs('requisitions.create') && in_array(request('type'),['BUILD_WIP','BUILD_FG'])))}}" title="ส่งเข้า WIP/FG">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="sidebar-label">ผลิต WIP / FG</span>
        </a>
        <a href="{{route('operations.create','supplier-receive')}}" class="sidebar-link {{$nav(request()->routeIs('operations.*') && request()->route('operation')==='supplier-receive')}}" title="รับเข้า Supplier">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <span class="sidebar-label">รับเข้า (Supplier)</span>
        </a>
        <a href="{{route('operations.create','sale')}}" class="sidebar-link {{$nav(request()->routeIs('operations.*') && request()->route('operation')==='sale')}}" title="ขาย">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="sidebar-label">ขายสินค้า (Sales)</span>
        </a>
        <a href="{{route('operations.create','claim')}}" class="sidebar-link {{$nav(request()->routeIs('operations.*') && request()->route('operation')==='claim')}}" title="เคลม">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span class="sidebar-label">รับเคลมจากลูกค้า</span>
        </a>
        @endif
    </div>
</details>

<details open class="sidebar-group mb-4">
    <summary class="sidebar-group-title flex cursor-pointer list-none [&::-webkit-details-marker]:hidden [&::marker]:hidden items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-slate-200">
        <span>รายงาน & สถิติ</span>
        <svg class="size-3.5" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        <a href="{{route('reports.cost-profit')}}" class="sidebar-link {{$nav(request()->routeIs('reports.cost-profit'))}}" title="ต้นทุน - กำไร">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            <span class="sidebar-label">ต้นทุน - กำไร</span>
        </a>
        <a href="{{route('reports.issue')}}" class="sidebar-link {{$nav(request()->routeIs('reports.issue'))}}" title="รายงานเบิก-จ่าย">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="sidebar-label">รายงานเบิก-จ่าย</span>
        </a>
        <a href="{{route('reports.sales')}}" class="sidebar-link {{$nav(request()->routeIs('reports.sales'))}}" title="รายงานขาย">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span class="sidebar-label">รายงานการขาย</span>
        </a>
        <a href="{{route('reports.claims')}}" class="sidebar-link {{$nav(request()->routeIs('reports.claims'))}}" title="รายงานเคลม">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            <span class="sidebar-label">รายงานเคลม</span>
        </a>
        <a href="{{route('reports.waste')}}" class="sidebar-link {{$nav(request()->routeIs('reports.waste'))}}" title="รายงานของเสีย">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <span class="sidebar-label">รายงานของเสีย</span>
        </a>
    </div>
</details>

@if(auth()->user()->isAdmin())
<details class="sidebar-group mb-2" open>
    <summary class="sidebar-group-title flex cursor-pointer list-none [&::-webkit-details-marker]:hidden [&::marker]:hidden items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-slate-200">
        <span>จัดการระบบ</span>
        <svg class="size-3.5" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        <a href="{{route('requisitions.issues')}}" class="sidebar-link {{$nav(request()->routeIs('requisitions.issues','requisitions.approvals'))}}">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="sidebar-label flex-1">อนุมัติรายการ</span>
            @if($pendingRequests)<span class="sidebar-label rounded-full bg-rose-500 px-2 py-0.5 text-xs font-bold text-white shadow-sm ring-2 ring-rose-300/30 animate-pulse">{{$pendingRequests}}</span>@endif
        </a>
        <a href="{{route('users.index')}}" class="sidebar-link {{$nav(request()->routeIs('users.*'))}}">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span class="sidebar-label">ผู้ใช้งาน</span>
        </a>
        <a href="{{route('settings')}}" class="sidebar-link {{$nav(request()->routeIs('settings*'))}}">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="sidebar-label">ตั้งค่าระบบ</span>
        </a>
    </div>
</details>
@endif
