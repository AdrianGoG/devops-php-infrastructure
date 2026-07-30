<x-guest-layout>
    <h1 class="h5 mb-1">Forgot your password?</h1>
    <p class="card-text-sm mb-4">
        Enter your email address and we will send you a link to choose a new one.
    </p>

    @if (session('status'))
        <div class="alert-soft alert-soft-ok mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autofocus placeholder="name@example.com">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-accent w-100">Email password reset link</button>

        <p class="card-text-sm text-center mt-4 mb-0">
            <a href="{{ route('login') }}">Back to sign in</a>
        </p>
    </form>
</x-guest-layout>
