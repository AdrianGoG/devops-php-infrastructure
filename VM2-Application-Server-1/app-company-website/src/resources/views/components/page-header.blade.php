@props([
    'eyebrow' => null,
    'title' => '',
    'lead' => null,
])

<header class="page-head">
    <div class="container">
        @if ($eyebrow)
            <span class="eyebrow mb-3 d-inline-flex">{{ $eyebrow }}</span>
        @endif

        <h1 class="display-hero mb-3" style="max-width: 22ch;">{{ $title }}</h1>

        @if ($lead)
            <p class="lead-muted mb-0" style="max-width: 68ch;">{{ $lead }}</p>
        @endif

        {{ $slot }}
    </div>
</header>

<hr class="section-divider">
