<x-layout>
    {{-- =====================================================  HERO  ===== --}}
    <section class="hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="hero-badge">
                        <span class="pill">{{ config('project.meta.theme') }}</span>
                        ITSchool final project · DevOps
                        <span class="text-dim">{{ str_repeat('★', config('project.meta.difficulty')) }}</span>
                    </span>

                    <h1 class="display-hero mb-4">
                        A complete <span class="text-gradient">CI/CD&nbsp;pipeline</span>
                        for a PHP web infrastructure
                    </h1>

                    <p class="lead-muted mb-4" style="max-width: 56ch;">
                        {{ config('project.meta.tagline') }}
                    </p>

                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <a href="{{ route('pipeline') }}" class="btn btn-accent d-inline-flex align-items-center gap-2">
                            Explore the pipeline
                            <x-icon name="arrow-right" :size="18" />
                        </a>
                        <a href="{{ route('infrastructure') }}" class="btn btn-ghost d-inline-flex align-items-center gap-2">
                            <x-icon name="server" :size="18" />
                            The infrastructure
                        </a>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="pill pill-accent"><x-icon name="docker" :size="14" /> Docker</span>
                        <span class="pill pill-accent"><x-icon name="pipeline" :size="14" /> Jenkins</span>
                        <span class="pill pill-accent"><x-icon name="ansible" :size="14" /> Ansible</span>
                        <span class="pill pill-accent"><x-icon name="python" :size="14" /> Python</span>
                        <span class="pill pill-accent"><x-icon name="chart" :size="14" /> Prometheus</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="terminal">
                        <div class="terminal-bar">
                            <span class="tdot"></span><span class="tdot"></span><span class="tdot"></span>
                            <span class="tname">jenkins · pipeline #128</span>
                        </div>
<pre class="terminal-body"><span class="c-prompt">$</span> git push origin development
<span class="c-dim">Enumerating objects: 14, done.</span>
<span class="c-dim">To github.com:AdrianGoG/devops-php-infrastructure.git</span>

<span class="c-key">[webhook]</span>  push detected → triggering Jenkins
<span class="c-key">[stage 1]</span>  Checkout ............... <span class="c-ok">OK</span>
<span class="c-key">[stage 2]</span>  Validate structure ..... <span class="c-ok">OK</span>
<span class="c-key">[stage 3]</span>  PHPUnit ................ <span class="c-ok">17 passed</span>
<span class="c-key">[stage 4]</span>  Docker build ........... <span class="c-str">php:8.3-fpm</span>
<span class="c-key">[stage 5]</span>  Deploy → <span class="c-str">vm2</span> ......... <span class="c-ok">OK</span>
<span class="c-key">[stage 6]</span>  Smoke test ............. <span class="c-ok">HTTP 200</span>
<span class="c-key">[notify ]</span>  Discord webhook sent

