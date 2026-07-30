@props([
    'icon' => 'dot',
    'tone' => 'accent',
    'title' => '',
])

@php
    // Maps the logical tone onto the colour class of the icon tile.
    $toneClass = match ($tone) {
        'cyan' => 'is-cyan',
        'green' => 'is-green',
        'amber' => 'is-amber',
        'purple' => 'is-purple',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => 'surface surface-pad hover-lift h-100']) }}>
    <span class="feature-icon {{ $toneClass }}">
        <x-icon :name="$icon" :size="22" />
    </span>

    <h3 class="card-title-sm">{{ $title }}</h3>

    <div class="card-text-sm">{{ $slot }}</div>
</div>
