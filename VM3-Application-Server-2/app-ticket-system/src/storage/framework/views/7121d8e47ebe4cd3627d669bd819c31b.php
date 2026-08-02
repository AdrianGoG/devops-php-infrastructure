<?php $__env->startSection('title', $ticket->reference); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <span class="mono text-dim"><?php echo e($ticket->reference); ?></span>
            <h1 class="page-title"><?php echo e($ticket->subject); ?></h1>
            <p class="page-subtitle mb-0">
                Opened <?php echo e($ticket->created_at->diffForHumans()); ?> by <?php echo e($ticket->requester); ?>

                <?php if($ticket->resolved_at): ?>
                    · resolved <?php echo e($ticket->resolved_at->diffForHumans()); ?>

                <?php endif; ?>
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo e(route('tickets.edit', $ticket)); ?>" class="btn btn-ghost btn-sm">Edit</a>
            <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-ghost btn-sm">Back to queue</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-surface card-pad h-100">
                <h2 class="card-title-sm">Description</h2>
                <p class="card-text-sm" style="white-space: pre-line;"><?php echo e($ticket->description); ?></p>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-surface card-pad h-100">
                <h2 class="card-title-sm mb-3">Details</h2>

                <div class="table-responsive">
                    <table class="table table-crm">
                        <tbody>
                            <tr>
                                <td>Status</td>
                                <td class="text-end">
                                    <span class="pill <?php echo e($ticket->statusClass()); ?>"><?php echo e($ticket->statusLabel()); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Priority</td>
                                <td class="text-end">
                                    <span class="pill <?php echo e($ticket->priorityClass()); ?>"><?php echo e($ticket->priority); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Assignee</td>
                                <td class="text-end cell-strong"><?php echo e($ticket->assignee ?? 'Unassigned'); ?></td>
                            </tr>
                            <tr>
                                <td>Last update</td>
                                <td class="text-end text-dim"><?php echo e($ticket->updated_at->format('d M Y H:i')); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if($ticket->isUnresolved()): ?>
                    <form method="POST" action="<?php echo e(route('tickets.close', $ticket)); ?>" class="mt-3">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="btn btn-accent w-100">Close this ticket</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Herd\devops-php-infrastructure\VM3-Application-Server-2\app-ticket-system\src\resources\views/tickets/show.blade.php ENDPATH**/ ?>