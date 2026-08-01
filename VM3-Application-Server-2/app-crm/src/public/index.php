<?php

/**
 * app-crm - the client list, with search and status filter.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

crm_require_login();

$search = isset($_GET['q']) ? crm_clean($_GET['q']) : '';
$status = isset($_GET['status']) ? crm_clean($_GET['status']) : '';

if (!in_array($status, crm_statuses(), true)) {
    $status = '';
}

$clients = crm_find_clients($search, $status);
$counts = crm_count_by_status();

$pageTitle = 'Clients';
require __DIR__ . '/../includes/layout-top.php';
?>

<div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
    <div>
        <h1 class="page-title">Clients</h1>
        <p class="page-subtitle mb-0">
            <?php echo count($clients); ?>
            <?php echo count($clients) === 1 ? 'client' : 'clients'; ?> shown
            <?php if ($search !== '' || $status !== ''): ?>
                · <a href="/index.php">clear filters</a>
            <?php endif; ?>
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <?php foreach (crm_statuses() as $one): ?>
            <span class="pill <?php echo crm_status_class($one); ?>">
                <?php echo e(crm_status_label($one)); ?>:
                <?php echo isset($counts[$one]) ? (int) $counts[$one] : 0; ?>
            </span>
        <?php endforeach; ?>
    </div>
</div>

<form method="GET" action="/index.php" class="card-surface card-pad mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-6">
            <label for="q" class="form-label">Search</label>
            <input type="text" id="q" name="q" value="<?php echo e($search); ?>"
                   class="form-control" placeholder="Company, contact or email">
        </div>

        <div class="col-md-4">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">All statuses</option>
                <?php foreach (crm_statuses() as $one): ?>
                    <option value="<?php echo e($one); ?>" <?php echo $status === $one ? 'selected' : ''; ?>>
                        <?php echo e(crm_status_label($one)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-ghost w-100">Filter</button>
        </div>
    </div>
</form>

<div class="card-surface">
    <div class="table-responsive">
        <table class="table table-crm align-middle">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Tags</th>
                    <th>Status</th>
                    <th>Added</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$clients): ?>
                    <tr>
                        <td colspan="6" class="text-center text-dim py-4">
                            No client matches the current filters.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar"><?php echo e(crm_initial($client['company'])); ?></span>
                                <div>
                                    <span class="cell-strong d-block"><?php echo e($client['company']); ?></span>
                                    <?php if ($client['phone']): ?>
                                        <span class="text-dim small"><?php echo e($client['phone']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php echo e($client['contact_name']); ?>
                            <a class="d-block small" href="mailto:<?php echo e($client['email']); ?>">
                                <?php echo e($client['email']); ?>
                            </a>
                        </td>
                        <td class="text-dim">
                            <?php echo e(crm_format_tags($client['tags'])); ?>
                        </td>
                        <td>
                            <span class="pill <?php echo crm_status_class($client['status']); ?>">
                                <?php echo e(crm_status_label($client['status'])); ?>
                            </span>
                        </td>
                        <td class="text-dim small">
                            <?php echo e(date('d M Y', strtotime($client['created_at']))); ?>
                        </td>
                        <td class="text-end">
                            <a href="/edit.php?id=<?php echo (int) $client['id']; ?>"
                               class="btn btn-ghost btn-sm">Edit</a>

                            <form method="POST" action="/delete.php" class="d-inline"
                                  onsubmit="return confirm('Delete <?php echo e($client['company']); ?>?');">
                                <input type="hidden" name="_token" value="<?php echo e(crm_csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $client['id']; ?>">
                                <button type="submit" class="btn btn-danger-soft btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/layout-bottom.php'; ?>
