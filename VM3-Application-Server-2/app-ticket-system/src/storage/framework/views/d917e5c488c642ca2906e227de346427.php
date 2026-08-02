<?php $__env->startSection('title', 'Queue'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Ticket queue</h1>
            <p class="page-subtitle mb-0">
                <?php echo e($tickets->count()); ?> <?php echo e(Str::plural('ticket', $tickets->count())); ?> shown
                <?php if($search !== '' || $status !== '' || $priority !== ''): ?>
                    · <a href="<?php echo e(route('tickets.index')); ?>">clear filters</a>
                <?php endif; ?>
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="pill">Total: <?php echo e($totalTickets); ?></span>
            <span class="pill <?php echo e($unresolvedCount > 0 ? 'pill-warn' : 'pill-ok'); ?>">
                Unresolved: <?php echo e($unresolvedCount); ?>

            </span>
            <span class="pill <?php echo e($urgentCount > 0 ? 'pill-danger' : 'pill-ok'); ?>">
                Urgent: <?php echo e($urgentCount); ?>

            </span>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('tickets.index')); ?>" class="card-surface card-pad mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="q" class="form-label">Search</label>
                <input type="text" id="q" name="q" value="<?php echo e($search); ?>"
                       class="form-control" placeholder="Reference, subject or requester">
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All statuses</option>
                    <?php $__currentLoopData = \App\Models\Ticket::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $one): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($one); ?>" <?php if($status === $one): echo 'selected'; endif; ?>>
                            <?php echo e(ucfirst(str_replace('_', ' ', $one))); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="priority" class="form-label">Priority</label>
                <select id="priority" name="priority" class="form-select">
                    <option value="">All</option>
                    <?php $__currentLoopData = \App\Models\Ticket::PRIORITIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $one): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($one); ?>" <?php if($priority === $one): echo 'selected'; endif; ?>><?php echo e(ucfirst($one)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
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
                        <th>Ref</th>
                        <th>Subject</th>
                        <th>Requester</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assignee</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="mono cell-strong"><?php echo e($ticket->reference); ?></td>
                            <td>
                                <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="cell-strong">
                                    <?php echo e(Str::limit($ticket->subject, 60)); ?>

                                </a>
                                <span class="text-dim small d-block">
                                    opened <?php echo e($ticket->created_at->diffForHumans()); ?>

                                </span>
                            </td>
                            <td class="text-dim small"><?php echo e($ticket->requester); ?></td>
                            <td><span class="pill <?php echo e($ticket->priorityClass()); ?>"><?php echo e($ticket->priority); ?></span></td>
                            <td><span class="pill <?php echo e($ticket->statusClass()); ?>"><?php echo e($ticket->statusLabel()); ?></span></td>
                            <td class="text-dim"><?php echo e($ticket->assignee ?? '—'); ?></td>
                            <td class="text-end">
                                <?php if($ticket->isUnresolved()): ?>
                                    <form method="POST" action="<?php echo e(route('tickets.close', $ticket)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit" class="btn btn-ghost btn-sm">Close</button>
                                    </form>
                                <?php endif; ?>

                                <a href="<?php echo e(route('tickets.edit', $ticket)); ?>" class="btn btn-ghost btn-sm">Edit</a>

                                <form method="POST" action="<?php echo e(route('tickets.destroy', $ticket)); ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete <?php echo e($ticket->reference); ?>?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger-soft btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-dim py-4">
                                No ticket matches the current filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Herd\devops-php-infrastructure\VM3-Application-Server-2\app-ticket-system\src\resources\views/tickets/index.blade.php ENDPATH**/ ?>