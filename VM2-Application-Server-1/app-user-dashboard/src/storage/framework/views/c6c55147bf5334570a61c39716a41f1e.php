<section class="card-surface card-surface-pad">
    <h2 class="card-title-sm">Delete account</h2>
    <p class="card-text-sm mb-4">
        Once the account is deleted, all of its data is removed permanently. This cannot be undone.
    </p>

    <button type="button" class="btn btn-danger-soft" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
        Delete account
    </button>

    
    <div class="modal fade" id="deleteAccountModal" tabindex="-1"
         aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="<?php echo e(route('profile.destroy')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('delete'); ?>

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
                               class="form-control <?php $__errorArgs = ['password', 'userDeletion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               placeholder="Password">
                        <?php $__errorArgs = ['password', 'userDeletion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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

<?php if($errors->userDeletion->isNotEmpty()): ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
        });
    </script>
<?php endif; ?>
<?php /**PATH E:\Herd\devops-php-infrastructure\VM2-Application-Server-1\app-user-dashboard\src\resources\views/profile/partials/delete-user-form.blade.php ENDPATH**/ ?>