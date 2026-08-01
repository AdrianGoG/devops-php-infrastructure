<?php

/**
 * app-crm - authentication.
 *
 * Sessions and password_hash(), no library and no framework - the same rule as
 * the rest of this application.
 *
 * Every page that shows or changes a client calls crm_require_login() before it
 * prints anything. health.php deliberately does not: the Python monitor,
 * Prometheus and the Jenkins smoke test have to reach it without credentials,
 * and it exposes no client data.
 */

/**
 * The users table. Created by bin/install.php, which the deployment runs.
 *
 * @return string
 */
function crm_users_schema()
{
    return 'CREATE TABLE IF NOT EXISTS users ('
        . 'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, '
        . 'name VARCHAR(120) NOT NULL, '
        . 'email VARCHAR(180) NOT NULL, '
        . 'password_hash VARCHAR(255) NOT NULL, '
        . 'created_at DATETIME NOT NULL, '
        . 'UNIQUE KEY users_email_unique (email)'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
}

/**
 * The user behind the current session, or null when nobody is signed in.
 *
 * @param bool $reset forget the cached row - used right after a login
 * @return array|null
 */
function crm_user($reset = false)
{
    static $user = null;
    static $loaded = false;

    if ($reset) {
        $user = null;
        $loaded = false;

        return null;
    }

    if ($loaded) {
        return $user;
    }

    $loaded = true;

    if (empty($_SESSION['crm_user_id'])) {
        return null;
    }

    $user = crm_select_one(
        'SELECT id, name, email FROM users WHERE id = :id',
        array('id' => (int) $_SESSION['crm_user_id'])
    );

    return $user;
}

/**
 * @return bool
 */
function crm_logged_in()
{
    return crm_user() !== null;
}

/**
 * Send anyone who is not signed in to the login page.
 *
 * Has to be called before a single byte is printed, otherwise PHP cannot send
 * the Location header.
 *
 * @return void
 */
function crm_require_login()
{
    if (crm_logged_in()) {
        return;
    }

    header('Location: /login.php');
    exit;
}

/**
 * Check an email and a password against the users table.
 *
 * @param string $email
 * @param string $password
 * @return bool
 */
function crm_attempt_login($email, $password)
{
    $user = crm_select_one(
        'SELECT id, password_hash FROM users WHERE email = :email',
        array('email' => $email)
    );

    // An unknown address is verified against a throwaway hash anyway, so a
    // wrong email and a wrong password take the same time to answer and cannot
    // be told apart by measuring the response.
    $hash = $user === null
        ? '$2y$10$C6UzMDM.H6dfI/f/IKcEe.7VjaXcuGCLLdaXTWLcFH8ZgY0Yo6Uwq'
        : $user['password_hash'];

    $correct = password_verify($password, $hash);

    if ($user === null || !$correct) {
        return false;
    }

    // A fresh session id after a successful login, so an id planted before the
    // login cannot be reused after it.
    session_regenerate_id(true);

    $_SESSION['crm_user_id'] = (int) $user['id'];

    crm_user(true);

    return true;
}

/**
 * Empty the session and drop its cookie.
 *
 * @return void
 */
function crm_logout()
{
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    crm_user(true);
}
