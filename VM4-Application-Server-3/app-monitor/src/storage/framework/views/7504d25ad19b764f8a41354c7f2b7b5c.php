<?php $__env->startSection('title', 'Estate'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Estate health</h1>
            <p class="page-subtitle mb-0">
                Every application probed through its own health endpoint, live.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="pill pill-ok">Healthy: <?php echo e($summary['healthy']); ?></span>
            <?php if($summary['degraded'] > 0): ?>
                <span class="pill pill-warn">Degraded: <?php echo e($summary['degraded']); ?></span>
            <?php endif; ?>
            <span class="pill <?php echo e($summary['down'] > 0 ? 'pill-danger' : 'pill'); ?>">
                Down: <?php echo e($summary['down']); ?>

            </span>
            <a href="<?php echo e(route('monitor.index')); ?>" class="btn btn-ghost btn-sm">Refresh</a>
        </div>
    </div>

    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Probed</p>
                <p class="stat-value"><?php echo e($summary['total']); ?></p>
                <p class="stat-hint">applications of the estate</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Healthy</p>
                <p class="stat-value" style="color: var(--ok);"><?php echo e($summary['healthy']); ?></p>
                <p class="stat-hint">answering 200 with status ok</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Down</p>
                <p class="stat-value" style="color: var(--danger);"><?php echo e($summary['down']); ?></p>
                <p class="stat-hint">unreachable or erroring</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Scraped by</p>
                <p class="stat-value" style="font-size: 1.1rem; padding-top: .5rem;">
                    <a href="<?php echo e(route('monitor.metrics')); ?>" class="mono">/metrics</a>
                </p>
                <p class="stat-hint">Prometheus exposition format</p>
            </div>
        </div>
    </div>

    <div class="card-surface">
        <div class="table-responsive">
            <table class="table table-crm align-middle">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Server</th>
                        <th>PHP</th>
                        <th>HTTP</th>
                        <th class="text-end">Response</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <span class="cell-strong d-block"><?php echo e($result['name']); ?></span>
                                <span class="text-dim small"><?php echo e($result['url']); ?></span>
                                <?php if($result['error']): ?>
                                    <span class="text-dim small d-block"><?php echo e(Str::limit($result['error'], 90)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-uppercase mono"><?php echo e($result['server']); ?></td>
                            <td>
                                <?php if($result['php']): ?>
                                    <span class="pill"><?php echo e($result['php']); ?></span>
                                <?php else: ?>
                                    <span class="text-dim">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="mono">
                                <?php echo e($result['http_status'] ?? '—'); ?>

                            </td>
                            <td class="text-end text-dim mono">
                                <?php echo e($result['response_ms'] !== null ? $result['response_ms'].' ms' : '—'); ?>

                            </td>
                            <td>
                                <?php
                                    $tone = match ($result['status']) {
                                        'ok' => 'pill-ok',
                                        'degraded' => 'pill-warn',
                                        default => 'pill-danger',
                                    };
                                ?>
                                <span class="pill <?php echo e($tone); ?>"><?php echo e($result['status']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="card-text-sm mt-3">
        Results are cached for <?php echo e(config('estate.cache_seconds')); ?> seconds, so a page refresh does not
        probe the whole estate again.
    </p>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Herd\devops-php-infrastructure\VM4-Application-Server-3\app-monitor\src\resources\views/monitor/index.blade.php ENDPATH**/ ?>