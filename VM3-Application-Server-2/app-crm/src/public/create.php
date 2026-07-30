<?php

/**
 * app-crm - add a client.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$errors = array();
$client = array(
    'company' => '',
    'contact_name' => '',
    'email' => '',
    'phone' => '',
    'status' => 'lead',
    'tags' => '',
    'notes' => '',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_check_csrf(isset($_POST['_token']) ? $_POST['_token'] : null);

    $client = crm_client_from_post();
    $errors = crm_validate_client($client);

    if (!$errors) {
        crm_execute(
            'INSERT INTO clients (company, contact_name, email, phone, status, tags, notes, created_at)
             VALUES (:company, :contact_name, :email, :phone, :status, :tags, :notes, :created_at)',
            array(
                'company' => $client['company'],
                'contact_name' => $client['contact_name'],
                'email' => $client['email'],
                'phone' => $client['phone'] !== '' ? $client['phone'] : null,
                'status' => $client['status'],
                'tags' => $client['tags'] !== '' ? $client['tags'] : null,
                'notes' => $client['notes'] !== '' ? $client['notes'] : null,
                'created_at' => date('Y-m-d H:i:s'),
            )
        );

        header('Location: /index.php');
        exit;
    }
}

$pageTitle = 'New client';
$formAction = '/create.php';
$submitLabel = 'Add client';

require __DIR__ . '/../includes/layout-top.php';
require __DIR__ . '/../includes/client-form.php';
require __DIR__ . '/../includes/layout-bottom.php';
