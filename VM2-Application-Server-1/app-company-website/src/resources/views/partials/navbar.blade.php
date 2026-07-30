<nav class="navbar navbar-expand-lg sticky-top site-nav py-2">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <span class="brand-mark">
                <x-icon name="pipeline" :size="20" stroke-width="1.9" />
            </span>
            <span class="brand-text">
                <span>{{ config('project.meta.name') }}</span>
                <small>{{ config('project.meta.theme') }} · ITSchool</small>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                @foreach (config('project.navigation') as $item)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                           href="{{ route($item['route']) }}"
                           @if (request()->routeIs($item['route'])) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach

                <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                    <a class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-2"
                       href="{{ config('project.meta.repository') }}" target="_blank" rel="noopener">
                        <x-icon name="github" :size="16" />
                        <span>Repository</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
