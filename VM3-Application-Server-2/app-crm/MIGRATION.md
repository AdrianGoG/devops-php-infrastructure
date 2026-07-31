# app-crm - PHP 7.4 → 8.3 migration dossier

`app-crm` runs correctly on **PHP 7.4** and contains **three deliberate
incompatibilities** with PHP 8. All three live in one file,
`src/includes/functions.php`, and they surface **one at a time** - which is
exactly how a real migration feels.

---

## 1. How to reproduce

```bash
cd VM3-Application-Server-2/app-crm

# Before: everything green
php src/tests/run-tests.php          # 36 passed, exit 0

# The upgrade Ansible performs
sed -i 's/FROM php:7.4-fpm/FROM php:8.3-fpm/' docker/php/Dockerfile
docker compose up -d --build php

# After: the application is down
curl -i http://localhost:8081/index.php
```

---

## 2. The three incompatibilities

| # | Function | Construct | PHP 7.4 | PHP 8.3 |
|---|---|---|---|---|
| 1 | `crm_initial()` | `$company{0}` curly string offset | deprecation | **parse error** - the file does not load at all |
| 2 | `crm_clean()` | `get_magic_quotes_gpc()` | deprecation | **removed** - `Error: Call to undefined function` |
| 3 | `crm_format_tags()` | `implode($array, $glue)` reversed arguments | deprecation | **removed** - `TypeError` |

### Why they surface one at a time

Incompatibility #1 is a **parse error**, so on PHP 8.3 `functions.php` never
loads and *every* page answers HTTP 500 with an empty body. Nothing else can even
be reached yet.

Fix #1, reload, and the list page now renders far enough to hit #2 or #3. That is
the honest shape of a migration: you fix, you re-run, the next error appears. The
test suite shortens the loop because it finds all three in one run once the file
parses.

### Endpoint status after the upgrade

| Page | PHP 7.4 | PHP 8.3, before the fixes |
|---|---|---|
| `/index.php` | 200 | **500** (#1, then #2 and #3) |
| `/create.php`, `/edit.php` | 200 | **500** (#1, then #2) |
| `/delete.php` | 302 | **500** (#1, then #2) |
| `/health.php` | 200 | **500** (#1) |

Unlike `app-api`, this application goes down completely: all its code paths share
the same helper file. That contrast is worth showing - how blast radius depends
on how the code is structured, not on how many problems there are.

---

## 3. The fixes

### #1 - curly brace string offset

`src/includes/functions.php`, `crm_initial()`

```diff
-    return strtoupper($company{0});
+    return strtoupper($company[0]);
```

### #2 - get_magic_quotes_gpc()

`src/includes/functions.php`, `crm_clean()`

```diff
-    // LEGACY: removed in PHP 8.0.
-    if (get_magic_quotes_gpc()) {
-        $value = stripslashes($value);
-    }
-
     return trim($value);
```

The function has returned `false` on every supported PHP version since 5.4, so
the `stripslashes()` branch was already dead code. Deleting it is the fix - not
replacing it with something else.

### #3 - reversed implode() arguments

`src/includes/functions.php`, `crm_format_tags()`

```diff
-    // LEGACY: reversed argument order, removed in PHP 8.0.
-    return implode($pieces, ' · ');
+    return implode(' · ', $pieces);
```

---

## 4. Declaring the migration done

```diff
 # docker/php/Dockerfile
-FROM php:7.4-fpm
+FROM php:8.3-fpm
```

Then, in order:

1. `php src/tests/run-tests.php` - 36 tests, all green on 8.3.
2. `git commit` on `development`, one commit per incompatibility, so the history
   documents the migration.
3. `git push` - the webhook triggers Jenkins.
4. The pipeline rebuilds the image, redeploys and runs the smoke test against
   `/health.php`.
5. Update the registry so the estate reflects reality:

```bash
curl -X POST http://192.168.0.169:8083/api/deployments \
  -H "X-API-Key: $API_KEY" -H 'Content-Type: application/json' \
  -d '{"application":"app-crm","result":"success","branch":"development","notes":"Migrated to PHP 8.3"}'
```

---

## 5. All three are caught by the test suite

| # | Test that fails on PHP 8.3 |
|---|---|
| 1 | every test - the suite cannot even load `functions.php` |
| 2 | `crm_clean() trims whitespace`, `crm_clean() turns null into an empty string` |
| 3 | the three `crm_format_tags()` cases |

`php tests/run-tests.php` exits with code 1, which is what makes it usable
directly as a Jenkins stage - no PHPUnit, no Composer, no vendor directory.

Note that on PHP 7.4 the same command prints the deprecation notices to stderr.
PHP is telling you in advance exactly what will break; the notices are the
starting point of this document.
