@extends('layouts.app')

@section('title', 'Estate')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Estate health</h1>
            <p class="page-subtitle mb-0">
                Every application probed through its own health endpoint, live.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="pill pill-ok">Healthy: {{ $summary['healthy'] }}</span>
            @if ($summary['degraded'] > 0)
                <span class="pill pill-warn">Degraded: {{ $summary['degraded'] }}</span>
            @endif
            <span class="pill {{ $summary['down'] > 0 ? 'pill-danger' : 'pill' }}">
                Down: {{ $summary['down'] }}
            </span>
            <a href="{{ route('monitor.index') }}" class="btn btn-ghost btn-sm">Refresh</a>
        </div>
    </div>

    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Probed</p>
                <p class="stat-value">{{ $summary['total'] }}</p>
                <p class="stat-hint">applications of the estate</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Healthy</p>
                <p class="stat-value" style="color: var(--ok);">{{ $summary['healthy'] }}</p>
                <p class="stat-hint">answering 200 with status ok</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Down</p>
                <p class="stat-value" style="color: var(--danger);">{{ $summary['down'] }}</p>
                <p class="stat-hint">unreachable or erroring</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Scraped by</p>
                <p class="stat-value" style="font-size: 1.1rem; padding-top: .5rem;">
                    <a href="{{ route('monitor.metrics') }}" class="mono">/metrics</a>
                </p>
                <p class="stat-hint">Prometheus exposition format</p>
            </div>
        </div>
    </div>

    <div class="card-surface">
        <div class="table-responsive">
            <table class="table table-crm align-middle">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Server</th>
                        <th>PHP</th>
                        <th>HTTP</th>
                        <th class="text-end">Response</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results as $result)
                        <tr>
                            <td>
                                <span class="cell-strong d-block">{{ $result['name'] }}</span>
                                <span class="text-dim small">{{ $result['url'] }}</span>
                                @if ($result['error'])
                                    <span class="text-dim small d-block">{{ Str::limit($result['error'], 90) }}</span>
                                @endif
                            </td>
                            <td class="text-uppercase mono">{{ $result['server'] }}</td>
                            <td>
                                @if ($result['php'])
                                    <span class="pill">{{ $result['php'] }}</span>
                                @else
                                    <span class="text-dim">—</span>
                                @endif
                            </td>
                            <td class="mono">
                                {{ $result['http_status'] ?? '—' }}
                            </td>
                            <td class="text-end text-dim mono">
                                {{ $result['response_ms'] !== null ? $result['response_ms'].' ms' : '—' }}
                            </td>
                            <td>
                                @php
                                    $tone = match ($result['status']) {
                                        'ok' => 'pill-ok',
                                        'degraded' => 'pill-warn',
                                        default => 'pill-danger',
                                    };
                                @endphp
                                <span class="pill {{ $tone }}">{{ $result['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="card-text-sm mt-3">
        Results are cached for {{ config('estate.cache_seconds') }} seconds, so a page refresh does not
        probe the whole estate again.
    </p>
@endsection