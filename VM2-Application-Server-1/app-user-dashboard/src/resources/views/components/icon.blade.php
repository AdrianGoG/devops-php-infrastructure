@props([
    'name' => 'dot',
    'size' => 20,
])

{{-- Inline SVG icons: no icon font, no extra request, no CDN. --}}

@php
    $paths = [
        'pipeline' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><path d="M6.5 10v3.5a3 3 0 0 0 3 3H14"/>',
        'server' => '<rect x="2.5" y="3" width="19" height="7" rx="2"/><rect x="2.5" y="14" width="19" height="7" rx="2"/><path d="M6.5 6.5h.01M6.5 17.5h.01"/>',
        'box' => '<path d="M20.5 7.5 12 3 3.5 7.5v9L12 21l8.5-4.5Z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/>',
        'rocket' => '<path d="M13.5 2.5 4 14h6l-1.5 7.5L18 10h-6l1.5-7.5Z"/>',
        'alert' => '<path d="M12 3.5 2.5 20h19L12 3.5Z"/><path d="M12 10v4.5M12 17.5h.01"/>',
        'check' => '<path d="M20 6.5 9.5 17 4.5 12"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.25L15.5 14"/>',
        'chevron-down' => '<path d="m6 9.5 6 6 6-6"/>',
        'external' => '<path d="M14 4.5h5.5V10"/><path d="M19.5 4.5 11 13"/><path d="M18 14.5v3.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h3.5"/>',
        'refresh' => '<path d="M3.5 12a8.5 8.5 0 0 1 14.6-5.9L21 9"/><path d="M21 3.5v5.5h-5.5"/>',
        'dot' => '<circle cx="12" cy="12" r="4"/>',
    ];

    $path = $paths[$name] ?? $paths['dot'];
@endphp

<svg {{ $attributes->merge(['class' => 'flex-shrink-0', 'aria-hidden' => 'true']) }}
     width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
     fill="none" stroke="currentColor" stroke-width="1.6"
     stroke-linecap="round" stroke-linejoin="round">
    {!! $path !!}
</svg>
