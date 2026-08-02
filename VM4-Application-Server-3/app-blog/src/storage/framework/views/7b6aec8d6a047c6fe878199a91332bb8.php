<?php $__env->startSection('title', 'Articles'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Articles</h1>
            <p class="page-subtitle mb-0">
                <?php echo e($posts->count()); ?> published <?php echo e(Str::plural('article', $posts->count())); ?>

                <?php if($search !== ''): ?>
                    for "<?php echo e($search); ?>" · <a href="<?php echo e(route('posts.index')); ?>">clear</a>
                <?php endif; ?>
            </p>
        </div>

        <?php if($draftCount > 0): ?>
            <span class="pill pill-warn"><?php echo e($draftCount); ?> <?php echo e(Str::plural('draft', $draftCount)); ?></span>
        <?php endif; ?>
    </div>

    <form method="GET" action="<?php echo e(route('posts.index')); ?>" class="card-surface card-pad mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-10">
                <label for="q" class="form-label">Search</label>
                <input type="text" id="q" name="q" value="<?php echo e($search); ?>"
                       class="form-control" placeholder="Title or excerpt">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-ghost w-100">Search</button>
            </div>
        </div>
    </form>

    <div class="d-flex flex-column gap-3">
        <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="card-surface card-pad">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="pill pill-ok">published</span>
                    <span class="text-dim small">
                        <?php echo e(optional($post->published_at)->format('d M Y')); ?>

                        · <?php echo e($post->readingMinutes()); ?> min read
                        <?php if($post->author): ?>
                            · <?php echo e($post->author); ?>

                        <?php endif; ?>
                    </span>
                </div>

                <h2 class="card-title-sm mb-2">
                    <a href="<?php echo e(route('posts.show', $post)); ?>"><?php echo e($post->title); ?></a>
                </h2>

                <p class="card-text-sm"><?php echo e($post->excerpt); ?></p>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card-surface card-pad text-center text-dim">
                No published article yet.
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Herd\devops-php-infrastructure\VM4-Application-Server-3\app-blog\src\resources\views/posts/index.blade.php ENDPATH**/ ?>