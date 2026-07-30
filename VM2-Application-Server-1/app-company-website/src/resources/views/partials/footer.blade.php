<footer class="site-footer">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="brand-mark">
                        <x-icon name="pipeline" :size="20" stroke-width="1.9" />
                    </span>
                    <span class="fw-bold">{{ config('project.meta.name') }}</span>
                </div>
                <p class="card-text-sm mb-3" style="max-width: 34ch;">
                    {{ config('project.meta.tagline') }}
                </p>
                <a class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-2"
                   href="{{ config('project.meta.repository') }}" target="_blank" rel="noopener">
                    <x-icon name="github" :size="16" />
                    <span>View the code on GitHub</span>
                </a>
            </div>

            <div class="col-6 col-lg-2">
                <h5>Navigation</h5>
                @foreach (config('project.navigation') as $item)
                    <a class="footer-link" href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                @endforeach
            </div>

            <div class="col-6 col-lg-3">
                <h5>Infrastructure</h5>
                @foreach (config('project.servers') as $server)
                    <a class="footer-link" href="{{ route('infrastructure') }}#{{ $server['key'] }}">
                        {{ $server['name'] }}
                    </a>
                @endforeach
            </div>

            <div class="col-lg-3">
                <h5>Application status</h5>
                <div class="d-flex flex-column gap-2">
                    <span class="pill pill-ok"><span class="dot dot-pulse"></span> HTTP 200 · online</span>
                    <span class="pill">PHP {{ PHP_VERSION }}</span>
                    <span class="pill">Laravel {{ app()->version() }}</span>
                    <span class="pill">env: {{ app()->environment() }}</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-wrap justify-content-between gap-2">
            <span>© {{ date('Y') }} {{ config('project.meta.author') }} · {{ config('project.meta.course') }}</span>
            <span class="mono">app-company-website · VM2 Application Server 1</span>
        </div>
    </div>
</footer>
