# app-api - PHP 7.4 → 8.3 migration dossier

`app-api` is the legacy service of the infrastructure. It runs correctly on
**PHP 7.4** and contains **six deliberate incompatibilities** with PHP 8, so the
automated upgrade performed by Ansible produces real, reproducible failures that
have to be fixed in the source code and redeployed through Jenkins.

This file is the complete map of those failures: what breaks, where, why, what
the visible symptom is, and what the fix looks like.

---

## 1. How to reproduce the upgrade

```bash
cd VM2-Application-Server-1/app-api

# Before: capture the healthy state
python3 ../../python-monitor/infra_check.py --report before.json

# The upgrade Ansible performs: bump the image and rebuild
sed -i 's/FROM php:7.4-fpm/FROM php:8.3-fpm/' docker/php/Dockerfile
docker compose up -d --build php

# After: the same report, now with failures
python3 ../../python-monitor/infra_check.py --compare before.json
```

The tests are the earlier tripwire - they fail before anything is deployed:

```bash
cd src && php vendor/bin/phpunit
```

---

## 2. The six incompatibilities

| # | File | Construct | PHP 7.4 | PHP 8.3 | Symptom |
|---|---|---|---|---|---|
| 1 | `src/app/Support/StringHelper.php:40` | `$part{0}` curly string offset | deprecation notice | **parse error** | fatal, uncatchable: `/api/applications*` → 500 with an empty body |
| 2 | `src/app/Support/Sorter.php:31` | `create_function()` | deprecation notice | **removed** | `Error: Call to undefined function` → `GET /api/servers` → 500 |
| 3 | `src/app/Support/Collection.php:30` | `each()` | deprecation notice | **removed** | `Error: Call to undefined function` → `GET /api/deployments` → 500 |
| 4 | `src/app/legacy/LegacyTimer.php:31` | PHP 4 style constructor | works | **no longer a constructor** | *silent*: uptime metric reads 0, `legacy_timer_initialised` flips to false |
| 5 | `src/app/Core/Database.php` | relies on the implicit `PDO::ERRMODE_SILENT` | failing query returns `false` | **default is `ERRMODE_EXCEPTION`** | uncaught `PDOException` → `GET /metrics` → 500 |
| 6 | `src/app/Support/Validator.php:113` | `trim(null)` / `strlen(null)` | accepted | **deprecated (8.1+)** | *silent*: deprecation notices flood the log on every incomplete payload |

