@props(['product', 'size' => 'md'])
@php
    $sizeClass = match ($size) {
        'sm' => 'size-10',
        'lg' => 'size-16',
        'xl' => 'size-24',
        default => 'size-12',
    };
@endphp

@if($product?->image_path)
    <img
        src="{{ route('products.image', $product) }}"
        alt="รูปสินค้า {{ $product->name }}"
        loading="lazy"
        {{ $attributes->class([$sizeClass, 'shrink-0 rounded-xl border border-slate-200 bg-white object-cover shadow-sm']) }}
    >
@else
    <span {{ $attributes->class([$sizeClass, 'grid shrink-0 place-items-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400']) }} title="ยังไม่มีรูปสินค้า">
        <svg class="h-1/2 w-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m3 16 5-5 4 4 3-3 6 6M5 20h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Zm10-12h.01"/></svg>
    </span>
@endif
