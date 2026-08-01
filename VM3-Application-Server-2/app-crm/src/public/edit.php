<?php

/**
 * app-crm - edit a client.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

crm_require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);

$existing = crm_find_client($id);

if ($existing === null) {
    http_response_code(404);

    $pageTitle = 'Client not found';
    require __DIR__ . '/../includes/layout-top.php';
    echo '<div class="alert-soft alert-soft-err">This client does not exist. '
        . '<a href="/index.php">Back to the list</a>.</div>';
    require __DIR__ . '/../includes/layout-bottom.php';
    exit;
}

$errors = array();
$client = $existing;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_check_csrf(isset($_POST['_token']) ? $_POST['_token'] : null);

    $client = crm_client_from_post();
    $client['id'] = $id;

    $errors = crm_validate_client($client);

    if (!$errors) {
        crm_execute(
            'UPDATE clients SET company = :company, contact_name = :contact_name, email = :email,
                    phone = :phone, status = :status, tags = :tags, notes = :notes
             WHERE id = :id',
            array(
                'company' => $client['company'],
                'contact_name' => $client['contact_name'],
                'email' => $client['email'],
                'phone' => $client['phone'] !== '' ? $client['phone'] : null,
                'status' => $client['status'],
                'tags' => $client['tags'] !== '' ? $client['tags'] : null,
                'notes' => $client['notes'] !== '' ? $client['notes'] : null,
                'id' => $id,
            )
        );

        header('Location: /index.php');
        exit;
    }
}

$pageTitle = 'Edit ' . $existing['company'];
$formAction = '/edit.php?id=' . $id;
$submitLabel = 'Save changes';

require __DIR__ . '/../includes/layout-top.php';
require __DIR__ . '/../includes/client-form.php';
require __DIR__ . '/../includes/layout-bottom.php';
