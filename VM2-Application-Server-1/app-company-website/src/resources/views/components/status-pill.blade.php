@props([
    'status' => 'ok',
])

@php
    // Application state as reported by the Python monitoring utility.
    $map = [
        'ok' => ['class' => 'pill-ok', 'label' => 'HTTP 200'],
        'legacy' => ['class' => 'pill-danger', 'label' => 'legacy'],
        // The runtime is below what the application requires: it cannot boot
        // until PHP is upgraded. The opposite problem to "legacy".
        'blocked' => ['class' => 'pill-danger', 'label' => 'blocked'],
        'pending' => ['class' => 'pill-warn', 'label' => 'in progress'],
    ];

    $state = $map[$status] ?? $map['pending'];
@endphp

<span class="pill {{ $state['class'] }}">
    <span class="dot {{ $status === 'ok' ? 'dot-pulse' : '' }}"></span>
    {{ $state['label'] }}
</span>