<span class="c-ok">SUCCESS</span> <span class="c-dim">— app-company-website deployed in 1m 42s</span>
</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ====================================================  STATS  ===== --}}
    <section class="section-tight">
        <div class="container">
            <div class="row g-3 g-lg-4">
                @foreach (config('project.stats') as $stat)
                    <div class="col-6 col-lg-3">
                        <x-stat-tile :value="$stat['value']" :label="$stat['label']" :hint="$stat['hint']" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================  OBJECTIVES  ===== --}}
    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Project scope"
                title="A legacy estate, managed like a modern one"
                lead="Three Ubuntu servers host nine PHP applications on different PHP versions. The goal of the project is to show how such an estate can be versioned, tested, upgraded, delivered and monitored completely automatically, with no manual intervention." />

            <div class="row g-3 g-lg-4">
                @foreach (config('project.objectives') as $objective)
                    <div class="col-md-6 col-lg-4">
                        <x-feature-card
                            :icon="$objective['icon']"
                            :tone="$objective['tone']"
                            :title="$objective['title']">
                            {{ $objective['text'] }}
                        </x-feature-card>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ==================================================  THE FLOW  ===== --}}
    <section class="section">
        <div class="container">
            <div class="row align-items-end g-4 mb-4 mb-lg-5">
                <div class="col-lg-8">
                    <span class="eyebrow mb-3 d-inline-flex">Delivery flow</span>
                    <h2 class="section-title mb-3">From commit to production in six steps</h2>
                    <p class="lead-muted mb-0" style="max-width: 62ch;">
                        The pipeline is triggered by a GitHub webhook on every change. If any stage fails the
                        deployment stops and the previous version stays live.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('pipeline') }}" class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-2">
                        All stages
                        <x-icon name="arrow-right" :size="16" />
                    </a>
                </div>
            </div>

            <div class="flow">
                @foreach (config('project.flow') as $step)
                    <div class="flow-step">
                        <h4>{{ $step['title'] }}</h4>
                        <p>{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ============================================  INFRASTRUCTURE  ===== --}}
    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Infrastructure"
                title="Four servers, nine applications, six PHP versions"
                lead="One control node running Jenkins and Ansible, plus three application servers. Every application runs isolated in its own container, with exactly the PHP version it needs." />

            <div class="row g-3 g-lg-4">
                @foreach (config('project.servers') as $server)
                    <div class="col-md-6 col-xl-3">
                        <div class="server-card hover-lift">
                            <div class="server-head">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <span class="feature-icon mb-0 {{ $server['is_controller'] ? 'is-purple' : 'is-cyan' }}"
                                          style="width: 38px; height: 38px; border-radius: 11px;">
                                        <x-icon :name="$server['is_controller'] ? 'pipeline' : 'server'" :size="18" />
                                    </span>
                                    @if ($server['host'])
                                        <span class="pill">{{ $server['host'] }}</span>
                                    @else
                                        <span class="pill pill-accent">control node</span>
                                    @endif
                                </div>
                                <h3 class="card-title-sm mb-1">{{ $server['name'] }}</h3>
                                <p class="app-meta mb-0">{{ $server['role'] }} · {{ $server['os'] }}</p>
                            </div>

                            <div class="server-body">
                                @if ($server['is_controller'])
                                    <p class="card-text-sm mb-3">{{ $server['summary'] }}</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($server['stack'] as $tool)
                                            <span class="pill pill-accent">{{ $tool }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    @foreach ($server['apps'] as $app)
                                        <div class="app-row">
                                            <div class="flex-grow-1 min-w-0">
                                                <p class="app-name text-truncate">{{ $app['name'] }}</p>
                                                <p class="app-meta">PHP {{ $app['php'] }} · port {{ $app['port'] }}</p>
                                            </div>
                                            <x-status-pill :status="$app['status']" />
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-4 mt-lg-5">
                <a href="{{ route('infrastructure') }}" class="btn btn-ghost d-inline-flex align-items-center gap-2">
                    <x-icon name="server" :size="18" />
                    Server by server
                </a>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ==============================================  MONITORING  ===== --}}
    <section class="section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="eyebrow mb-3 d-inline-flex">Validation & monitoring</span>
                    <h2 class="section-title mb-3">The infrastructure checks itself</h2>
                    <p class="lead-muted mb-4">
                        A Python utility runs before and after every automated update: it tests connectivity to
                        all servers, queries each application, interprets the HTTP status it receives and writes
                        a centralised report on the state of the infrastructure.
                    </p>

                    <ul class="check-list mb-4">
                        <li>connectivity checks against the three application servers</li>
                        <li>HTTP probes for all nine applications, with response times</li>
                        <li>identification of 200, 404, 500 and 503 responses</li>
                        <li>timestamped logs, kept to compare before and after an upgrade</li>
                    </ul>

                    <a href="{{ route('monitoring') }}" class="btn btn-ghost d-inline-flex align-items-center gap-2">
                        <x-icon name="chart" :size="18" />
                        Prometheus, Grafana and ELK
                    </a>
                </div>

                <div class="col-lg-6">
                    <div class="terminal">
                        <div class="terminal-bar">
                            <span class="tdot"></span><span class="tdot"></span><span class="tdot"></span>
                            <span class="tname">python-monitor · infra_check.py</span>
                        </div>
<pre class="terminal-body"><span class="c-prompt">$</span> python3 infra_check.py

<span class="c-dim">── connectivity ──────────────────────</span>
vm2  192.168.0.169   <span class="c-ok">reachable</span>   1.8 ms
vm3  192.168.0.105   <span class="c-ok">reachable</span>   2.1 ms
vm4  192.168.0.125   <span class="c-ok">reachable</span>   2.4 ms

<span class="c-dim">── applications ──────────────────────</span>
app-company-website   <span class="c-ok">200</span>   <span class="c-dim">84 ms</span>
app-user-dashboard    <span class="c-ok">200</span>   <span class="c-dim">91 ms</span>
app-api               <span class="c-err">503</span>   <span class="c-dim">php-fpm down</span>
app-crm               <span class="c-err">500</span>   <span class="c-dim">syntax error</span>
app-inventory         <span class="c-ok">200</span>   <span class="c-dim">others: 4/4 OK</span>

<span class="c-warn">REPORT</span> 7/9 healthy · <span class="c-dim">log: logs/2026-07-30.log</span>
</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ====================================================  CTA  ===== --}}
    <section class="section-tight pb-5">
        <div class="container">
            <div class="cta-panel">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="eyebrow mb-3 d-inline-flex">Portfolio</span>
                        <h2 class="section-title mb-3">The whole project in a single repository</h2>
                        <p class="lead-muted mb-0" style="max-width: 60ch;">
                            Source code, Jenkinsfile, Dockerfiles, Ansible playbooks, Python scripts and the
                            installation documentation — organised per server, mirroring the real infrastructure.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="d-flex flex-column flex-sm-row flex-lg-column gap-2 justify-content-lg-end">
                            <a href="{{ config('project.meta.repository') }}" target="_blank" rel="noopener"
                               class="btn btn-accent d-inline-flex align-items-center justify-content-center gap-2">
                                <x-icon name="github" :size="18" />
                                Open on GitHub
                            </a>
                            <a href="{{ route('technologies') }}"
                               class="btn btn-ghost d-inline-flex align-items-center justify-content-center gap-2">
                                <x-icon name="layers" :size="18" />
                                The technology stack
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>
