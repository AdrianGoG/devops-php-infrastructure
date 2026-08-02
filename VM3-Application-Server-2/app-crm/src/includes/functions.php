<?php

/**
 * app-crm - helpers.
 *
 * ======================================================================
 * THIS FILE CONTAINS THE THREE DELIBERATE PHP 8 INCOMPATIBILITIES.
 * Each one is marked with "LEGACY" and documented in MIGRATION.md.
 * ======================================================================
 */

require_once __DIR__ . '/db.php';

/** The statuses a client can have. */
function crm_statuses()
{
    return array('lead', 'active', 'churned');
}

/**
 * Escape a value for HTML output.
 *
 * @param string|null $value
 * @return string
 */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Clean a value that arrived from a form.
 *
 * LEGACY #1 (MIGRATION.md): get_magic_quotes_gpc() was deprecated in PHP 7.4
 * and REMOVED in PHP 8.0. On PHP 8 this raises
 * "Call to undefined function get_magic_quotes_gpc()", so saving a client
 * fails with HTTP 500.
 *
 * @param string|null $value
 * @return string
 */
function crm_clean($value)
{
    if ($value === null) {
        return '';
    }

    // LEGACY: removed in PHP 8.0.
    // if (get_magic_quotes_gpc()) {
    //     $value = stripslashes($value);
    // }

    // MIGRATION FIX #2
    // (delete the four lines above - the branch has been dead code since 5.4)

    return trim($value);
}

/**
 * Format the comma separated tags of a client for display.
 *
 * LEGACY #2 (MIGRATION.md): implode() is called with the array first and the
 * separator second. That argument order was deprecated in PHP 7.4 and REMOVED
 * in PHP 8.0, where it raises a TypeError - so the client list returns
 * HTTP 500.
 *
 * @param string|null $tags
 * @return string
 */
function crm_format_tags($tags)
{
    if ($tags === null || trim($tags) === '') {
        return '';
    }

    $pieces = array();

    foreach (explode(',', $tags) as $tag) {
        $tag = trim($tag);

        if ($tag !== '') {
            $pieces[] = ucfirst($tag);
        }
    }

    // LEGACY: reversed argument order, removed in PHP 8.0.
   // return implode($pieces, ' · ');

    // MIGRATION FIX #3
    return implode(' · ', $pieces);
}

/**
 * Human readable label for a client status.
 *
 * @param string $status
 * @return string
 */
function crm_status_label($status)
{
    switch ($status) {
        case 'active':
            return 'Active client';
        case 'lead':
            return 'Lead';
        default:
            return 'Churned';
    }
}

/**
 * First letter of a company name, for the avatar shown in the client list.
 *
 * LEGACY #3 (MIGRATION.md): the string offset is read with curly braces. That
 * syntax was deprecated in PHP 7.4 and REMOVED in PHP 8.0, where it is a parse
 * error - so this whole file stops loading and every page of the application
 * breaks. It is the first error you hit after the upgrade.
 *
 * @param string $company
 * @return string
 */
function crm_initial($company)
{
    $company = trim((string) $company);

    if ($company === '') {
        return '?';
    }

    // LEGACY: curly brace string offset, parse error on PHP 8.0.
    //return strtoupper($company{0});

    // MIGRATION FIX #1
    return strtoupper($company[0]);
}

/**
 * Bootstrap pill class for a status.
 *
 * @param string $status
 * @return string
 */
function crm_status_class($status)
{
    switch ($status) {
        case 'active':
            return 'pill-ok';
        case 'lead':
            return 'pill-warn';
        default:
            return 'pill-danger';
    }
}

/**
 * The CSRF token of the current session, created on first use.
 *
 * @return string
 */
function crm_csrf_token()
{
    // The session is started by config.php, before any output.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Stop the request unless the submitted token matches the session one.
 *
 * @param string|null $token
 * @return void
 */
function crm_check_csrf($token)
{
    if (!hash_equals(crm_csrf_token(), (string) $token)) {
        http_response_code(419);
        exit('The form session expired. Please go back and try again.');
    }
}

/**
 * Validate the client form.
 *
 * @param array $data
 * @return array field => message
 */
function crm_validate_client($data)
{
    $errors = array();

    if ($data['company'] === '') {
        $errors['company'] = 'The company name is required.';
    } elseif (strlen($data['company']) > 120) {
        $errors['company'] = 'The company name may not exceed 120 characters.';
    }

    if ($data['contact_name'] === '') {
        $errors['contact_name'] = 'The contact name is required.';
    }

    if ($data['email'] === '') {
        $errors['email'] = 'The email address is required.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'That email address is not valid.';
    }

    if (!in_array($data['status'], crm_statuses(), true)) {
        $errors['status'] = 'Choose one of the available statuses.';
    }

    return $errors;
}

/**
 * Read the client form out of $_POST.
 *
 * @return array
 */
function crm_client_from_post()
{
    return array(
        'company' => crm_clean(isset($_POST['company']) ? $_POST['company'] : null),
        'contact_name' => crm_clean(isset($_POST['contact_name']) ? $_POST['contact_name'] : null),
        'email' => crm_clean(isset($_POST['email']) ? $_POST['email'] : null),
        'phone' => crm_clean(isset($_POST['phone']) ? $_POST['phone'] : null),
        'status' => crm_clean(isset($_POST['status']) ? $_POST['status'] : null),
        'tags' => crm_clean(isset($_POST['tags']) ? $_POST['tags'] : null),
        'notes' => crm_clean(isset($_POST['notes']) ? $_POST['notes'] : null),
    );
}

/**
 * Clients, optionally filtered by search term and status.
 *
 * @param string $search
 * @param string $status
 * @return array
 */
function crm_find_clients($search = '', $status = '')
{
    $sql = 'SELECT * FROM clients';
    $where = array();
    $bindings = array();

    if ($search !== '') {
        // Each placeholder must be unique: with PDO::ATTR_EMULATE_PREPARES off,
        // MySQL rejects the same named parameter used more than once.
        $where[] = '(company LIKE :search_company
                     OR contact_name LIKE :search_contact
                     OR email LIKE :search_email)';

        $like = '%' . $search . '%';

        $bindings['search_company'] = $like;
        $bindings['search_contact'] = $like;
        $bindings['search_email'] = $like;
    }

    if ($status !== '') {
        $where[] = 'status = :status';
        $bindings['status'] = $status;
    }

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY company';

    return crm_select($sql, $bindings);
}

/**
 * One client by id.
 *
 * @param int $id
 * @return array|null
 */
function crm_find_client($id)
{
    return crm_select_one('SELECT * FROM clients WHERE id = :id', array('id' => (int) $id));
}

/**
 * How many clients there are per status.
 *
 * @return array status => count
 */
function crm_count_by_status()
{
    $counts = array();

    foreach (crm_select('SELECT status, COUNT(*) AS total FROM clients GROUP BY status') as $row) {
        $counts[$row['status']] = (int) $row['total'];
    }

    return $counts;
}
