@props([
    'eyebrow' => null,
    'title' => '',
    'lead' => null,
    'align' => 'start',
])

<div class="mb-4 mb-lg-5 {{ $align === 'center' ? 'text-center mx-auto container-narrow' : '' }}">
    @if ($eyebrow)
        <span class="eyebrow mb-3 d-inline-flex">{{ $eyebrow }}</span>
    @endif

    <h2 class="section-title mb-3">{{ $title }}</h2>

    @if ($lead)
        <p class="lead-muted mb-0" @if ($align !== 'center') style="max-width: 66ch;" @endif>{{ $lead }}</p>
    @endif
</div>
