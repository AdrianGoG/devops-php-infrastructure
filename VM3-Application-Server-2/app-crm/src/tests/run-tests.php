<?php

/**
 * app-crm - test suite.
 *
 * A self contained runner: the application has no Composer and no PHPUnit, so
 * the tests are plain PHP too. It exits with code 0 when everything passes and
 * 1 on the first failure, which is all the Jenkins "Test" stage needs:
 *
 *     php tests/run-tests.php
 *
 * The tests double as the tripwire for the PHP 8 migration - see MIGRATION.md.
 * They read from the database but never write to it.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$passed = 0;
$failed = 0;

/**
 * @param string $description
 * @param mixed $expected
 * @param mixed $actual
 * @return void
 */
function check($description, $expected, $actual)
{
    global $passed, $failed;

    if ($expected === $actual) {
        $passed++;
        echo "  ok    " . $description . PHP_EOL;

        return;
    }

    $failed++;
    echo "  FAIL  " . $description . PHP_EOL;
    echo "        expected: " . var_export($expected, true) . PHP_EOL;
    echo "        actual:   " . var_export($actual, true) . PHP_EOL;
}

/**
 * @param string $description
 * @param bool $condition
 * @return void
 */
function check_true($description, $condition)
{
    check($description, true, (bool) $condition);
}

echo 'app-crm test suite - PHP ' . PHP_VERSION . PHP_EOL . PHP_EOL;

// ---------------------------------------------------------------------------
echo 'helpers' . PHP_EOL;

check('e() escapes HTML', '&lt;b&gt;x&lt;/b&gt;', e('<b>x</b>'));
check('e() escapes quotes', '&quot;quoted&quot;', e('"quoted"'));
check('e() turns null into an empty string', '', e(null));

// LEGACY #1 - crm_clean() calls get_magic_quotes_gpc(), removed in PHP 8.0.
check('crm_clean() trims whitespace', 'Acme', crm_clean('  Acme  '));
check('crm_clean() turns null into an empty string', '', crm_clean(null));

// LEGACY #2 - crm_format_tags() uses the reversed implode() argument order.
check('crm_format_tags() joins and capitalises tags', 'Saas · Enterprise', crm_format_tags('saas,enterprise'));
check('crm_format_tags() ignores empty pieces', 'Retail', crm_format_tags('retail,,'));
check('crm_format_tags() handles null', '', crm_format_tags(null));

// LEGACY #3 - crm_initial() reads a string offset with curly braces, which is a
// parse error in PHP 8.0: if functions.php loaded at all, this already passed.
check('crm_initial() returns the first letter, uppercased', 'N', crm_initial('Nordwind Logistics'));
check('crm_initial() ignores leading whitespace', 'A', crm_initial('  acme'));
check('crm_initial() falls back for an empty name', '?', crm_initial(''));

check('crm_status_label() labels an active client', 'Active client', crm_status_label('active'));
check('crm_status_label() labels a lead', 'Lead', crm_status_label('lead'));
check('crm_status_label() labels a churned client', 'Churned', crm_status_label('churned'));

check('crm_status_class() maps active to the ok pill', 'pill-ok', crm_status_class('active'));
check('crm_status_class() maps churned to the danger pill', 'pill-danger', crm_status_class('churned'));

// ---------------------------------------------------------------------------
echo PHP_EOL . 'validation' . PHP_EOL;

$valid = array(
    'company' => 'Acme SRL',
    'contact_name' => 'Ana Pop',
    'email' => 'ana@acme.example',
    'phone' => '',
    'status' => 'lead',
    'tags' => '',
    'notes' => '',
);

check('a valid client passes validation', array(), crm_validate_client($valid));

$missing = crm_validate_client(array_merge($valid, array('company' => '', 'contact_name' => '')));
check_true('an empty company is rejected', isset($missing['company']));
check_true('an empty contact name is rejected', isset($missing['contact_name']));

$badEmail = crm_validate_client(array_merge($valid, array('email' => 'not-an-email')));
check_true('an invalid email is rejected', isset($badEmail['email']));

$badStatus = crm_validate_client(array_merge($valid, array('status' => 'exploded')));
check_true('an unknown status is rejected', isset($badStatus['status']));

$longCompany = crm_validate_client(array_merge($valid, array('company' => str_repeat('a', 200))));
check_true('a company name over 120 characters is rejected', isset($longCompany['company']));

// ---------------------------------------------------------------------------
echo PHP_EOL . 'database' . PHP_EOL;

try {
    $row = crm_select_one('SELECT COUNT(*) AS total FROM clients');

    check_true('the clients table is reachable', $row !== null);
    check_true('the demo data is present', (int) $row['total'] > 0);

    $clients = crm_find_clients();
    check_true('crm_find_clients() returns rows', count($clients) > 0);

    $first = $clients[0];
    foreach (array('id', 'company', 'contact_name', 'email', 'status', 'created_at') as $column) {
        check_true('a client row has the ' . $column . ' column', array_key_exists($column, $first));
    }

    $active = crm_find_clients('', 'active');
    $onlyActive = true;

    foreach ($active as $client) {
        if ($client['status'] !== 'active') {
            $onlyActive = false;
        }
    }

    check_true('the status filter only returns matching clients', $onlyActive);

    $found = crm_find_clients($first['company']);
    check_true('the search finds a client by company name', count($found) > 0);

    check_true('crm_find_client() returns one client', crm_find_client($first['id']) !== null);
    check('crm_find_client() returns null for an unknown id', null, crm_find_client(999999));

    $counts = crm_count_by_status();
    check_true('crm_count_by_status() returns counters', count($counts) > 0);

    // -----------------------------------------------------------------------
    echo PHP_EOL . 'Authentication' . PHP_EOL;

    crm_execute(crm_users_schema());

    // The suite creates its own account instead of relying on bin/install.php
    // having been run, so it passes on a database it has never seen before.
    if (crm_select_one('SELECT id FROM users LIMIT 1') === null) {
        crm_execute(
            'INSERT INTO users (name, email, password_hash, created_at)
             VALUES (:name, :email, :password_hash, :created_at)',
            array(
                'name' => 'Test Account',
                'email' => 'tests@devops.test',
                'password_hash' => password_hash('a-password-only-the-tests-know', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            )
        );
    }

    $account = crm_select_one('SELECT email FROM users ORDER BY id LIMIT 1');

    check_true('the users table holds an account', $account !== null);

    if ($account !== null) {
        check_true(
            'a wrong password is rejected',
            crm_attempt_login($account['email'], 'definitely-not-the-password') === false
        );
    }

    check_true(
        'an unknown email is rejected',
        crm_attempt_login('nobody@example.invalid', 'password') === false
    );

    check('nobody is signed in during the tests', null, crm_user());
    check_true('crm_logged_in() is false without a session', crm_logged_in() === false);
} catch (Exception $exception) {
    $failed++;
    echo '  FAIL  the database is not reachable' . PHP_EOL;
    echo '        ' . $exception->getMessage() . PHP_EOL;
    echo '        Start it with "docker compose up -d mysql" in the app-crm directory.' . PHP_EOL;
}

// ---------------------------------------------------------------------------
echo PHP_EOL;
echo str_repeat('-', 52) . PHP_EOL;
echo sprintf('%d passed, %d failed', $passed, $failed) . PHP_EOL;

exit($failed === 0 ? 0 : 1);
