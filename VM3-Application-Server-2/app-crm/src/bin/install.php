#!/usr/bin/env php
<?php

/**
 * app-crm - one time setup.
 *
 *   php bin/install.php
 *
 * Creates the users table and the account used to sign in. Both steps are
 * idempotent, so the deployment playbook runs it on every deployment: an
 * existing account is left exactly as it is, password included.
 *
 * The credentials come from .env when they are set there:
 *
 *   CRM_ADMIN_EMAIL=you@example.com
 *   CRM_ADMIN_PASSWORD=something-long
 *   CRM_ADMIN_NAME=Your Name
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, 'bin/install.php must be run from the command line.' . PHP_EOL);
    exit(1);
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$email = (string) crm_config('CRM_ADMIN_EMAIL', 'admin@devops.test');
$password = (string) crm_config('CRM_ADMIN_PASSWORD', 'password');
$name = (string) crm_config('CRM_ADMIN_NAME', 'DevOps Admin');

try {
    crm_execute(crm_users_schema());

    $existing = crm_select_one('SELECT id FROM users WHERE email = :email', array('email' => $email));

    if ($existing !== null) {
        fwrite(STDOUT, 'Account ' . $email . ' already exists, left untouched.' . PHP_EOL);
        exit(0);
    }

    crm_execute(
        'INSERT INTO users (name, email, password_hash, created_at)
         VALUES (:name, :email, :password_hash, :created_at)',
        array(
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
        )
    );

    fwrite(STDOUT, 'Created account ' . $email . PHP_EOL);
} catch (Exception $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
