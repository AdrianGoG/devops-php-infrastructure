<?php

/**
 * app-crm - sign in.
 *
 * The only page reachable without a session, apart from health.php.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if (crm_logged_in()) {
    header('Location: /index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_check_csrf(isset($_POST['_token']) ? $_POST['_token'] : null);

    $email = isset($_POST['email']) ? crm_clean($_POST['email']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

    if (crm_attempt_login($email, $password)) {
        header('Location: /index.php');
        exit;
    }

    // One message for both a wrong address and a wrong password: telling them
    // apart would confirm which addresses have an account.
    $error = 'Those credentials do not match any account.';
}

$pageTitle = 'Sign in';
require __DIR__ . '/../includes/layout-top.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-7 col-lg-5">

        <div class="card-surface card-pad">

            <h1 class="page-title">Sign in</h1>
            <p class="page-subtitle mb-4">The client register is not public.</p>

            <?php if ($error !== ''): ?>
                <div class="alert-soft alert-soft-err mb-3"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="post" action="/login.php" novalidate>
                <input type="hidden" name="_token" value="<?php echo e(crm_csrf_token()); ?>">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo e($email); ?>" autocomplete="username" autofocus required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                           autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn-accent w-100">Sign in</button>
            </form>

        </div>

    </div>
</div>

<?php require __DIR__ . '/../includes/layout-bottom.php'; ?>
