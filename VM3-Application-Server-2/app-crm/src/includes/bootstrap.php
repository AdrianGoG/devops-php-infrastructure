<?php

/**
 * app-crm - bootstrap.
 *
 * Every page includes this file and nothing else.
 *
 * The order matters. PHP compiles a whole file before executing it, and the
 * legacy constructs in functions.php raise their deprecation notices at compile
 * time. If functions.php were loaded first, those notices would be emitted
 * before config.php had a chance to configure error reporting - and they would
 * end up printed at the top of the page.
 *
 * Loading config.php first means error reporting is already set by the time
 * functions.php is compiled.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
