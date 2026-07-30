<x-layout title="404 · Page not found">
    <section class="section text-center">
        <div class="container container-narrow">
            <span class="pill pill-warn mono mb-4 d-inline-flex" style="font-size: .95rem; padding: .5rem .9rem;">
                HTTP 404
            </span>

            <h1 class="display-hero mb-3 mx-auto" style="max-width: 18ch;">
                This page does <span class="text-gradient">not exist</span>
            </h1>

            <p class="lead-muted mb-4">
                The requested route is not defined in the application. In the Python monitoring report, a 404
                means a missing route or files missing after an incomplete distribution.
            </p>

            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ route('home') }}" class="btn btn-accent d-inline-flex align-items-center gap-2">
                    <x-icon name="arrow-right" :size="18" />
                    Back to the home page
                </a>
                <a href="{{ route('monitoring') }}" class="btn btn-ghost d-inline-flex align-items-center gap-2">
                    <x-icon name="chart" :size="18" />
                    Monitored HTTP codes
                </a>
            </div>
        </div>
    </section>
</x-layout>
