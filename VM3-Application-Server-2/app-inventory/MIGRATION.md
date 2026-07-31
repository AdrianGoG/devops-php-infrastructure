# app-inventory - PHP 8.0 → 8.3 migration dossier

This application is the **other kind of legacy**. `app-crm` and `app-api` are
blocked by their own source code - a few removed constructs, fixed with a few
diffs. `app-inventory` has clean source code and is blocked by its
**dependencies**: it runs **Laravel 9**, a framework version that was never
released with PHP 8.3 support.

That distinction is the point of this file. Both cases produce the same HTTP 500,
and they need completely different work.

---

## 1. Why the source code is not the problem

Search the application for removed constructs and you will find none:

```bash
grep -rn 'get_magic_quotes_gpc\|create_function\|each(\|{0}' src/app src/routes src/resources
# no matches
```

The blocker is one line in `src/composer.json`:

```json
"laravel/framework": "^9.0"
```

Laravel 9 supports PHP 8.0, 8.1 and 8.2. It reached end of life in February 2024
and never gained PHP 8.3 support. Running it on 8.3 produces deprecation storms
and failures inside the framework itself, in code you do not own and must not
patch.

Composer states it plainly:

```bash
cd src && php -r "require 'vendor/autoload.php';" # fine on 8.0
composer why-not php 8.3
```

---

## 2. What the upgrade actually looks like

| Step | app-crm (source blocked) | app-inventory (dependency blocked) |
|---|---|---|
| Find the problem | `grep` for removed functions | `composer why-not php 8.3` |
| Fix | three one-line diffs | upgrade the framework across major versions |
| Risk | contained, per function | breaking changes in every upgraded package |
| Time | minutes | the long pole of the migration |

Laravel cannot jump from 9 to 12 in one step. The supported path is one major
version at a time, running the test suite between each:

```bash
cd src

# 9 → 10   (PHP >= 8.1)
composer require laravel/framework:^10.0 --update-with-all-dependencies
php artisan test

# 10 → 11  (PHP >= 8.2, new application skeleton)
composer require laravel/framework:^11.0 --update-with-all-dependencies
php artisan test

# 11 → 12  (PHP >= 8.2)
composer require laravel/framework:^12.0 --update-with-all-dependencies
php artisan test
```

The PHP version in the Dockerfile has to move **with** each step, not before it:
Laravel 10 needs at least 8.1, Laravel 11 at least 8.2. Bumping PHP first is what
produces the 500s.

### The 10 → 11 step is the expensive one

Laravel 11 replaced the application skeleton: `app/Http/Kernel.php`,
`app/Console/Kernel.php` and `app/Exceptions/Handler.php` are gone, and their
configuration moved into `bootstrap/app.php`. That is a manual restructuring, not
a `composer require`.

For this project the honest and cheaper option is to **stop at Laravel 10 on PHP
8.1**, which is exactly what `app-ticket-system` already runs. The estate then
has one uniform legacy-but-supported tier instead of one application halfway
through a skeleton rewrite.

---

## 3. A second, real blocker: security advisories

Composer refuses to install Laravel 10 at all right now:

```
Root composer.json requires laravel/framework ^10.0, found laravel/framework[v10.0.0, ..., 10.50.2]
but these were not loaded, because they are affected by security advisories
```

Laravel 9 and 10 are both out of support, so their open advisories will never be
patched. `app-ticket-system` carries the exception that makes the install
possible, recorded in its own `composer.json` rather than in a global setting:

```json
"config": {
    "audit": { "block-insecure": false }
}
```

**This is a deliberate choice for a lab.** Two consequences worth stating out
loud at a presentation:

- these applications must never be exposed outside the lab network;
- "upgrade PHP" and "upgrade the framework" are different projects, and the
  second one is the one that actually protects you.

---

## 4. Declaring the migration done

1. Upgrade the framework one major version at a time, tests green between each.
2. Raise `docker/php/Dockerfile` to the PHP version the new framework requires.
3. `php artisan test` - all 15 tests green on the new stack.
4. `git commit` one commit per major version, so the history shows the path.
5. `git push` - the webhook triggers Jenkins, which rebuilds and redeploys.
6. Update the registry:

```bash
curl -X POST http://192.168.0.169:8083/api/deployments \
  -H "X-API-Key: $API_KEY" -H 'Content-Type: application/json' \
  -d '{"application":"app-inventory","result":"success","branch":"development","notes":"Laravel 9 → 10, PHP 8.0 → 8.1"}'
```

---

## 5. What the test suite can and cannot tell you

The 15 tests cover the stock list, the search, the low stock filter, validation
and the health contract. They will catch a framework upgrade that breaks the
application's behaviour - a changed validation message, a route that no longer
resolves, a cast that behaves differently.

They will **not** tell you that Laravel 9 is unsupported. No test fails because a
framework is out of support; that information only exists in
`composer why-not`, `composer audit` and the release calendar. Keeping an eye on
those is part of running the infrastructure, not part of running the tests.
