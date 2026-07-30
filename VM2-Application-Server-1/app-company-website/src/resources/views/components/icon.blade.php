@props([
    'name' => 'dot',
    'size' => 20,
])

{{--
    Minimal inline SVG icon set.
    The paths are inlined in the markup to avoid an icon font or an extra CDN
    request - the application stays completely self-contained.
--}}

@php
    $paths = [
        'git' => '<line x1="6" y1="3" x2="6" y2="15"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 0 1-9 9"/>',
        'pipeline' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><path d="M6.5 10v3.5a3 3 0 0 0 3 3H14"/>',
        'docker' => '<path d="M21 8.5 12 3.5 3 8.5v7L12 20.5l9-5Z"/><path d="M3 8.5l9 5 9-5"/><path d="M12 13.5V20.5"/>',
        'layers' => '<path d="m12 2.5 9 4.75-9 4.75-9-4.75 9-4.75Z"/><path d="m3 12 9 4.75L21 12"/><path d="m3 16.75 9 4.75 9-4.75"/>',
        'ansible' => '<circle cx="12" cy="12" r="3"/><path d="M12 2.5v3M12 18.5v3M4.6 4.6l2.1 2.1M17.3 17.3l2.1 2.1M2.5 12h3M18.5 12h3M4.6 19.4l2.1-2.1M17.3 6.7l2.1-2.1"/>',
        'python' => '<path d="m9 18-6-6 6-6"/><path d="m15 6 6 6-6 6"/>',
        'chart' => '<path d="M3 3v18h18"/><path d="M7.5 18v-5M12 18V9M16.5 18v-8"/>',
        'logs' => '<path d="M14 2.5H6.5a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V8Z"/><path d="M14 2.5V8h5.5"/><path d="M8.5 13h7M8.5 17h4.5"/>',
        'kubernetes' => '<path d="M12 2.5 20.5 7v10L12 21.5 3.5 17V7Z"/><circle cx="12" cy="12" r="2.75"/>',
        'server' => '<rect x="2.5" y="3" width="19" height="7" rx="2"/><rect x="2.5" y="14" width="19" height="7" rx="2"/><path d="M6.5 6.5h.01M6.5 17.5h.01"/>',
        'terminal' => '<rect x="2.5" y="3.5" width="19" height="17" rx="2"/><path d="m7 10 2.5 2.5L7 15"/><path d="M12.5 15h4.5"/>',
        'cpu' => '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9.5 2.5v3M14.5 2.5v3M9.5 18.5v3M14.5 18.5v3M2.5 9.5h3M2.5 14.5h3M18.5 9.5h3M18.5 14.5h3"/>',
        'database' => '<ellipse cx="12" cy="5.5" rx="8" ry="3"/><path d="M4 5.5v13c0 1.66 3.58 3 8 3s8-1.34 8-3v-13"/><path d="M4 12c0 1.66 3.58 3 8 3s8-1.34 8-3"/>',
        'shield' => '<path d="M12 21.5s8-3.86 8-9.5V5.2L12 2.5 4 5.2v6.8c0 5.64 8 9.5 8 9.5Z"/><path d="m9 12 2.25 2.25L15.5 10"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.25L15.5 14"/>',
        'bell' => '<path d="M18 8.5a6 6 0 1 0-12 0c0 6-2.5 8-2.5 8h17s-2.5-2-2.5-8"/><path d="M10.25 20.5a2 2 0 0 0 3.5 0"/>',
        'rollback' => '<path d="M3.5 12a8.5 8.5 0 1 0 2.6-6.1"/><path d="M3.5 3.5v5h5"/><path d="M12 8v4.5l3 1.8"/>',
        'box' => '<path d="M20.5 7.5 12 3 3.5 7.5v9L12 21l8.5-4.5Z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/>',
        'mail' => '<rect x="2.5" y="4.5" width="19" height="15" rx="2.5"/><path d="m3.5 7 8.5 5.5L20.5 7"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3.5 12h17"/><path d="M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18Z"/>',
        'zap' => '<path d="M13.5 2.5 4 14h6l-1.5 7.5L18 10h-6l1.5-7.5Z"/>',
        'check' => '<path d="M20 6.5 9.5 17 4.5 12"/>',
        'arrow-right' => '<path d="M4.5 12h15"/><path d="m13.5 6 6 6-6 6"/>',
        'external' => '<path d="M14 4.5h5.5V10"/><path d="M19.5 4.5 11 13"/><path d="M18 14.5v3.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h3.5"/>',
        'github' => '<path fill="currentColor" stroke="none" d="M12 1.8a10.2 10.2 0 0 0-3.23 19.89c.51.09.7-.22.7-.49v-1.9c-2.84.62-3.44-1.2-3.44-1.2-.46-1.18-1.14-1.5-1.14-1.5-.93-.63.07-.62.07-.62 1.03.07 1.57 1.06 1.57 1.06.91 1.56 2.39 1.11 2.97.85.09-.66.36-1.11.65-1.37-2.27-.26-4.65-1.13-4.65-5.05 0-1.11.4-2.03 1.05-2.74-.11-.26-.46-1.3.1-2.71 0 0 .86-.27 2.81 1.05a9.8 9.8 0 0 1 5.12 0c1.95-1.32 2.81-1.05 2.81-1.05.56 1.41.21 2.45.1 2.71.65.71 1.05 1.63 1.05 2.74 0 3.93-2.39 4.79-4.67 5.04.37.32.7.94.7 1.9v2.82c0 .27.18.59.71.49A10.2 10.2 0 0 0 12 1.8Z"/>',
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
