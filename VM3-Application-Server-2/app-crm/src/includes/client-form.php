<?php

/**
 * app-crm - the client form, shared by create.php and edit.php.
 *
 * Expects: $client, $errors, $formAction, $submitLabel.
 */
?>

<div class="d-flex align-items-end justify-content-between gap-3 mb-4">
    <div>
        <h1 class="page-title"><?php echo e($pageTitle); ?></h1>
        <p class="page-subtitle mb-0">Fields marked as required cannot be left empty.</p>
    </div>
    <a href="/index.php" class="btn btn-ghost btn-sm">Back to list</a>
</div>

<?php if ($errors): ?>
    <div class="alert-soft alert-soft-err mb-4">
        The form has <?php echo count($errors); ?>
        <?php echo count($errors) === 1 ? 'error' : 'errors'; ?>. Please check the fields below.
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo e($formAction); ?>" class="card-surface card-pad" novalidate>
    <input type="hidden" name="_token" value="<?php echo e(crm_csrf_token()); ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label for="company" class="form-label">Company *</label>
            <input type="text" id="company" name="company" value="<?php echo e($client['company']); ?>"
                   class="form-control <?php echo isset($errors['company']) ? 'is-invalid' : ''; ?>">
            <?php if (isset($errors['company'])): ?>
                <div class="invalid-feedback d-block"><?php echo e($errors['company']); ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="contact_name" class="form-label">Contact name *</label>
            <input type="text" id="contact_name" name="contact_name" value="<?php echo e($client['contact_name']); ?>"
                   class="form-control <?php echo isset($errors['contact_name']) ? 'is-invalid' : ''; ?>">
            <?php if (isset($errors['contact_name'])): ?>
                <div class="invalid-feedback d-block"><?php echo e($errors['contact_name']); ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" name="email" value="<?php echo e($client['email']); ?>"
                   class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback d-block"><?php echo e($errors['email']); ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo e($client['phone']); ?>"
                   class="form-control">
        </div>

        <div class="col-md-6">
            <label for="status" class="form-label">Status *</label>
            <select id="status" name="status"
                    class="form-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>">
                <?php foreach (crm_statuses() as $one): ?>
                    <option value="<?php echo e($one); ?>" <?php echo $client['status'] === $one ? 'selected' : ''; ?>>
                        <?php echo e(crm_status_label($one)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['status'])): ?>
                <div class="invalid-feedback d-block"><?php echo e($errors['status']); ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="tags" class="form-label">Tags</label>
            <input type="text" id="tags" name="tags" value="<?php echo e($client['tags']); ?>"
                   class="form-control" placeholder="saas, enterprise">
            <div class="form-text">Comma separated.</div>
        </div>

        <div class="col-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea id="notes" name="notes" rows="4" class="form-control"><?php echo e($client['notes']); ?></textarea>
        </div>

        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-accent"><?php echo e($submitLabel); ?></button>
            <a href="/index.php" class="btn btn-ghost">Cancel</a>
        </div>
    </div>
</form>