Note the spread on purpose: two of the six break nothing visibly (#4 and #6).
Those are the ones a report based only on HTTP status codes would miss, which is
why the health endpoint and the Prometheus metrics both expose the state of the
legacy timer explicitly.

---

## 3. Endpoint status before and after the upgrade

| Endpoint | PHP 7.4 | PHP 8.3, before the fixes | Cause |
|---|---|---|---|
| `GET /` | 200 | **200** | no legacy dependency |
| `GET /api/health` | 200 | **200** *(degraded payload)* | #4 - `legacy_timer_initialised: false` |
| `GET /api/servers` | 200 | **500** | #2 |
| `GET /api/servers/{key}` | 200 | **200** | does not sort |
| `GET /api/applications` | 200 | **500** | #1 |
| `GET /api/applications/{name}` | 200 | **500** | #1 |
| `GET /api/deployments` | 200 | **500** | #3 |
| `POST /api/deployments` | 201 | **201** *(noisy log)* | #6 |
| `GET /metrics` | 200 | **500** | #5 |

A partial outage, not a total one: the service still answers, which is exactly
the situation that makes a per-endpoint monitoring report worth having.

---

## 4. The fixes, one by one

### #1 - curly brace string offsets

`src/app/Support/StringHelper.php`

```diff
-            $code .= strtoupper($part{0});
+            $code .= strtoupper($part[0]);
```

Square brackets have been valid since PHP 4, so the fixed code runs on both
versions.

### #2 - create_function()

`src/app/Support/Sorter.php`

```diff
-        $comparator = create_function(
-            '$a, $b',
-            'return strcmp((string) $a["' . $field . '"], (string) $b["' . $field . '"]);'
-        );
-
-        usort($rows, $comparator);
+        usort($rows, function (array $a, array $b) use ($field) {
+            return strcmp((string) $a[$field], (string) $b[$field]);
+        });
```

A closure is not only supported, it also removes the string-eval: the field name
no longer gets interpolated into executable code.

### #3 - each()

`src/app/Support/Collection.php`

```diff
-        while (list($index, $row) = each($rows)) {
-            unset($index);
-
+        foreach ($rows as $row) {
             if (!isset($row[$field])) {
                 continue;
             }
             ...
         }
```

`each()` also relied on the array's internal pointer, so the original code could
only ever be iterated once per array. `foreach` fixes that bug too.

### #4 - PHP 4 style constructor

`src/app/legacy/LegacyTimer.php`

```diff
-    public function LegacyTimer()
+    public function __construct()
     {
         $this->startedAt = microtime(true);
     }
```

While the file is being touched, it can also be moved under `App\Support` and
autoloaded through PSR-4 instead of the Composer classmap - but only *after* the
constructor is renamed, because a namespaced class never treats a same-name
method as a constructor.

### #5 - the implicit PDO error mode

`src/app/Core/Database.php` - make the intent explicit rather than depend on a
default that changed:

```diff
         $this->pdo = new PDO($dsn, $user, $password, [
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false,
+            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_TIMEOUT => 5,
         ]);
```

and stop querying a table that no longer exists:

```diff
     public function legacyDeploymentStats(): ?array
     {
-        $statement = $this->pdo->query('SELECT total, failures FROM deployment_stats WHERE id = 1');
-
-        if ($statement === false) {
-            return null;
-        }
-
-        $row = $statement->fetch();
-
-        return is_array($row) ? $row : null;
+        // The deployment_stats table was dropped; the counters are derived
+        // from the deployments table instead.
+        return null;
     }
```

With `ERRMODE_EXCEPTION` on, the `if ($statement === false)` guards in the rest
of the class become dead code and should be replaced by `try`/`catch` where a
failure genuinely has to be tolerated.

### #6 - null passed to internal string functions

`src/app/Support/Validator.php`

```diff
     private static function isBlank($value)
     {
         if (is_array($value)) {
             return count($value) === 0;
         }
 
-        return strlen(trim($value)) === 0;
+        if ($value === null) {
+            return true;
+        }
+
+        return strlen(trim((string) $value)) === 0;
     }
```

---

## 5. Declaring the migration done

```diff
 # src/composer.json
-        "php": "^7.2",
+        "php": "^8.3",
```

```diff
 # docker/php/Dockerfile
-FROM php:7.4-fpm
+FROM php:8.3-fpm
```

Then, in order:

1. `cd src && composer update --lock` - refresh the platform requirement.
2. `php vendor/bin/phpunit` - all 54 tests must pass on the new version.
3. `git commit` the fixes on the `development` branch, one commit per
   incompatibility so the history documents the migration.
4. `git push` - the GitHub webhook triggers Jenkins.
5. The pipeline rebuilds the image on 8.3, redeploys and runs the smoke test.
6. `python3 infra_check.py --compare before.json` - every endpoint back to 200.
7. Update the application status in the registry:

```bash
curl -X POST http://192.168.0.170:8083/api/deployments \
  -H "X-API-Key: $API_KEY" \
  -H 'Content-Type: application/json' \
  -d '{"application":"app-api","result":"success","branch":"development","notes":"Migrated to PHP 8.3"}'
```

---

## 6. Why the tests catch five of the six problems

| # | Test that fails on PHP 8.3 |
|---|---|
| 1 | `LegacySupportTest::test_the_string_helper_builds_a_short_code` - and every test that loads the file |
| 2 | `LegacySupportTest::test_the_sorter_orders_rows_by_a_field`, `ServerEndpointTest` |
| 3 | `LegacySupportTest::test_the_collection_summarises_values`, `DeploymentEndpointTest` |
| 4 | `LegacySupportTest::test_the_legacy_timer_initialises_itself`, `HealthEndpointTest`, `MetricsEndpointTest` |
| 5 | `MetricsEndpointTest` - all four cases |
| 6 | none - a deprecation is not a failure; it only shows up in the log |

Incompatibility #6 is deliberately invisible to the suite. It is the reminder
that a green pipeline is not the same thing as a clean upgrade, and that the log
centralised in the ELK stack is where the rest of the truth lives.
