<x-layout title="Infrastructure"
          description="Four Ubuntu Server machines and nine containerised PHP applications, each running its own PHP version.">

    <x-page-header
        eyebrow="Infrastructure"
        title="Four servers, nine applications"
        lead="One control node running Jenkins and Ansible, plus three identically configured web servers. Each server hosts three independent PHP applications, isolated in Docker containers, each with its own PHP version.">

        <div class="d-flex flex-wrap gap-2 mt-4">
            <span class="pill pill-accent"><x-icon name="server" :size="14" /> Ubuntu Server 24.04 LTS</span>
            <span class="pill pill-accent"><x-icon name="globe" :size="14" /> NGINX</span>
            <span class="pill pill-accent"><x-icon name="docker" :size="14" /> Docker + Compose</span>
            <span class="pill pill-accent"><x-icon name="database" :size="14" /> MySQL 8.0</span>
        </div>
    </x-page-header>

    {{-- ====================================  SERVER BY SERVER  ===== --}}
    @foreach (config('project.servers') as $server)
        <section class="section-tight" id="{{ $server['key'] }}">
            <div class="container">
                <div class="surface surface-pad">
                    <div class="row g-4">
                        {{-- Server identity column --}}
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="feature-icon mb-0 {{ $server['is_controller'] ? 'is-purple' : 'is-cyan' }}">
                                    <x-icon :name="$server['is_controller'] ? 'pipeline' : 'server'" :size="22" />
                                </span>
                                <div>
                                    <h2 class="h5 mb-1">{{ $server['name'] }}</h2>
                                    <p class="app-meta mb-0">{{ $server['role'] }}</p>
                                </div>
                            </div>

                            <p class="card-text-sm mb-3">{{ $server['summary'] }}</p>

                            <div class="d-flex flex-wrap gap-2">
                                @if ($server['host'])
                                    <span class="pill"><x-icon name="globe" :size="13" /> {{ $server['host'] }}</span>
                                @else
                                    <span class="pill pill-accent">control node</span>
                                @endif
                                <span class="pill">{{ $server['os'] }}</span>

                                {{-- On the control node the stack is already detailed in the cards next to it. --}}
                                @unless ($server['is_controller'])
                                    @foreach ($server['stack'] as $tool)
                                        <span class="pill">{{ $tool }}</span>
                                    @endforeach
                                @endunless
                            </div>
                        </div>

                        {{-- Hosted applications column --}}
                        <div class="col-lg-8">
                            @if ($server['is_controller'])
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="stat-tile">
                                            <span class="feature-icon is-purple">
                                                <x-icon name="pipeline" :size="20" />
                                            </span>
                                            <h3 class="card-title-sm">Jenkins</h3>
                                            <p class="card-text-sm">Runs the declarative pipeline and receives the GitHub webhooks.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-tile">
                                            <span class="feature-icon is-amber">
                                                <x-icon name="ansible" :size="20" />
                                            </span>
                                            <h3 class="card-title-sm">Ansible</h3>
                                            <p class="card-text-sm">The inventory of the three servers and the configuration and upgrade playbooks.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-tile">
                                            <span class="feature-icon">
                                                <x-icon name="git" :size="20" />
                                            </span>
                                            <h3 class="card-title-sm">Git</h3>
                                            <p class="card-text-sm">The working clone of the repository, the source of every deployment.</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="table-wrap">
                                    <table class="table table-dark-soft align-middle">
                                        <thead>
                                            <tr>
                                                <th>Application</th>
                                                <th>Role</th>
                                                <th>PHP</th>
                                                <th>Port</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($server['apps'] as $app)
                                                <tr>
                                                    <td>
                                                        <span class="mono d-block" style="color: var(--text);">{{ $app['name'] }}</span>
                                                        <span class="app-meta">{{ $app['note'] }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $app['title'] }}
                                                        <span class="app-meta d-block">{{ $app['framework'] }}</span>
                                                    </td>
                                                    <td><span class="pill">{{ $app['php'] }}</span></td>
                                                    <td class="mono">{{ $app['port'] }}</td>
                                                    <td><x-status-pill :status="$app['status']" /></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    <hr class="section-divider">

    {{-- ==============================  PHP VERSION MATRIX  ===== --}}
    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="PHP versions"
                title="Six PHP versions running side by side"
                lead="The applications were deliberately written against older PHP versions, to reproduce the situation of a real legacy estate. Docker containers let them run in parallel on the same server, while Ansible drives the gradual migration to the newer versions." />

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="table-wrap">
                        <table class="table table-dark-soft">
                            <thead>
                                <tr>
                                    <th>Server</th>
                                    <th>Application</th>
                                    <th>Docker image</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (config('project.servers') as $server)
                                    @foreach ($server['apps'] as $app)
                                        <tr>
                                            <td class="mono text-uppercase">{{ $server['key'] }}</td>
                                            <td class="mono">{{ $app['name'] }}</td>
                                            <td class="mono" style="color: #fcd34d;">php:{{ $app['php'] }}-fpm</td>
                                            <td><x-status-pill :status="$app['status']" /></td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="surface surface-pad h-100">
                        <span class="feature-icon is-amber">
                            <x-icon name="zap" :size="22" />
                        </span>
                        <h3 class="card-title-sm">What happens during an upgrade</h3>
                        <p class="card-text-sm mb-3">
                            When the Ansible playbook raises the PHP version of a legacy application, the
                            container starts on a runtime that no longer accepts the old syntax. The application
                            starts answering with <span class="mono" style="color:#fda4af;">HTTP 503</span>
                            or <span class="mono" style="color:#fda4af;">500</span>, and the Python script flags it
                            in the very next report.
                        </p>
                        <p class="card-text-sm mb-3">
                            That is the intended state: the starting point of the modernisation process. The source
                            code is adapted, committed to Git, and Jenkins redeploys it automatically.
                        </p>
                        <a href="{{ route('pipeline') }}#migration" class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-2">
                            The migration process
                            <x-icon name="arrow-right" :size="16" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ==============================  ANATOMY OF AN APP  ===== --}}
    <section class="section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <span class="eyebrow mb-3 d-inline-flex">Anatomy of an application</span>
                    <h2 class="section-title mb-3">The same template, repeated nine times</h2>
                    <p class="lead-muted mb-4">
                        Every application has its own Docker Compose stack: an NGINX container serving requests,
                        a PHP-FPM container built on the declared PHP version and, where needed, a MySQL database
                        with a persistent volume.
                    </p>
                    <ul class="check-list">
                        <li><span class="mono">Dockerfile</span> — the PHP-FPM image of the application</li>
                        <li><span class="mono">docker-compose.yml</span> — the services and the exposed ports</li>
                        <li><span class="mono">docker/nginx/default.conf</span> — the virtual host</li>
                        <li><span class="mono">src/</span> — the source code, mounted as a volume for persistence</li>
                    </ul>
                </div>

                <div class="col-lg-7">
                    <div class="terminal">
                        <div class="terminal-bar">
                            <span class="tdot"></span><span class="tdot"></span><span class="tdot"></span>
                            <span class="tname">app-company-website/docker-compose.yml</span>
                        </div>
<pre class="terminal-body"><span class="c-key">services</span>:
  <span class="c-key">nginx</span>:
    image: <span class="c-str">nginx:latest</span>
    container_name: <span class="c-str">company-site-nginx</span>
    ports:
      - <span class="c-str">"8081:80"</span>
    volumes:
      - ./src:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on: [php]

  <span class="c-key">php</span>:
    build:
      context: .
      dockerfile: docker/php/Dockerfile   <span class="c-dim"># php:8.x-fpm</span>
    container_name: <span class="c-str">company-site-php</span>
    volumes:
      - ./src:/var/www/html

  <span class="c-key">mysql</span>:
    image: <span class="c-str">mysql:8.0</span>
    container_name: <span class="c-str">company-site-mysql</span>
    volumes:
      - mysql_data:/var/lib/mysql
</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>
