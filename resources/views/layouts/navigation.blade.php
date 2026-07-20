<div class="mb-5 px-3">
    <a href="{{route('dashboard')}}" class="sidebar-link {{$nav(request()->routeIs('dashboard'))}}" title="ภาพรวม">
        <span class="grid size-5 shrink-0 place-items-center">⌂</span><span class="sidebar-label">ภาพรวม</span>
    </a>
</div>

<details open class="sidebar-group mb-4">
    <summary class="sidebar-group-title flex cursor-pointer list-none items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-500">รายการ <span>⌄</span></summary>
    <div class="space-y-1">
        @if(auth()->user()->canOperateStock())
        <a href="{{route('requisitions.withdraw')}}" class="sidebar-link {{$nav(request()->routeIs('requisitions.withdraw') || (request()->routeIs('requisitions.create') && !in_array(request('type'),['BUILD_WIP','BUILD_FG'])))}}" title="เบิก-จ่าย"><span class="grid size-5 shrink-0 place-items-center">↗</span><span class="sidebar-label">เบิก-จ่าย</span></a>
        <a href="{{route('requisitions.production')}}" class="sidebar-link {{$nav(request()->routeIs('requisitions.production','requisitions.wip.*') || (request()->routeIs('requisitions.create') && in_array(request('type'),['BUILD_WIP','BUILD_FG'])))}}" title="ส่งเข้า WIP FG"><span class="grid size-5 shrink-0 place-items-center">⚙</span><span class="sidebar-label">ส่งเข้า WIP / FG</span></a>
        @endif
        <a href="{{route('reports.balances')}}" class="sidebar-link {{$nav(request()->routeIs('reports.balances'))}}" title="สต็อกคงเหลือ"><span class="grid size-5 shrink-0 place-items-center">▤</span><span class="sidebar-label">สต็อกคงเหลือ</span></a>
        @if(auth()->user()->canOperateStock())
        <a href="{{route('operations.create','supplier-receive')}}" class="sidebar-link {{$nav(request()->routeIs('operations.*') && request()->route('operation')==='supplier-receive')}}" title="รับเข้า Supplier"><span class="grid size-5 shrink-0 place-items-center">↓</span><span class="sidebar-label">รับเข้า (Supplier)</span></a>
        @endif
        @if(auth()->user()->isAdmin())
        <a href="{{route('products.index')}}" class="sidebar-link {{$nav(request()->routeIs('products.*'))}}" title="เพิ่มรายการ"><span class="grid size-5 shrink-0 place-items-center">＋</span><span class="sidebar-label">เพิ่ม WIP / FG / PART</span></a>
        @endif
        @if(auth()->user()->canOperateStock())
        <a href="{{route('operations.create','sale')}}" class="sidebar-link {{$nav(request()->routeIs('operations.*') && request()->route('operation')==='sale')}}" title="ขาย"><span class="grid size-5 shrink-0 place-items-center">฿</span><span class="sidebar-label">ขาย</span></a>
        <a href="{{route('operations.create','claim')}}" class="sidebar-link {{$nav(request()->routeIs('operations.*') && request()->route('operation')==='claim')}}" title="เคลม"><span class="grid size-5 shrink-0 place-items-center">↩</span><span class="sidebar-label">เคลม</span></a>
        @endif
    </div>
</details>

<details open class="sidebar-group mb-4">
    <summary class="sidebar-group-title flex cursor-pointer list-none items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-500">รายงาน <span>⌄</span></summary>
    <div class="space-y-1">
        <a href="{{route('reports.cost-profit')}}" class="sidebar-link {{$nav(request()->routeIs('reports.cost-profit'))}}" title="ต้นทุน - กำไร"><span class="grid size-5 shrink-0 place-items-center">◒</span><span class="sidebar-label">ต้นทุน - กำไร</span></a>
        <a href="{{route('reports.issue')}}" class="sidebar-link {{$nav(request()->routeIs('reports.issue'))}}" title="รายงานเบิก-จ่าย"><span class="grid size-5 shrink-0 place-items-center">↗</span><span class="sidebar-label">เบิก - จ่าย</span></a>
        <a href="{{route('reports.sales')}}" class="sidebar-link {{$nav(request()->routeIs('reports.sales'))}}" title="รายงานขาย"><span class="grid size-5 shrink-0 place-items-center">฿</span><span class="sidebar-label">ขาย</span></a>
        <a href="{{route('reports.claims')}}" class="sidebar-link {{$nav(request()->routeIs('reports.claims'))}}" title="รายงานเคลม"><span class="grid size-5 shrink-0 place-items-center">↩</span><span class="sidebar-label">เคลม (จากลูกค้า)</span></a>
        <a href="{{route('reports.waste')}}" class="sidebar-link {{$nav(request()->routeIs('reports.waste'))}}" title="รายงานของเสีย"><span class="grid size-5 shrink-0 place-items-center">!</span><span class="sidebar-label">ของเสีย</span></a>
    </div>
</details>

@if(auth()->user()->isAdmin())
<details class="sidebar-group mb-2">
    <summary class="sidebar-group-title flex cursor-pointer list-none items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-500">จัดการระบบ <span>⌄</span></summary>
    <div class="space-y-1">
        <a href="{{route('requisitions.issues')}}" class="sidebar-link {{$nav(request()->routeIs('requisitions.issues','requisitions.approvals'))}}"><span class="grid size-5 shrink-0 place-items-center">✓</span><span class="sidebar-label flex-1">อนุมัติรายการ</span>@if($pendingRequests)<span class="sidebar-label rounded-full bg-rose-500 px-2 py-0.5 text-xs font-bold text-white">{{$pendingRequests}}</span>@endif</a>
        <a href="{{route('users.index')}}" class="sidebar-link {{$nav(request()->routeIs('users.*'))}}"><span class="grid size-5 shrink-0 place-items-center">●</span><span class="sidebar-label">ผู้ใช้งาน</span></a>
        <a href="{{route('settings')}}" class="sidebar-link {{$nav(request()->routeIs('settings*'))}}"><span class="grid size-5 shrink-0 place-items-center">⚙</span><span class="sidebar-label">ตั้งค่าระบบ</span></a>
    </div>
</details>
@endif
