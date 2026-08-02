<?php $__env->startSection('title', 'Files'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Files</h1>
            <p class="page-subtitle mb-0">
                <?php echo e($files->count()); ?> <?php echo e(Str::plural('file', $files->count())); ?> shown
                <?php if($search !== ''): ?>
                    · <a href="<?php echo e(route('files.index')); ?>">clear search</a>
                <?php endif; ?>
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="pill">Stored: <?php echo e($totalFiles); ?></span>
            <span class="pill pill-accent"><?php echo e(round($totalBytes / 1048576, 2)); ?> MB</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <form method="POST" action="<?php echo e(route('files.store')); ?>" enctype="multipart/form-data"
                  class="card-surface card-pad">
                <?php echo csrf_field(); ?>

                <h2 class="card-title-sm">Upload a file</h2>
                <p class="card-text-sm mb-3">
                    Up to <?php echo e(round($maxKilobytes / 1024)); ?> MB. Files are kept on a Docker volume,
                    so they survive a container rebuild.
                </p>

                <div class="mb-3">
                    <label for="file" class="form-label">File *</label>
                    <input type="file" id="file" name="file"
                           class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                    <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" id="description" name="description" value="<?php echo e(old('description')); ?>"
                           class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           placeholder="What is this file?">
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit" class="btn btn-accent w-100">Upload</button>
            </form>
        </div>

        <div class="col-lg-8">
            <form method="GET" action="<?php echo e(route('files.index')); ?>" class="card-surface card-pad mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-9">
                        <label for="q" class="form-label">Search</label>
                        <input type="text" id="q" name="q" value="<?php echo e($search); ?>"
                               class="form-control" placeholder="File name or description">
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-ghost w-100">Search</button>
                    </div>
                </div>
            </form>

            <div class="card-surface">
                <div class="table-responsive">
                    <table class="table table-crm align-middle">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Type</th>
                                <th class="text-end">Size</th>
                                <th>Uploaded</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <span class="cell-strong d-block"><?php echo e($file->original_name); ?></span>
                                        <?php if($file->description): ?>
                                            <span class="text-dim small"><?php echo e($file->description); ?></span>
                                        <?php endif; ?>
                                        <?php if (! ($file->existsOnDisk())): ?>
                                            <span class="pill pill-danger mt-1">missing on disk</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="pill"><?php echo e($file->extension()); ?></span></td>
                                    <td class="text-end mono"><?php echo e($file->humanSize()); ?></td>
                                    <td class="text-dim small"><?php echo e($file->created_at->format('d M Y H:i')); ?></td>
                                    <td class="text-end">
                                        <a href="<?php echo e(route('files.download', $file)); ?>"
                                           class="btn btn-ghost btn-sm">Download</a>

                                        <form method="POST" action="<?php echo e(route('files.destroy', $file)); ?>"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete <?php echo e($file->original_name); ?>?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-danger-soft btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-dim py-4">
                                        No file stored yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Herd\devops-php-infrastructure\VM4-Application-Server-3\app-file-manager\src\resources\views/files/index.blade.php ENDPATH**/ ?>