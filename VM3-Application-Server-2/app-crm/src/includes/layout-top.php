<?php

/**
 * app-crm - top of every page.
 *
 * Expects $pageTitle to be set before the include.
 */

if (!isset($pageTitle)) {
    $pageTitle = 'Clients';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?> · CRM</title>
    <meta name="theme-color" content="#05070f">

    <!-- Bootstrap is served from assets/vendor: no CDN, no build step. -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg app-nav py-2">
        <div class="container">
            <a class="navbar-brand" href="/index.php">
                <span class="brand-mark">CRM</span>
                <span class="brand-text">
                    <span>Client Relations</span>
                    <small>VM3 · app-crm · PHP <?php echo e(PHP_VERSION); ?></small>
                </span>
            </a>

            <?php if (crm_logged_in()): ?>
                <div class="d-flex align-items-center gap-2">
                    <a href="/create.php" class="btn btn-accent btn-sm">+ New client</a>

                    <span class="text-dim small d-none d-md-inline ms-1">
                        <?php echo e(crm_user()['name']); ?>
                    </span>

                    <form method="post" action="/logout.php" class="m-0">
                        <input type="hidden" name="_token" value="<?php echo e(crm_csrf_token()); ?>">
                        <button type="submit" class="btn btn-ghost btn-sm">Sign out</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container py-4 py-lg-5">
