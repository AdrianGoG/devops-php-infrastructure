<?php $__env->startSection('title', 'Stock'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Stock</h1>
            <p class="page-subtitle mb-0">
                <?php echo e($products->count()); ?> <?php echo e(Str::plural('product', $products->count())); ?> shown
                <?php if($search !== '' || $lowOnly): ?>
                    · <a href="<?php echo e(route('products.index')); ?>">clear filters</a>
                <?php endif; ?>
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="pill">Catalogue: <?php echo e($totalProducts); ?></span>
            <span class="pill <?php echo e($lowStockCount > 0 ? 'pill-danger' : 'pill-ok'); ?>">
                Low stock: <?php echo e($lowStockCount); ?>

            </span>
            <span class="pill pill-ok">Value: <?php echo e(number_format($stockValue, 2)); ?></span>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('products.index')); ?>" class="card-surface card-pad mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-7">
                <label for="q" class="form-label">Search</label>
                <input type="text" id="q" name="q" value="<?php echo e($search); ?>"
                       class="form-control" placeholder="SKU, name or location">
            </div>

            <div class="col-md-3">
                <div class="form-check mt-md-4">
                    <input class="form-check-input" type="checkbox" id="low" name="low" value="1"
                           <?php echo e($lowOnly ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="low">Only low stock</label>
                </div>
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
                        <th>SKU</th>
                        <th>Product</th>
                        <th class="text-end">In stock</th>
                        <th class="text-end">Reorder at</th>
                        <th class="text-end">Unit price</th>
                        <th class="text-end">Stock value</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="mono cell-strong"><?php echo e($product->sku); ?></td>
                            <td>
                                <span class="cell-strong d-block"><?php echo e($product->name); ?></span>
                                <span class="text-dim small"><?php echo e($product->location ?? 'no location'); ?></span>
                            </td>
                            <td class="text-end">
                                <?php if($product->isLowStock()): ?>
                                    <span class="pill pill-danger"><?php echo e($product->quantity); ?></span>
                                <?php else: ?>
                                    <span class="cell-strong"><?php echo e($product->quantity); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-dim"><?php echo e($product->reorder_level); ?></td>
                            <td class="text-end"><?php echo e(number_format((float) $product->unit_price, 2)); ?></td>
                            <td class="text-end cell-strong"><?php echo e(number_format($product->stockValue(), 2)); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-ghost btn-sm">Edit</a>

                                <form method="POST" action="<?php echo e(route('products.destroy', $product)); ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete <?php echo e($product->name); ?>?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger-soft btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-dim py-4">
                                No product matches the current filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Herd\devops-php-infrastructure\VM3-Application-Server-2\app-inventory\src\resources\views/products/index.blade.php ENDPATH**/ ?>