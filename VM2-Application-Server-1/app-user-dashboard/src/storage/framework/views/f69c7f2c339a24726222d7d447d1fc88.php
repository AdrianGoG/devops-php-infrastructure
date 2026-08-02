<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <span class="eyebrow mb-2 d-inline-flex">Infrastructure</span>
                <h1 class="page-title">Welcome back, <?php echo e(Str::before(Auth::user()->name, ' ')); ?></h1>
                <p class="page-subtitle">
                    Live state of the estate, read from
                    <span class="mono">app-api</span> on VM2.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if($registryReachable): ?>
                    <span class="pill pill-ok"><span class="dot"></span> registry online</span>
                <?php else: ?>
                    <span class="pill pill-danger"><span class="dot"></span> registry offline</span>
                <?php endif; ?>

                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'refresh','size' => 15]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'refresh','size' => 15]); ?>
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
                    Refresh
                </a>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <?php if (! ($registryReachable)): ?>
        <div class="alert-soft alert-soft-warn section-gap d-flex gap-2 align-items-start" role="alert">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'alert','size' => 18]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alert','size' => 18]); ?>
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
            <div>
                <strong>The registry API is not answering.</strong>
                The dashboard cannot reach <span class="mono"><?php echo e($registryUrl); ?></span>, so the panels below are
                empty. This is the expected state while <span class="mono">app-api</span> is being upgraded to
                PHP 8.3 - start it with <span class="mono">docker compose up -d</span> in the app-api directory.
            </div>
        </div>
    <?php endif; ?>

    
    <div class="row g-3 g-lg-4 section-gap">
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Servers</p>
                <p class="stat-value text-gradient"><?php echo e($stats['servers']); ?></p>
                <p class="stat-hint">1 control node + application servers</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Applications</p>
                <p class="stat-value text-gradient"><?php echo e($stats['applications']); ?></p>
                <p class="stat-hint">PHP apps under the pipeline</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Legacy</p>
                <p class="stat-value" style="color: var(--danger);"><?php echo e($stats['legacy']); ?></p>
                <p class="stat-hint">still to migrate to PHP 8.3</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <p class="stat-label">Failed deploys</p>
                <p class="stat-value" style="color: var(--warn);"><?php echo e($stats['failed_deployments']); ?></p>
                <p class="stat-hint">in the recent history</p>
            </div>
        </div>
    </div>

    
    <div class="card-surface section-gap">
        <div class="card-head">
            <div>
                <h2>Applications</h2>
                <p class="card-text-sm">Every PHP application of the infrastructure, with its runtime version.</p>
            </div>
            <span class="pill">GET /api/applications</span>
        </div>

        <div class="table-wrap">
            <table class="table table-dash align-middle">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Server</th>
                        <th>PHP</th>
                        <th>Port</th>
                        <th>Status</th>
                        <th class="text-end">Open</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="cell-strong d-block"><?php echo e($application['name']); ?></span>
                                <span class="text-dim" style="font-size: .78rem;"><?php echo e($application['title']); ?></span>
                            </td>
                            <td class="text-uppercase mono"><?php echo e($application['server']); ?></td>
                            <td><span class="pill"><?php echo e($application['php_version']); ?></span></td>
                            <td class="mono"><?php echo e($application['port']); ?></td>
                            <td>
                                <?php
                                    $tone = match ($application['status']) {
                                        'ok' => 'pill-ok',
                                        'legacy' => 'pill-danger',
                                        default => 'pill-warn',
                                    };
                                ?>
                                <span class="pill <?php echo e($tone); ?>"><span class="dot"></span> <?php echo e($application['status']); ?></span>
                            </td>
                            <td class="text-end">
                                <?php if(! empty($application['url'])): ?>
                                    <a href="<?php echo e($application['url']); ?>" target="_blank" rel="noopener"
                                       class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-1">
                                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'external','size' => 14]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'external','size' => 14]); ?>
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
                                    </a>
                                <?php else: ?>
                                    <span class="text-dim">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center text-dim py-4">
                                No data. The registry API did not return any application.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-7">
            <div class="card-surface h-100">
                <div class="card-head">
                    <div>
                        <h2>Recent deployments</h2>
                        <p class="card-text-sm">Written by the Jenkins pipeline at the end of every run.</p>
                    </div>
                    <span class="pill">GET /api/deployments</span>
                </div>

                <div class="table-wrap">
                    <table class="table table-dash align-middle">
                        <thead>
                            <tr>
                                <th>Application</th>
                                <th>Build</th>
                                <th>Branch</th>
                                <th>Result</th>
                                <th>When</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $deployments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deployment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="cell-strong"><?php echo e($deployment['application']); ?></td>
                                    <td class="mono">#<?php echo e($deployment['build_number'] ?? '—'); ?></td>
                                    <td class="mono"><?php echo e($deployment['branch'] ?? '—'); ?></td>
                                    <td>
                                        <?php
                                            $tone = match ($deployment['result']) {
                                                'success' => 'pill-ok',
                                                'failed' => 'pill-danger',
                                                default => 'pill-warn',
                                            };
                                        ?>
                                        <span class="pill <?php echo e($tone); ?>"><?php echo e($deployment['result']); ?></span>
                                    </td>
                                    <td class="text-dim" style="font-size: .8rem;"><?php echo e($deployment['deployed_at']); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-dim py-4">
                                        No deployment recorded yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="col-lg-5">
            <div class="card-surface h-100">
                <div class="card-head">
                    <div>
                        <h2>Servers</h2>
                        <p class="card-text-sm">The machines of the infrastructure.</p>
                    </div>
                    <span class="pill">GET /api/servers</span>
                </div>

                <div class="card-surface-pad d-flex flex-column gap-3">
                    <?php $__empty_1 = true; $__currentLoopData = $servers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $server): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="d-flex align-items-start gap-3">
                            <span class="brand-mark" style="background: rgba(99,102,241,.16); color: #a5b4fc;">
                                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'server','size' => 17]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'server','size' => 17]); ?>
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
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="cell-strong"><?php echo e($server['key']); ?></span>
                                    <?php if(! empty($server['host'])): ?>
                                        <span class="pill"><?php echo e($server['host']); ?></span>
                                    <?php else: ?>
                                        <span class="pill pill-accent">control node</span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text-sm mt-1 mb-0">
                                    <?php echo e($server['name']); ?> ·
                                    <?php echo e($server['applications_count']); ?>

                                    <?php echo e(Str::plural('application', $server['applications_count'])); ?>

                                </p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="card-text-sm text-center text-dim mb-0 py-3">No server reported.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\Herd\devops-php-infrastructure\VM2-Application-Server-1\app-user-dashboard\src\resources\views/dashboard.blade.php ENDPATH**/ ?>