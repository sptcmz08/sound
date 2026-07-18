@extends('layouts.app')
@section('title','ลายเซ็นออนไลน์')
@section('header','ลายเซ็นออนไลน์ของฉัน')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div><h2 class="page-title">ตั้งค่าลายเซ็นออนไลน์</h2><p class="page-subtitle">บันทึกลายเซ็นไว้ใช้กับใบเบิก และยืนยันทุกครั้งด้วย PIN 4 หลัก</p></div>
    <form method="post" action="{{route('signature.update')}}" id="signature-form" class="panel">@csrf
        <div class="panel-header"><div><h3 class="text-xl font-bold text-slate-950">ลายเซ็นของ {{auth()->user()->name}}</h3><p class="text-sm text-slate-500">วาดด้วยเมาส์/นิ้ว หรืออัปโหลด PNG/JPG ไม่เกิน 2MB</p></div>@if($signature)<span class="badge-green">บันทึกแล้ว</span>@endif</div>
        <div class="panel-body space-y-5">
            @if($signature)<div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><span class="label">ลายเซ็นปัจจุบัน</span><img src="{{route('signature.show',$signature)}}" class="h-28 max-w-full object-contain" alt="ลายเซ็นปัจจุบัน"></div>@endif
            <div class="flex gap-2"><button type="button" id="tab-draw" class="btn-primary">วาดลายเซ็น</button><button type="button" id="tab-upload" class="btn-secondary">อัปโหลดไฟล์</button></div>
            <div id="draw-area"><canvas id="signature-canvas" width="900" height="280" class="h-52 w-full touch-none rounded-xl border-2 border-dashed border-slate-300 bg-white"></canvas><div class="mt-2 flex justify-between"><small class="text-slate-500">ลากเมาส์หรือนิ้วเพื่อลงลายเซ็น</small><button type="button" id="clear-signature" class="font-semibold text-rose-600">ล้าง</button></div></div>
            <div id="upload-area" class="hidden"><label class="block cursor-pointer rounded-xl border-2 border-dashed border-slate-300 p-10 text-center hover:border-blue-400"><strong class="block text-lg">เลือกไฟล์ลายเซ็น</strong><span class="text-slate-500">PNG หรือ JPG ขนาดไม่เกิน 2MB</span><input id="signature-file" type="file" accept="image/png,image/jpeg" class="hidden"></label><img id="upload-preview" class="mt-4 hidden h-40 max-w-full object-contain" alt="ตัวอย่าง"></div>
            <input type="hidden" name="signature_data" id="signature-data">
            <label class="block max-w-sm"><span class="label">PIN สำหรับยืนยันลายเซ็น 4 หลัก *</span><input class="input text-center text-2xl tracking-[.5em]" type="password" name="pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required autocomplete="new-password" placeholder="••••"><small class="mt-2 block text-slate-500">ระบบจัดเก็บ PIN แบบเข้ารหัส</small></label>
        </div>
        <div class="flex justify-end border-t border-slate-100 p-5"><button class="btn-success px-8">บันทึกลายเซ็น</button></div>
    </form>
</div>
@endsection
@push('scripts')
<script>
const canvas=document.getElementById('signature-canvas'),ctx=canvas.getContext('2d'),data=document.getElementById('signature-data');
ctx.lineWidth=5;ctx.lineCap='round';ctx.lineJoin='round';ctx.strokeStyle='#0f172a';let drawing=false,drawn=false;
function point(e){const r=canvas.getBoundingClientRect(),p=e.touches?.[0]||e;return[(p.clientX-r.left)*canvas.width/r.width,(p.clientY-r.top)*canvas.height/r.height]}
function start(e){e.preventDefault();drawing=drawn=true;ctx.beginPath();ctx.moveTo(...point(e))}function move(e){if(!drawing)return;e.preventDefault();ctx.lineTo(...point(e));ctx.stroke()}function end(){drawing=false}
canvas.addEventListener('pointerdown',start);canvas.addEventListener('pointermove',move);canvas.addEventListener('pointerup',end);canvas.addEventListener('pointerleave',end);
document.getElementById('clear-signature').onclick=()=>{ctx.clearRect(0,0,canvas.width,canvas.height);drawn=false;data.value=''};
const drawArea=document.getElementById('draw-area'),uploadArea=document.getElementById('upload-area');
document.getElementById('tab-draw').onclick=()=>{drawArea.classList.remove('hidden');uploadArea.classList.add('hidden')};document.getElementById('tab-upload').onclick=()=>{uploadArea.classList.remove('hidden');drawArea.classList.add('hidden')};
document.getElementById('signature-file').onchange=e=>{const file=e.target.files[0];if(!file)return;if(file.size>2*1024*1024){alert('ไฟล์ต้องไม่เกิน 2MB');e.target.value='';return}const reader=new FileReader();reader.onload=ev=>{data.value=ev.target.result;const preview=document.getElementById('upload-preview');preview.src=ev.target.result;preview.classList.remove('hidden')};reader.readAsDataURL(file)};
document.getElementById('signature-form').onsubmit=e=>{if(drawn)data.value=canvas.toDataURL('image/png');if(!data.value&&!@json((bool)$signature)){e.preventDefault();alert('กรุณาวาดหรืออัปโหลดลายเซ็น')}};
</script>
@endpush
