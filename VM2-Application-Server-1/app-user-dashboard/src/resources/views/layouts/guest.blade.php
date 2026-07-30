<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'User Dashboard') }}</title>
    <meta name="theme-color" content="#05070f">

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="guest-wrap">
        <div class="guest-card">
            <a href="{{ route('login') }}" class="guest-brand">
                <span class="brand-mark">
                    <x-icon name="pipeline" :size="19" stroke-width="1.9" />
                </span>
                <span>{{ config('app.name', 'User Dashboard') }}</span>
            </a>

            <div class="card-surface card-surface-pad">
                {{ $slot }}
            </div>

            <p class="text-center card-text-sm mt-4 mb-0">
                app-user-dashboard · VM2 · PHP {{ PHP_VERSION }}
            </p>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
</body>
</html>
