@props([
    'title' => null,
    'description' => null,
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' · '.config('project.meta.name') : config('project.meta.name') }}</title>
    <meta name="description" content="{{ $description ?? config('project.meta.description') }}">
    <meta name="author" content="{{ config('project.meta.author') }}">
    <meta name="theme-color" content="#05070f">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('project.meta.name') }}">
    <meta property="og:title" content="{{ $title ?? config('project.meta.title') }}">
    <meta property="og:description" content="{{ $description ?? config('project.meta.description') }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    {{-- Bootstrap is vendored in public/vendor, so there is no CDN dependency at runtime. --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.navbar')

    <main id="main">
        {{ $slot }}
    </main>

    @include('partials.footer')

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
</body>
</html>
