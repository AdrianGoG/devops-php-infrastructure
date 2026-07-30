<?php

/**
 * app-crm - delete a client.
 *
 * POST only and CSRF protected: a destructive action must never be reachable
 * through a plain link.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('This endpoint only accepts POST.');
}

crm_check_csrf(isset($_POST['_token']) ? $_POST['_token'] : null);

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id > 0) {
    crm_execute('DELETE FROM clients WHERE id = :id', array('id' => $id));
}

header('Location: /index.php');
exit;
