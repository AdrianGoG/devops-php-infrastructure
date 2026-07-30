<x-guest-layout>
    <h1 class="h5 mb-1">Sign in</h1>
    <p class="card-text-sm mb-4">Access the infrastructure dashboard.</p>

    @if (session('status'))
        <div class="alert-soft alert-soft-ok mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autofocus autocomplete="username" placeholder="name@example.com">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password" placeholder="••••••••">
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
            <label class="form-check-label" for="remember_me">Remember me</label>
        </div>

        <button type="submit" class="btn btn-accent w-100">Sign in</button>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
            <a class="btn-link-muted" href="{{ route('register') }}">Create an account</a>

            @if (Route::has('password.request'))
                <a class="btn-link-muted" href="{{ route('password.request') }}">Forgot your password?</a>
            @endif
        </div>
    </form>
</x-guest-layout>
