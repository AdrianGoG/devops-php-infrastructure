<?php

/**
 * app-crm - sign out.
 *
 * POST only and CSRF protected, like delete.php: a signed in user must not be
 * logged out by a link on somebody else's page.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Sign out is a POST only endpoint.');
}

crm_check_csrf(isset($_POST['_token']) ? $_POST['_token'] : null);

crm_logout();

header('Location: /login.php');
exit;
