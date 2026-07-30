<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Blog') · DevOps Blog</title>
    <meta name="theme-color" content="#05070f">

    {{-- Bootstrap is vendored in public/vendor: no build step, no CDN. --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg app-nav py-2">
        <div class="container">
            <a class="navbar-brand" href="{{ route('posts.index') }}">
                <span class="brand-mark">BLG</span>
                <span class="brand-text">
                    <span>DevOps Blog</span>
                    <small>VM4 · app-blog · PHP {{ PHP_VERSION }}</small>
                </span>
            </a>

            <div class="d-flex gap-2">
                <a href="{{ route('posts.manage') }}" class="btn btn-ghost btn-sm">Editor</a>
                <a href="{{ route('posts.create') }}" class="btn btn-accent btn-sm">+ New post</a>
            </div>
        </div>
    </nav>

    <main class="container py-4 py-lg-5">
        @if (session('status'))
            <div class="alert-soft alert-soft-ok mb-4" role="alert">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="app-footer">
        <div class="container d-flex flex-wrap justify-content-between gap-2">
            <span>app-blog · VM4 Application Server 3 · port 8081</span>
            <span class="mono">Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</span>
        </div>
    </footer>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
</body>
</html>