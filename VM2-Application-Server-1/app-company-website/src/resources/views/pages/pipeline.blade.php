<x-layout title="CI/CD Pipeline"
          description="The Jenkins pipeline stages: from the GitHub webhook to containerised deployment and the automated smoke test.">

    <x-page-header
        eyebrow="CI/CD Pipeline"
        title="One commit, eight stages, zero manual steps"
        lead="The Jenkins pipeline is declared in a Jenkinsfile and triggered by GitHub webhooks. Every stage is a gate: if one fails, the pipeline stops and the version currently in production is left untouched.">

        <div class="d-flex flex-wrap gap-2 mt-4">
            <span class="pill pill-accent"><x-icon name="git" :size="14" /> webhook triggered</span>
            <span class="pill pill-accent"><x-icon name="shield" :size="14" /> tests before build</span>
            <span class="pill pill-accent"><x-icon name="rollback" :size="14" /> rollback on failure</span>
            <span class="pill pill-accent"><x-icon name="bell" :size="14" /> notification at the end</span>
        </div>
    </x-page-header>

    {{-- ======================================  PIPELINE STAGES  ===== --}}
    <section class="section">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-7">
                    <x-section-heading
                        eyebrow="Stages"
                        title="What happens on every push" />

                    <ol class="timeline">
                        @foreach (config('project.pipeline') as $stage)
                            <li class="timeline-item">
                                <div class="timeline-title">
                                    <span>{{ $stage['stage'] }}</span>
                                    <span class="pill">{{ $stage['tool'] }}</span>
                                </div>
                                <p class="card-text-sm">{{ $stage['text'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="col-lg-5">
                    <div class="position-sticky" style="top: 100px;">
                        <div class="terminal mb-4">
                            <div class="terminal-bar">
                                <span class="tdot"></span><span class="tdot"></span><span class="tdot"></span>
                                <span class="tname">Jenkinsfile</span>
                            </div>
<pre class="terminal-body"><span class="c-key">pipeline</span> {
  agent any

  <span class="c-key">environment</span> {
    APP  = <span class="c-str">'app-company-website'</span>
    HOST = <span class="c-str">'192.168.0.170'</span>
  }

  <span class="c-key">triggers</span> { githubPush() }

  <span class="c-key">stages</span> {
    stage(<span class="c-str">'Checkout'</span>)   { <span class="c-dim">/* git scm  */</span> }
    stage(<span class="c-str">'Validate'</span>)   { <span class="c-dim">/* structure */</span> }
    stage(<span class="c-str">'Test'</span>)       { <span class="c-dim">/* phpunit   */</span> }
    stage(<span class="c-str">'Build'</span>)      { <span class="c-dim">/* docker    */</span> }
    stage(<span class="c-str">'Deploy'</span>)     { <span class="c-dim">/* ssh + up  */</span> }
    stage(<span class="c-str">'Smoke test'</span>) { <span class="c-dim">/* python    */</span> }
  }

  <span class="c-key">post</span> {
    success { <span class="c-ok">notify('SUCCESS')</span> }
    failure { <span class="c-err">rollback(); notify('FAILED')</span> }
  }
}
</pre>
                        </div>

                        <div class="surface surface-pad">
                            <h3 class="card-title-sm">The gates of the pipeline</h3>
                            <p class="card-text-sm mb-3">
                                Tests run before the build, and the smoke test runs after the deployment.
                                Anything other than <span class="mono">200</span> marks the build as failed
                                and triggers the rollback to the previous version.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="pill pill-ok">200 → SUCCESS</span>
                                <span class="pill pill-danger">503 → ROLLBACK</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ======================================  VERSION CONTROL  ===== --}}
    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Version control"
                title="How the repository is organised"
                lead="The code of the entire infrastructure lives in a single repository, structured per server. Development happens on separate branches, and main always holds the deployable version." />

            <div class="row g-3 g-lg-4">
                <div class="col-md-6 col-lg-3">
                    <x-feature-card icon="git" tone="purple" title="main branch">
                        The stable branch. Any merge into main triggers a full deployment to the target servers.
                    </x-feature-card>
                </div>
                <div class="col-md-6 col-lg-3">
                    <x-feature-card icon="git" tone="cyan" title="development branch">
                        The working branch for changes and for adapting legacy code, integrated through pull requests.
                    </x-feature-card>
                </div>
                <div class="col-md-6 col-lg-3">
                    <x-feature-card icon="box" tone="amber" title="Documented commits">
                        Every commit states what changed and why, so the history of the migration can be reconstructed.
                    </x-feature-card>
                </div>
                <div class="col-md-6 col-lg-3">
                    <x-feature-card icon="rollback" tone="green" title="Roll back anytime">
                        Any earlier version can be redeployed by checking out that commit and re-running the pipeline.
                    </x-feature-card>
                </div>
            </div>

            <div class="terminal mt-4">
                <div class="terminal-bar">
                    <span class="tdot"></span><span class="tdot"></span><span class="tdot"></span>
                    <span class="tname">repository structure</span>
                </div>
<pre class="terminal-body">devops-php-infrastructure/
├── <span class="c-key">VM1-Jenkins-Ansible-Git/</span>   <span class="c-dim">control node: jenkins, ansible, scripts</span>
├── <span class="c-key">VM2-Application-Server-1/</span>  <span class="c-dim">app-company-website, app-user-dashboard, app-api</span>
├── <span class="c-key">VM3-Application-Server-2/</span>  <span class="c-dim">app-crm, app-inventory, app-ticket-system</span>
├── <span class="c-key">VM4-Application-Server-3/</span>  <span class="c-dim">app-blog, app-file-manager, app-monitor</span>
├── <span class="c-key">python-monitor/</span>            <span class="c-dim">infrastructure check utility</span>
├── <span class="c-key">monitoring/</span>                <span class="c-dim">prometheus + grafana</span>
└── <span class="c-key">docs/</span>                      <span class="c-dim">installation documentation</span>
</pre>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ==========================================  MIGRATION  ===== --}}
    <section class="section" id="migration">
        <div class="container">
            <x-section-heading
                eyebrow="Modernisation"
                title="How a legacy application moves to a new PHP version"
                lead="This is the central scenario of the project: the automated PHP upgrade exposes the incompatibilities, and the pipeline closes the loop once the source code is fixed." />

            <div class="row g-3 g-lg-4">
                @foreach (config('project.migration') as $index => $step)
                    <div class="col-md-6 col-lg-4">
                        <div class="surface surface-pad hover-lift h-100">
                            <span class="pill pill-accent mb-3">step {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="card-title-sm">{{ $step['title'] }}</h3>
                            <p class="card-text-sm">{{ $step['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ==============================  ADDITIONAL OBJECTIVES  ===== --}}
    <section class="section">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-6">
                    <x-section-heading
                        eyebrow="Advanced level"
                        title="Pipeline extensions"
                        lead="Capabilities that take the automation beyond the basic build → test → deploy flow." />

                    <ul class="check-list">
                        @foreach (config('project.extras') as $extra)
                            <li>{{ $extra }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="col-lg-6">
                    <div class="surface surface-pad">
                        <span class="feature-icon is-green">
                            <x-icon name="box" :size="22" />
                        </span>
                        <h3 class="card-title-sm">Portfolio deliverables</h3>
                        <p class="card-text-sm mb-4">
                            The project is delivered as a complete portfolio, with everything needed to rebuild
                            the infrastructure from scratch.
                        </p>

                        <div class="row g-3">
                            @foreach (config('project.deliverables') as $item)
                                <div class="col-sm-6">
                                    <div class="d-flex gap-2">
                                        <span style="color: var(--ok);">
                                            <x-icon name="check" :size="17" />
                                        </span>
                                        <div>
                                            <p class="mb-1" style="font-size: .9rem; font-weight: 600; color: var(--text);">{{ $item['title'] }}</p>
                                            <p class="app-meta mb-0">{{ $item['text'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>
