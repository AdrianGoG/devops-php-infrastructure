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
    <div class="guest-wrap">
        <div class="guest-card">
            <a href="<?php echo e(route('login')); ?>" class="guest-brand">
                <span class="brand-mark">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'pipeline','size' => 19,'strokeWidth' => '1.9']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pipeline','size' => 19,'stroke-width' => '1.9']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                </span>
                <span><?php echo e(config('app.name', 'User Dashboard')); ?></span>
            </a>

            <div class="card-surface card-surface-pad">
                <?php echo e($slot); ?>

            </div>

            <p class="text-center card-text-sm mt-4 mb-0">
                app-user-dashboard · VM2 · PHP <?php echo e(PHP_VERSION); ?>

            </p>
        </div>
    </div>

    <script src="<?php echo e(asset('vendor/bootstrap/bootstrap.bundle.min.js')); ?>" defer></script>
</body>
</html>
<?php /**PATH E:\Herd\devops-php-infrastructure\VM2-Application-Server-1\app-user-dashboard\src\resources\views/layouts/guest.blade.php ENDPATH**/ ?>