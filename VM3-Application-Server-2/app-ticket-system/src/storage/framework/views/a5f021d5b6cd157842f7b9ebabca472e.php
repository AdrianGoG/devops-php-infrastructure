<?php $__env->startSection('title', $ticket->exists ? 'Edit '.$ticket->reference : 'New ticket'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <?php echo e($ticket->exists ? 'Edit '.$ticket->reference : 'New ticket'); ?>

            </h1>
            <p class="page-subtitle mb-0">
                <?php if($ticket->exists): ?>
                    Opened <?php echo e($ticket->created_at->diffForHumans()); ?>.
                <?php else: ?>
                    The reference is assigned automatically.
                <?php endif; ?>
            </p>
        </div>
        <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-ghost btn-sm">Back to queue</a>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert-soft alert-soft-err mb-4">
            The form has <?php echo e($errors->count()); ?> <?php echo e(Str::plural('error', $errors->count())); ?>.
            Please check the fields below.
        </div>
    <?php endif; ?>

    <form method="POST"
          action="<?php echo e($ticket->exists ? route('tickets.update', $ticket) : route('tickets.store')); ?>"
          class="card-surface card-pad" novalidate>
        <?php echo csrf_field(); ?>
        <?php if($ticket->exists): ?>
            <?php echo method_field('PUT'); ?>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-12">
                <label for="subject" class="form-label">Subject *</label>
                <input type="text" id="subject" name="subject" value="<?php echo e(old('subject', $ticket->subject)); ?>"
                       class="form-control <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php $__errorArgs = ['subject'];
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

            <div class="col-md-6">
                <label for="requester" class="form-label">Requester email *</label>
                <input type="email" id="requester" name="requester" value="<?php echo e(old('requester', $ticket->requester)); ?>"
                       class="form-control <?php $__errorArgs = ['requester'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="name@example.com">
                <?php $__errorArgs = ['requester'];
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

            <div class="col-md-6">
                <label for="assignee" class="form-label">Assignee</label>
                <input type="text" id="assignee" name="assignee" value="<?php echo e(old('assignee', $ticket->assignee)); ?>"
                       class="form-control <?php $__errorArgs = ['assignee'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Unassigned">
                <?php $__errorArgs = ['assignee'];
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

            <div class="col-md-6">
                <label for="priority" class="form-label">Priority *</label>
                <select id="priority" name="priority" class="form-select <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__currentLoopData = \App\Models\Ticket::PRIORITIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $one): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($one); ?>" <?php if(old('priority', $ticket->priority) === $one): echo 'selected'; endif; ?>>
                            <?php echo e(ucfirst($one)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['priority'];
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

            <div class="col-md-6">
                <label for="status" class="form-label">Status *</label>
                <select id="status" name="status" class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__currentLoopData = \App\Models\Ticket::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $one): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($one); ?>" <?php if(old('status', $ticket->status) === $one): echo 'selected'; endif; ?>>
                            <?php echo e(ucfirst(str_replace('_', ' ', $one))); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['status'];
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

            <div class="col-12">
                <label for="description" class="form-label">Description *</label>
                <textarea id="description" name="description" rows="6"
                          class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description', $ticket->description)); ?></textarea>
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

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-accent">
                    <?php echo e($ticket->exists ? 'Save changes' : 'Create ticket'); ?>

                </button>
                <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Herd\devops-php-infrastructure\VM3-Application-Server-2\app-ticket-system\src\resources\views/tickets/form.blade.php ENDPATH**/ ?>