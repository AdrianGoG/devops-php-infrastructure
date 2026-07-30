<?php

/**
 * app-crm - health check.
 *
 * Same contract as the other applications of the estate, so the Python
 * monitoring utility and the Jenkins smoke test treat all nine the same way.
 *
 * It also reports the PHP version, which is the piece of information that
 * matters after an Ansible driven upgrade.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$database = 'ok';
$clients = null;

try {
    $row = crm_select_one('SELECT COUNT(*) AS total FROM clients');
    $clients = $row === null ? 0 : (int) $row['total'];
} catch (Exception $exception) {
    $database = 'unavailable';
    error_log('app-crm health check failed: ' . $exception->getMessage());
}

$healthy = $database === 'ok';

http_response_code($healthy ? 200 : 503);

echo json_encode(
    array(
        'status' => $healthy ? 'ok' : 'degraded',
        'application' => 'app-crm',
        'server' => 'VM3-Application-Server-2',
        'php' => PHP_VERSION,
        'environment' => crm_config('APP_ENV', 'production'),
        'database' => $database,
        'clients' => $clients,
        'checked_at' => date('c'),
    ),
    JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
);
