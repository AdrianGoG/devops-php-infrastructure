<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'Files'); ?> · File Manager</title>
    <meta name="theme-color" content="#05070f">

    
    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg app-nav py-2">
        <div class="container">
            <a class="navbar-brand" href="<?php echo e(route('files.index')); ?>">
                <span class="brand-mark">FLS</span>
                <span class="brand-text">
                    <span>File Manager</span>
                    <small>VM4 · app-file-manager · PHP <?php echo e(PHP_VERSION); ?></small>
                </span>
            </a>
        </div>
    </nav>

    <main class="container py-4 py-lg-5">
        <?php if(session('status')): ?>
            <div class="alert-soft alert-soft-ok mb-4" role="alert"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert-soft alert-soft-err mb-4" role="alert"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="app-footer">
        <div class="container d-flex flex-wrap justify-content-between gap-2">
            <span>app-file-manager · VM4 Application Server 3 · port 8082</span>
            <span class="mono">Laravel <?php echo e(app()->version()); ?> · PHP <?php echo e(PHP_VERSION); ?></span>
        </div>
    </footer>

    <script src="<?php echo e(asset('vendor/bootstrap/bootstrap.bundle.min.js')); ?>" defer></script>
</body>
</html><?php /**PATH E:\Herd\devops-php-infrastructure\VM4-Application-Server-3\app-file-manager\src\resources\views/layouts/app.blade.php ENDPATH**/ ?>