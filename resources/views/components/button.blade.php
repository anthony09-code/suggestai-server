@php
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $variants = [
        'primary' => 'text-white',
        'outline' => 'bg-transparent border border-border text-text hover:bg-background-neutral',
        'ghost'   => 'bg-transparent text-text hover:bg-background-neutral',
        'danger'  => 'bg-red-500 text-white hover:bg-red-600',
    ];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center font-medium rounded transition cursor-pointer ' . $sizes[$size] . ' ' . $variants[$variant]]) }}
    @if($variant === 'primary')
        style="background-color: var(--office-color);"
        onmouseover="this.style.opacity='0.85'"
        onmouseout="this.style.opacity='1'"
    @endif
>
    {{ $slot }}
</button>
