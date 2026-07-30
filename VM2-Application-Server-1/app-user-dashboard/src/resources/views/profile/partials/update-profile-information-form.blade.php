<section class="card-surface card-surface-pad h-100">
    <h2 class="card-title-sm">Account information</h2>
    <p class="card-text-sm mb-4">Your name and email address.</p>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" novalidate>
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                   class="form-control @error('name') is-invalid @enderror"
                   required autocomplete="name">
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autocomplete="username">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert-soft alert-soft-warn mt-3">
                    Your email address is unverified.
                    <button form="send-verification" class="btn-link-muted border-0 bg-transparent p-0">
                        Click here to resend the verification email.
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <div class="mt-2">A new verification link has been sent to your email address.</div>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-accent">Save</button>

            @if (session('status') === 'profile-updated')
                <span class="card-text-sm">Saved.</span>
            @endif
        </div>
    </form>
</section>
