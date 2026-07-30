<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <span class="eyebrow mb-2 d-inline-flex">Infrastructure</span>
                <h1 class="page-title">Welcome back, {{ Str::before(Auth::user()->name, ' ') }}</h1>
                <p class="page-subtitle">
                    Live state of the estate, read from
                    <span class="mono">app-api</span> on VM2.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                @if ($registryReachable)
                    <span class="pill pill-ok"><span class="dot"></span> registry online</span>
                @else
                    <span class="pill pill-danger"><span class="dot"></span> registry offline</span>
                @endif

                <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-2">
                    <x-icon name="refresh" :size="15" />
                    Refresh
                </a>
            </div>
        </div>
    </x-slot>

    @unless ($registryReachable)
        <div class="alert-soft alert-soft-warn section-gap d-flex gap-2 align-items-start" role="alert">
            <x-icon name="alert" :size="18" />
            <div>
                <strong>The registry API is not answering.</strong>
                The dashboard cannot reach <span class="mono">{{ $registryUrl }}</span>, so the panels below are
                empty. This is the expected state while <span class="mono">app-api</span> is being upgraded to
                PHP 8.3 - start it with <span class="mono">docker compose up -d</span> in the app-api directory.
            </div>
        </div>
    @endunless

    {{-- ============================================  STATS  ===== --}}
    <div class="row g-3 g-lg-4 section-gap">
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Servers</p>
                <p class="stat-value text-gradient">{{ $stats['servers'] }}</p>
                <p class="stat-hint">1 control node + application servers</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Applications</p>
                <p class="stat-value text-gradient">{{ $stats['applications'] }}</p>
                <p class="stat-hint">PHP apps under the pipeline</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Legacy</p>
                <p class="stat-value" style="color: var(--danger);">{{ $stats['legacy'] }}</p>
                <p class="stat-hint">still to migrate to PHP 8.3</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Failed deploys</p>
                <p class="stat-value" style="color: var(--warn);">{{ $stats['failed_deployments'] }}</p>
                <p class="stat-hint">in the recent history</p>
            </div>
        </div>
    </div>

    {{-- =====================================  APPLICATIONS  ===== --}}
    <div class="card-surface section-gap">
        <div class="card-head">
            <div>
                <h2>Applications</h2>
                <p class="card-text-sm">Every PHP application of the infrastructure, with its runtime version.</p>
            </div>
            <span class="pill">GET /api/applications</span>
        </div>

        <div class="table-wrap">
            <table class="table table-dash align-middle">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Server</th>
                        <th>PHP</th>
                        <th>Port</th>
                        <th>Status</th>
                        <th class="text-end">Open</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td>
                                <span class="cell-strong d-block">{{ $application['name'] }}</span>
                                <span class="text-dim" style="font-size: .78rem;">{{ $application['title'] }}</span>
                            </td>
                            <td class="text-uppercase mono">{{ $application['server'] }}</td>
                            <td><span class="pill">{{ $application['php_version'] }}</span></td>
                            <td class="mono">{{ $application['port'] }}</td>
                            <td>
                                @php
                                    $tone = match ($application['status']) {
                                        'ok' => 'pill-ok',
                                        'legacy', 'blocked' => 'pill-danger',
                                        default => 'pill-warn',
                                    };
                                @endphp
                                <span class="pill {{ $tone }}"><span class="dot"></span> {{ $application['status'] }}</span>
                            </td>
                            <td class="text-end">
                                @if (! empty($application['url']))
                                    <a href="{{ $application['url'] }}" target="_blank" rel="noopener"
                                       class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-1">
                                        <x-icon name="external" :size="14" />
                                    </a>
                                @else
                                    <span class="text-dim">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-dim py-4">
                                No data. The registry API did not return any application.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        {{-- ==================================  DEPLOYMENTS  ===== --}}
        <div class="col-lg-7">
            <div class="card-surface h-100">
                <div class="card-head">
                    <div>
                        <h2>Recent deployments</h2>
                        <p class="card-text-sm">Written by the Jenkins pipeline at the end of every run.</p>
                    </div>
                    <span class="pill">GET /api/deployments</span>
                </div>

                <div class="table-wrap">
                    <table class="table table-dash align-middle">
                        <thead>
                            <tr>
                                <th>Application</th>
                                <th>Build</th>
                                <th>Branch</th>
                                <th>Result</th>
                                <th>When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deployments as $deployment)
                                <tr>
                                    <td class="cell-strong">{{ $deployment['application'] }}</td>
                                    <td class="mono">#{{ $deployment['build_number'] ?? '—' }}</td>
                                    <td class="mono">{{ $deployment['branch'] ?? '—' }}</td>
                                    <td>
                                        @php
                                            $tone = match ($deployment['result']) {
                                                'success' => 'pill-ok',
                                                'failed' => 'pill-danger',
                                                default => 'pill-warn',
                                            };
                                        @endphp
                                        <span class="pill {{ $tone }}">{{ $deployment['result'] }}</span>
                                    </td>
                                    <td class="text-dim" style="font-size: .8rem;">{{ $deployment['deployed_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-dim py-4">
                                        No deployment recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ======================================  SERVERS  ===== --}}
        <div class="col-lg-5">
            <div class="card-surface h-100">
                <div class="card-head">
                    <div>
                        <h2>Servers</h2>
                        <p class="card-text-sm">The machines of the infrastructure.</p>
                    </div>
                    <span class="pill">GET /api/servers</span>
                </div>

                <div class="card-surface-pad d-flex flex-column gap-3">
                    @forelse ($servers as $server)
                        <div class="d-flex align-items-start gap-3">
                            <span class="brand-mark" style="background: rgba(99,102,241,.16); color: #a5b4fc;">
                                <x-icon name="server" :size="17" />
                            </span>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="cell-strong">{{ $server['key'] }}</span>
                                    @if (! empty($server['host']))
                                        <span class="pill">{{ $server['host'] }}</span>
                                    @else
                                        <span class="pill pill-accent">control node</span>
                                    @endif
                                </div>
                                <p class="card-text-sm mt-1 mb-0">
                                    {{ $server['name'] }} ·
                                    {{ $server['applications_count'] }}
                                    {{ Str::plural('application', $server['applications_count']) }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="card-text-sm text-center text-dim mb-0 py-3">No server reported.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
