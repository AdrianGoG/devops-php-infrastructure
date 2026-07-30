<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'User Dashboard') }}</title>
    <meta name="theme-color" content="#05070f">

    {{-- Bootstrap is vendored in public/vendor: no build step, no CDN. --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    @include('layouts.navigation')

    @isset($header)
        <header class="page-header">
            <div class="container">
                {{ $header }}
            </div>
        </header>
    @endisset

    <main class="container pb-5">
        @if (session('status') && session('status') !== 'verification-link-sent')
            <div class="alert-soft alert-soft-ok section-gap" role="alert">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
</body>
</html>
