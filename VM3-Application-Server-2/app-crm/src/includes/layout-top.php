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

            <a href="/create.php" class="btn btn-accent btn-sm">+ New client</a>
        </div>
    </nav>

    <main class="container py-4 py-lg-5">
