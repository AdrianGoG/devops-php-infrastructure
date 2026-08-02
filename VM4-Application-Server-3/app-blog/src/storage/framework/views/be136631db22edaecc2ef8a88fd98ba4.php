<?php $__env->startSection('title', 'Editor'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Editor</h1>
            <p class="page-subtitle mb-0">Every post, drafts included.</p>
        </div>
        <a href="<?php echo e(route('posts.index')); ?>" class="btn btn-ghost btn-sm">View the public site</a>
    </div>

    <div class="card-surface">
        <div class="table-responsive">
            <table class="table table-crm align-middle">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="cell-strong d-block"><?php echo e($post->title); ?></span>
                                <span class="text-dim small mono">/<?php echo e($post->slug); ?></span>
                            </td>
                            <td class="text-dim"><?php echo e($post->author ?? '—'); ?></td>
                            <td>
                                <span class="pill <?php echo e($post->isPublished() ? 'pill-ok' : 'pill-warn'); ?>">
                                    <?php echo e($post->status); ?>

                                </span>
                            </td>
                            <td class="text-dim small">
                                <?php echo e(optional($post->published_at)->format('d M Y') ?? '—'); ?>

                            </td>
                            <td class="text-end">
                                <?php if($post->isPublished()): ?>
                                    <a href="<?php echo e(route('posts.show', $post)); ?>" class="btn btn-ghost btn-sm">View</a>
                                <?php endif; ?>

                                <a href="<?php echo e(route('posts.edit', $post)); ?>" class="btn btn-ghost btn-sm">Edit</a>

                                <form method="POST" action="<?php echo e(route('posts.destroy', $post)); ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this post?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger-soft btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-dim py-4">No post yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Herd\devops-php-infrastructure\VM4-Application-Server-3\app-blog\src\resources\views/posts/manage.blade.php ENDPATH**/ ?>