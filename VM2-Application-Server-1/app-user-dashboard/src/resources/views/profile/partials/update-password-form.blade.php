<section class="card-surface card-surface-pad h-100">
    <h2 class="card-title-sm">Password</h2>
    <p class="card-text-sm mb-4">Use a long, random password to keep the account secure.</p>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">Current password</label>
            <input id="update_password_current_password" type="password" name="current_password"
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label">New password</label>
            <input id="update_password_password" type="password" name="password"
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">Confirm password</label>
            <input id="update_password_password_confirmation" type="password" name="password_confirmation"
                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-accent">Change password</button>

            @if (session('status') === 'password-updated')
                <span class="card-text-sm">Saved.</span>
            @endif
        </div>
    </form>
</section>
