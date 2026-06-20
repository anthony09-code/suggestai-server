@props([
    'title' => null,
    'description' => null,
    'accent' => true,
])

@php
    /** @var \App\Models\Office $office */
@endphp


<div class="bg-white rounded-lg border border-border overflow-hidden">
    @if($accent)
        <div class="h-1.5 w-full" style="background-color: var(--office-color)"></div>
    @endif
    <div class="px-6 py-4 text-text">
        @if($title)
            <h2 class="text-3xl font-medium">{{ $title }}</h2>
        @endif
        @if($description)
            <p class="text-base text-text-muted mt-2">{{ $description }}</p>
        @endif
        @if($slot->isNotEmpty())
            {{ $slot }}
        @endif
    </div>
    @isset($footer)
        <div class="px-6 py-4 border-t border-border">
            {{ $footer }}
        </div>
    @endisset
</div>
