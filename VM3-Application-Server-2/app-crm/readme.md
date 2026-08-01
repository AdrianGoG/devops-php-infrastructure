# app-crm

A small client register - the first application of **VM3 - Application Server 2**,
served on port **8081**.

It is deliberately the plainest application of the estate: **PHP 7.4, no
framework, no Composer, no vendor directory**. Every page is a real file in
`public/`, exactly like the PHP applications this project is meant to model.

| | |
|---|---|
| Language | Plain PHP 7.4 - no framework, no dependencies at all |
| Database | MySQL 8.0 (the `mysql` service of the stack) |
| Front-end | Bootstrap 5.3 vendored in `src/public/assets` - no build step |
| Tests | 36 checks, plain PHP: `php tests/run-tests.php` |
| Status | **legacy** - three documented PHP 8 incompatibilities, see [MIGRATION.md](MIGRATION.md) |

> **PHP 7.4 is intentional.** This is one of the migration targets of the project.
> After the Ansible upgrade the whole application answers HTTP 500 until the
> source code is adapted. [MIGRATION.md](MIGRATION.md) documents each failure and
> its fix.

## What it does

| Page | Method | What it does |
|---|---|---|
| `/login.php` | GET, POST | Sign in - the only page open to an anonymous visitor |
| `/logout.php` | POST | Sign out - POST only, CSRF protected |
| `/index.php` | GET | Client list, with a text search and a status filter |
| `/create.php` | GET, POST | Add a client |
| `/edit.php?id=N` | GET, POST | Edit a client (404 when the id does not exist) |
| `/delete.php` | POST | Delete a client - POST only, CSRF protected |
| `/health.php` | GET | JSON health check, same contract as the other applications |

### Signing in

The client register is not public. Every page above calls `crm_require_login()`
before it prints anything, and sends an anonymous visitor to `/login.php`.

| | |
|---|---|
| Email | `admin@devops.test` |
| Password | `password` |

The account is created by `bin/install.php`, which the deployment playbook runs
on every deployment. The script is idempotent in the strict sense: if the account
already exists it is left exactly as it is, password included, so changing the
password on the server survives the next deployment. Set `CRM_ADMIN_EMAIL`,
`CRM_ADMIN_PASSWORD` and `CRM_ADMIN_NAME` in `.env` **before** the first
deployment to start from different credentials.

Three details worth pointing at, because they are the ones usually missed in a
hand-rolled login:

- **`/health.php` stays open.** The Python monitor, Prometheus and the Jenkins
  smoke test have to reach it without credentials. It exposes a status, a PHP
  version and a row count - no client data. Putting the guard in `bootstrap.php`
  instead of in each page would have locked the monitoring out of the whole
  estate, and the failure would have looked like an outage.
- **A wrong email and a wrong password give the same answer**, and take the same
  time: an unknown address is still verified against a throwaway hash, so the
  page cannot be used to find out which addresses have an account.
- **The session id is regenerated on login**, so an id planted before the login
  cannot be reused after it. Signing out empties the session, drops its cookie
  and destroys it server side.

Passwords are stored with `password_hash()` using the default algorithm, and
checked with `password_verify()`. No library, no framework - the same rule as the
rest of this application.

A client has a company, a contact name, an email, an optional phone, a status
(`lead`, `active`, `churned`), comma separated tags and free notes.

```bash
curl -s http://192.168.0.105:8081/health.php
```

```json
{
  "status": "ok",
  "application": "app-crm",
  "server": "VM3-Application-Server-2",
  "php": "7.4.33",
  "environment": "production",
  "database": "ok",
  "clients": 6,
  "checked_at": "2026-07-30T12:54:57+00:00"
}
```

## Structure

```
app-crm/
├── Dockerfile                      # standalone image (php:7.4-fpm)
├── docker-compose.yml              # nginx + php-fpm + mysql, port 8081
├── docker/
│   ├── nginx/default.conf
│   ├── php/Dockerfile
│   └── mysql/init/01-schema.sql    # the schema AND the demo data
├── MIGRATION.md                    # the PHP 7.4 → 8.3 dossier
└── src/
    ├── bin/install.php             # users table + the sign in account
    ├── public/                     # every page is a real file
    │   ├── login.php  logout.php   # open to an anonymous visitor
    │   ├── index.php  create.php  edit.php  delete.php   ← behind the login
    │   ├── health.php              # open on purpose, for the monitoring
    │   └── assets/{css/app.css, vendor/bootstrap/}
    ├── includes/
    │   ├── bootstrap.php           # the only file the pages include
    │   ├── config.php              # .env parser, error reporting, session
    │   ├── db.php                  # PDO + three query helpers
    │   ├── functions.php           # helpers  ← the three legacy constructs
    │   ├── auth.php                # sessions, password_verify, the guard
    │   ├── client-form.php         # form shared by create and edit
    │   └── layout-top.php / layout-bottom.php
    └── tests/run-tests.php         # the test suite
```

### Design notes

- **No migration tool.** The schema *is* `docker/mysql/init/01-schema.sql`, loaded
  by MySQL on first boot. For an existing database, apply it by hand.
- **Old fashioned in style, not insecure.** Every query uses PDO prepared
  statements, every output goes through `e()` (`htmlspecialchars`), the delete
  action is POST only and all forms carry a CSRF token. Legacy means old PHP
  constructs here, not missing basics.
- **`bootstrap.php` exists for a reason.** PHP compiles a file completely before
  executing it, so the deprecation notices raised by the legacy constructs in
  `functions.php` are emitted at compile time. Loading `config.php` first means
  error reporting is already configured by then - otherwise those notices get
  printed at the top of the page.
- **The session starts in `config.php`**, before any output. Starting it later
  would make the CSRF token depend on `output_buffering` being enabled.

## Running with Docker (the way it runs on VM3)

```bash
cd VM3-Application-Server-2/app-crm

docker compose up -d --build
docker compose exec php cp -n .env.example .env
```

That is the whole setup: no `composer install`, no migrations. MySQL creates the
schema and the demo data on its first boot.

Served on <http://localhost:8081> (or `http://192.168.0.105:8081` on VM3). The
MySQL container publishes port **33064** on the host.

## Local development

```bash
cd VM3-Application-Server-2/app-crm
docker compose up -d mysql          # publishes 127.0.0.1:33064

cd src
cp .env.example .env                # then DB_HOST=127.0.0.1, DB_PORT=33064

herd link app-crm
herd isolate 7.4 --site=app-crm     # -> http://app-crm.test
```

## Tests

```bash
cd src
php tests/run-tests.php
```

36 checks over the helpers, the validation rules and the database layer. Plain
PHP with two assertion functions - no PHPUnit, because the application has no
Composer and adding one just for the tests would give the PHP upgrade a
dependency it does not need.

It exits `0` on success and `1` on failure, so the Jenkins stage is one line:

```groovy
stage('Test') {
    steps { sh 'php src/tests/run-tests.php' }
}
```

The suite reads from the database and never writes to it, so it is safe to run
against a live instance. On PHP 7.4 it also prints the three deprecation notices
to stderr - PHP telling you in advance what the upgrade will break.
