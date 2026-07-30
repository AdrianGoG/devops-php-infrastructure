@props([
    'value' => '',
    'label' => '',
    'hint' => null,
])

<div class="stat-tile hover-lift">
    <div class="stat-value text-gradient">{{ $value }}</div>
    <p class="stat-label">{{ $label }}</p>

    @if ($hint)
        <p class="stat-hint mb-0">{{ $hint }}</p>
    @endif
</div>
