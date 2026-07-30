<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Monitor') · Estate Monitor</title>
    <meta name="theme-color" content="#05070f">

    {{-- Bootstrap is vendored in public/vendor: no build step, no CDN. --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg app-nav py-2">
        <div class="container">
            <a class="navbar-brand" href="{{ route('monitor.index') }}">
                <span class="brand-mark">MON</span>
                <span class="brand-text">
                    <span>Estate Monitor</span>
                    <small>VM4 · app-monitor · PHP {{ PHP_VERSION }}</small>
                </span>
            </a>

            <a href="{{ route('monitor.metrics') }}" class="btn btn-ghost btn-sm mono">/metrics</a>
        </div>
    </nav>

    <main class="container py-4 py-lg-5">
        @yield('content')
    </main>

    <footer class="app-footer">
        <div class="container d-flex flex-wrap justify-content-between gap-2">
            <span>app-monitor · VM4 Application Server 3 · port 8083</span>
            <span class="mono">Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</span>
        </div>
    </footer>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
</body>
</html>