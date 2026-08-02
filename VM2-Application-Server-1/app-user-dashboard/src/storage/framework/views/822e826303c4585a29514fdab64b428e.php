<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'User Dashboard')); ?></title>
    <meta name="theme-color" content="#05070f">

    
    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
</head>
<body>
    <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(isset($header)): ?>
        <header class="page-header">
            <div class="container">
                <?php echo e($header); ?>

            </div>
        </header>
    <?php endif; ?>

    <main class="container pb-5">
        <?php if(session('status') && session('status') !== 'verification-link-sent'): ?>
            <div class="alert-soft alert-soft-ok section-gap" role="alert">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        <?php echo e($slot); ?>

    </main>

    <script src="<?php echo e(asset('vendor/bootstrap/bootstrap.bundle.min.js')); ?>" defer></script>
</body>
</html>
<?php /**PATH E:\Herd\devops-php-infrastructure\VM2-Application-Server-1\app-user-dashboard\src\resources\views/layouts/app.blade.php ENDPATH**/ ?>