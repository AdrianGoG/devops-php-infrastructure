<x-layout title="503 · Service unavailable">
    <section class="section text-center">
        <div class="container container-narrow">
            <span class="pill pill-danger mono mb-4 d-inline-flex" style="font-size: .95rem; padding: .5rem .9rem;">
                HTTP 503
            </span>

            <h1 class="display-hero mb-3 mx-auto" style="max-width: 20ch;">
                The application is <span class="text-gradient">unavailable</span>
            </h1>

            <p class="lead-muted mb-4">
                The stack is being updated, or the PHP-FPM container failed to start after a PHP version
                upgrade. The Python utility reports the situation, and the application returns to
                <span class="mono">HTTP 200</span> once the source code is adapted and redeployed.
            </p>

            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ route('home') }}" class="btn btn-accent d-inline-flex align-items-center gap-2">
                    <x-icon name="rollback" :size="18" />
                    Try again
                </a>
                <a href="{{ route('pipeline') }}#migration" class="btn btn-ghost d-inline-flex align-items-center gap-2">
                    <x-icon name="pipeline" :size="18" />
                    The migration process
                </a>
            </div>
        </div>
    </section>
</x-layout>
