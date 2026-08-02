<?php $__env->startSection('title', $post->title); ?>

<?php $__env->startSection('content'); ?>
    <article class="mx-auto" style="max-width: 760px;">
        <a href="<?php echo e(route('posts.index')); ?>" class="btn-link-muted small">← Back to articles</a>

        <h1 class="page-title mt-3 mb-2"><?php echo e($post->title); ?></h1>

        <p class="page-subtitle mb-4">
            <?php echo e(optional($post->published_at)->format('d M Y')); ?>

            · <?php echo e($post->readingMinutes()); ?> min read
            <?php if($post->author): ?>
                · by <?php echo e($post->author); ?>

            <?php endif; ?>
        </p>

        <div class="card-surface card-pad">
            <p class="card-text-sm mb-4" style="font-style: italic;"><?php echo e($post->excerpt); ?></p>

            <hr class="divider-soft">

            <div class="card-text-sm" style="white-space: pre-line;"><?php echo e($post->body); ?></div>
        </div>

        <div class="mt-4">
            <a href="<?php echo e(route('posts.edit', $post)); ?>" class="btn btn-ghost btn-sm">Edit this post</a>
        </div>
    </article>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Herd\devops-php-infrastructure\VM4-Application-Server-3\app-blog\src\resources\views/posts/show.blade.php ENDPATH**/ ?>