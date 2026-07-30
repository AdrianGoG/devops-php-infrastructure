<x-layout title="Technologies"
          description="Git, Jenkins, Docker, Ansible, Python, Prometheus, Grafana, ELK and Kubernetes - the role each technology plays in the project.">

    <x-page-header
        eyebrow="Technology stack"
        title="Every technology with a clear job"
        lead="Nothing here is decorative: each tool covers a concrete stage of running the infrastructure, from versioning the code to visualising production metrics." />

    <section class="section">
        <div class="container">
            <div class="row g-3 g-lg-4">
                @foreach (config('project.technologies') as $tech)
                    <div class="col-md-6 col-lg-4">
                        <div class="surface surface-pad hover-lift h-100 d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <span class="feature-icon {{ match ($tech['tone']) {
                                    'cyan' => 'is-cyan',
                                    'green' => 'is-green',
                                    'amber' => 'is-amber',
                                    'purple' => 'is-purple',
                                    default => '',
                                } }}">
                                    <x-icon :name="$tech['icon']" :size="22" />
                                </span>
                                <span class="pill">{{ $tech['category'] }}</span>
                            </div>

                            <h2 class="card-title-sm">{{ $tech['name'] }}</h2>
                            <p class="card-text-sm mb-3">{{ $tech['role'] }}</p>

                            <div class="mt-auto d-flex flex-wrap gap-2">
                                @foreach ($tech['details'] as $detail)
                                    <span class="pill">{{ $detail }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- =============================  THIS APPLICATION  ===== --}}
    <section class="section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="eyebrow mb-3 d-inline-flex">Reference application</span>
                    <h2 class="section-title mb-3">How the site you are reading is built</h2>
                    <p class="lead-muted mb-4">
                        <span class="mono">app-company-website</span> is one of the nine applications of the
                        infrastructure and runs on VM2 in its own Docker stack. It is written in Laravel, with the
                        interface built entirely from Blade views and Bootstrap served locally.
                    </p>

                    <ul class="check-list mb-4">
                        <li>Laravel {{ app()->version() }} on PHP {{ PHP_VERSION }}</li>
                        <li>Blade views with reusable components, no front-end build step</li>
                        <li>infrastructure content read from <span class="mono">config/project.php</span></li>
                        <li>PHPUnit tests executed by Jenkins before every deployment</li>
                        <li>a <span class="mono">/health</span> endpoint polled by the Python utility</li>
                    </ul>

                    <a href="{{ url('/health') }}" target="_blank" rel="noopener"
                       class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-2">
                        <x-icon name="external" :size="16" />
                        Open /health
                    </a>
                </div>

                <div class="col-lg-6">
                    <div class="terminal">
                        <div class="terminal-bar">
                            <span class="tdot"></span><span class="tdot"></span><span class="tdot"></span>
                            <span class="tname">app-company-website/src</span>
                        </div>
<pre class="terminal-body">src/
├── <span class="c-key">app/Http/Controllers/</span>
│   └── PageController.php      <span class="c-dim">pages + /health</span>
├── <span class="c-key">config/project.php</span>          <span class="c-dim">infrastructure data</span>
├── <span class="c-key">resources/views/</span>
│   ├── components/             <span class="c-dim">layout, icon, card, pill</span>
│   ├── partials/               <span class="c-dim">navbar, footer</span>
│   ├── pages/                  <span class="c-dim">the five pages</span>
│   └── errors/                 <span class="c-dim">404 and 503</span>
├── <span class="c-key">public/</span>
│   ├── css/site.css            <span class="c-dim">custom theme</span>
│   └── vendor/bootstrap/       <span class="c-dim">bootstrap 5.3, local</span>
├── <span class="c-key">routes/web.php</span>
└── <span class="c-key">tests/Feature/</span>              <span class="c-dim">executed by Jenkins</span>
</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>
