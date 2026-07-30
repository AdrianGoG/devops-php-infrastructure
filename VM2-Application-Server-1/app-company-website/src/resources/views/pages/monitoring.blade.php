<x-layout title="Monitoring"
          description="Prometheus, Grafana, the ELK stack and the Python utility that checks the availability of all nine applications.">

    <x-page-header
        eyebrow="Monitoring & logging"
        title="What is measured, where the logs go"
        lead="Monitoring is not an afterthought here, it is part of the pipeline: the Python utility runs before and after every update, Prometheus scrapes metrics continuously, and ELK keeps the application and web server logs in one place.">

        <div class="d-flex flex-wrap gap-2 mt-4">
            <span class="pill pill-ok"><span class="dot dot-pulse"></span> 9 applications monitored</span>
            <span class="pill pill-accent"><x-icon name="chart" :size="14" /> Prometheus + Grafana</span>
            <span class="pill pill-accent"><x-icon name="logs" :size="14" /> ELK Stack</span>
            <span class="pill pill-accent"><x-icon name="python" :size="14" /> Python checks</span>
        </div>
    </x-page-header>

    {{-- =====================================  THE TOOLING  ===== --}}
    <section class="section">
        <div class="container">
            <div class="row g-3 g-lg-4">
                <div class="col-md-6 col-lg-3">
                    <x-feature-card icon="chart" tone="amber" title="Prometheus">
                        Scrapes metrics from the node_exporter installed on every server and from the
                        applications, keeping history for before/after upgrade comparisons.
                    </x-feature-card>
                </div>
                <div class="col-md-6 col-lg-3">
                    <x-feature-card icon="cpu" tone="cyan" title="Grafana">
                        Dashboards per server and per application: availability, response time, CPU and
                        memory usage.
                    </x-feature-card>
                </div>
                <div class="col-md-6 col-lg-3">
                    <x-feature-card icon="logs" tone="purple" title="ELK Stack">
                        Logstash collects the NGINX and PHP application logs, Elasticsearch indexes them,
                        Kibana makes them searchable and filterable.
                    </x-feature-card>
                </div>
                <div class="col-md-6 col-lg-3">
                    <x-feature-card icon="python" tone="green" title="Python utility">
                        Checks connectivity and HTTP status codes, writes logs and prints a centralised
                        report on the state of the infrastructure.
                    </x-feature-card>
                </div>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- =========================================  METRICS  ===== --}}
    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Metrics"
                title="What is tracked continuously" />

            <div class="row g-3 g-lg-4">
                @foreach (config('project.monitoring.metrics') as $metric)
                    <div class="col-md-6 col-lg-4">
                        <div class="stat-tile hover-lift">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span style="color: var(--accent);"><x-icon name="dot" :size="14" /></span>
                                <h3 class="card-title-sm mb-0">{{ $metric['title'] }}</h3>
                            </div>
                            <p class="card-text-sm">{{ $metric['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ====================================  HTTP STATUS  ===== --}}
    <section class="section">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <span class="eyebrow mb-3 d-inline-flex">Reading the responses</span>
                    <h2 class="section-title mb-3">What each HTTP status means in this project</h2>
                    <p class="lead-muted mb-4">
                        The Python script does not just report the status code, it reports what that code means
                        in the context of the infrastructure. The difference between the report taken before an
                        upgrade and the one taken after shows exactly which applications need their source code
                        adapted.
                    </p>
                    <a href="{{ route('pipeline') }}#migration" class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-2">
                        The remediation process
                        <x-icon name="arrow-right" :size="16" />
                    </a>
                </div>

                <div class="col-lg-7">
                    <div class="d-flex flex-column gap-3">
                        @foreach (config('project.monitoring.http_codes') as $code)
                            <div class="surface surface-pad d-flex gap-3 align-items-start">
                                <span class="pill pill-{{ $code['tone'] }} mono" style="font-size: .95rem; padding: .5rem .85rem;">
                                    {{ $code['code'] }}
                                </span>
                                <div>
                                    <h3 class="card-title-sm mb-1">{{ $code['label'] }}</h3>
                                    <p class="card-text-sm">{{ $code['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ==================================  PYTHON REPORT  ===== --}}
    <section class="section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="eyebrow mb-3 d-inline-flex">Before and after</span>
                    <h2 class="section-title mb-3">The same report, run twice</h2>
                    <p class="lead-muted mb-4">
                        Jenkins runs the utility before the Ansible playbook and again immediately after.
                        Comparing the two reports turns a risky upgrade into a measurable operation: you can see
                        exactly what broke and what kept working.
                    </p>

                    <ul class="check-list">
                        <li>connectivity checks against every server</li>
                        <li>HTTP response checks for each application</li>
                        <li>identification of 200, 404, 500 and 503 codes</li>
                        <li>timestamped log generation</li>
                        <li>a centralised report on the state of the infrastructure</li>
                    </ul>
                </div>

                <div class="col-lg-6">
                    <div class="terminal">
                        <div class="terminal-bar">
                            <span class="tdot"></span><span class="tdot"></span><span class="tdot"></span>
                            <span class="tname">post-upgrade · php 7.4 → 8.3</span>
                        </div>
<pre class="terminal-body"><span class="c-prompt">$</span> python3 infra_check.py --compare before.json

<span class="c-dim">application              before       after</span>
app-company-website      <span class="c-ok">200</span>          <span class="c-ok">200</span>
app-user-dashboard       <span class="c-ok">200</span>          <span class="c-ok">200</span>
app-api                  <span class="c-ok">200</span>          <span class="c-err">503</span>  <span class="c-warn">← regression</span>
app-crm                  <span class="c-ok">200</span>          <span class="c-err">500</span>  <span class="c-warn">← regression</span>
app-inventory            <span class="c-ok">200</span>          <span class="c-ok">200</span>
app-ticket-system        <span class="c-ok">200</span>          <span class="c-ok">200</span>
app-blog                 <span class="c-ok">200</span>          <span class="c-err">503</span>  <span class="c-warn">← regression</span>
app-file-manager         <span class="c-ok">200</span>          <span class="c-ok">200</span>
app-monitor              <span class="c-ok">200</span>          <span class="c-ok">200</span>

<span class="c-warn">3 applications need their source code adapted</span>
<span class="c-dim">report saved: logs/upgrade-php83-report.json</span>
</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>
