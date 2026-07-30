<section class="card-surface card-surface-pad">
    <h2 class="card-title-sm">Delete account</h2>
    <p class="card-text-sm mb-4">
        Once the account is deleted, all of its data is removed permanently. This cannot be undone.
    </p>

    <button type="button" class="btn btn-danger-soft" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
        Delete account
    </button>

    {{-- Bootstrap modal - replaces the Alpine powered one Breeze ships with. --}}
    <div class="modal fade" id="deleteAccountModal" tabindex="-1"
         aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h3 class="card-title-sm mb-0" id="deleteAccountModalLabel">
                            Are you sure you want to delete your account?
                        </h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="card-text-sm mb-3">
                            Enter your password to confirm you want to permanently delete this account.
                        </p>

                        <label for="delete_password" class="form-label">Password</label>
                        <input id="delete_password" type="password" name="password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               placeholder="Password">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger-soft">Delete account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@if ($errors->userDeletion->isNotEmpty())
    {{-- Reopen the modal when the password was wrong, so the error is visible. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
        });
    </script>
@endif
