# app-api

The **infrastructure registry API** of the DevOps PHP Infrastructure project.
It runs on **VM2 - Application Server 1**, in its own Docker stack, on port
**8083**, and it is the only application of the estate written in **plain PHP 7.4**
with no framework.

It is the integration point of the whole project:

```
Jenkins  ──POST /api/deployments──►  ┌──────────┐  ◄──GET /api/*──  app-user-dashboard
Prometheus ──GET /metrics─────────►  │ app-api  │
python-monitor ──GET /api/health──►  └──────────┘  ──►  MySQL (registry + deployment log)
```

| | |
|---|---|
| Language | Plain PHP 7.4 - own router, no framework, zero runtime dependencies |
| Database | MySQL 8.0 (the `mysql` service of the stack) |
| Autoloading | Composer PSR-4 for `App\`, plus a classmap for the legacy global class |
| Tests | 54 PHPUnit tests, run by the pipeline before every deployment |
| Status | **legacy** - contains six documented PHP 8 incompatibilities, see [MIGRATION.md](MIGRATION.md) |

> **PHP 7.4 is intentional.** This application is the migration target of the
> project: the Ansible playbook raises it to PHP 8.3, several endpoints start
> answering with 500, the Python monitor reports which ones, the source code is
> adapted and Jenkins redeploys it. [MIGRATION.md](MIGRATION.md) documents every
> failure, its symptom and its fix.

## Endpoints

| Method | URI | Auth | Purpose |
|---|---|---|---|
| GET | `/` | - | Self describing index of the API |
| GET | `/api/health` | - | Health check for the Python monitor and the Jenkins smoke test |
| GET | `/api/servers` | - | The four servers of the infrastructure |
| GET | `/api/servers/{key}` | - | One server (`vm1` … `vm4`) with its applications |
| GET | `/api/applications` | - | The nine applications; filters: `?server=`, `?status=`, `?php=` |
| GET | `/api/applications/{name}` | - | One application plus its last five deployments |
| GET | `/api/deployments` | - | The deployment log; filters: `?application=`, `?result=`, `?limit=` |
| POST | `/api/deployments` | `X-API-Key` | Record a deployment - called by Jenkins |
| GET | `/metrics` | - | Prometheus exposition format |

Reads are public because the monitor, Prometheus and the dashboard all need
them. Writes require the `X-API-Key` header, whose value Jenkins holds as a
credential.

### Example: the health contract

```bash
curl -s http://192.168.0.169:8083/api/health
```

```json
{
  "status": "ok",
  "application": "app-api",
  "server": "VM2-Application-Server-1",
  "php": "7.4.33",
  "environment": "production",
  "database": "ok",
  "applications_registered": 9,
  "legacy_timer_initialised": true,
  "checked_at": "2026-07-30T12:01:18+00:00"
}
```

`legacy_timer_initialised` is not decoration: it is the only visible symptom of
incompatibility #4 of the migration dossier, and it flips to `false` on PHP 8.

### Example: what Jenkins posts at the end of the pipeline

```bash
curl -X POST http://192.168.0.169:8083/api/deployments \
  -H "X-API-Key: $API_KEY" \
  -H 'Content-Type: application/json' \
  -d '{
        "application": "app-company-website",
        "build_number": 128,
        "branch": "main",
        "commit_sha": "c3f1a09b8d7e6f5a4b3c2d1e0f9a8b7c6d5e4f3a",
        "result": "success",
        "duration_seconds": 102,
        "notes": "Merged into main after the review."
      }'
```

`result` must be one of `success`, `failed`, `rolled_back`, and the application
has to be registered - otherwise the API answers `422` with the offending fields.

## Structure

```
app-api/
├── Dockerfile                      # standalone image (php:7.4-fpm)
├── docker-compose.yml              # nginx + php-fpm + mysql, port 8083
├── docker/
│   ├── nginx/default.conf          # virtual host, front controller
│   ├── php/Dockerfile              # the php-fpm image used by compose
│   └── mysql/init/                 # creates app_api_test on first boot
├── MIGRATION.md                    # the PHP 7.4 → 8.3 dossier
└── src/
    ├── composer.json
    ├── phpunit.xml
    ├── bin/console                 # migrate | seed | fresh | routes | request
    ├── public/index.php            # front controller
    ├── routes/api.php              # the route table
    ├── app/
    │   ├── Core/                   # Config, Request, Response, Router, Database, Logger, Kernel, Container
    │   ├── Http/Controllers/       # Index, Health, Server, Application, Deployment, Metrics
    │   ├── Http/Middleware/        # ApiKeyGuard
    │   ├── Repositories/           # Server, Application, Deployment
    │   ├── Database/               # Schema, Seeder
    │   ├── Support/                # StringHelper, Sorter, Collection, Validator  <- legacy
    │   └── legacy/LegacyTimer.php  # global class, no namespace                   <- legacy
    ├── storage/logs/               # JSON lines, ingested by Logstash
    └── tests/
        ├── Unit/                   # Router, Config, legacy helpers
        └── Feature/                # every endpoint, end to end
```

### Design notes

- **No runtime dependencies.** The `.env` parser, the router and the PDO wrapper
  are all part of the application. A legacy service with no vendor tree is a
  legacy service whose PHP upgrade cannot be blocked by a third-party package.
- **Responses are values.** Controllers return a `Response`; only `send()` writes
  output. That is what makes all 54 tests run through the real kernel without a
  web server.
- **Logs are JSON lines.** One object per line, so Logstash ingests
  `storage/logs/api.log` with a plain json codec and the entries land in
  Elasticsearch next to the NGINX logs.
- **The legacy code is quarantined.** Every PHP 8 incompatibility lives in its
  own small class under `app/Support` or `app/legacy`, each with a header comment
  pointing at MIGRATION.md. The failures are therefore per-endpoint and
  diagnosable, not a single wall of breakage.

## Running with Docker (the way it runs on VM2)

```bash
cd VM2-Application-Server-1/app-api

docker compose up -d --build

docker compose exec php composer install --no-dev --optimize-autoloader
docker compose exec php cp -n .env.example .env
docker compose exec php php bin/console migrate
docker compose exec php php bin/console seed
```

The API is then served on <http://localhost:8083> (or `http://192.168.0.169:8083`
on VM2). Set a real `API_KEY` in `src/.env` before exposing it.

The MySQL container publishes port **33063** on the host and creates two
databases on first boot: `app_api` for the application and `app_api_test` for the
test suite (see `docker/mysql/init/01-test-database.sql`).

## Local development

The application needs a MySQL it can reach. The simplest option is the one from
this very stack:

```bash
cd VM2-Application-Server-1/app-api
docker compose up -d mysql          # publishes 127.0.0.1:33063

cd src
composer install
cp .env.example .env                # then set DB_HOST=127.0.0.1 and DB_PORT=33063
php bin/console fresh               # create the tables and load the inventory
```

With Laravel Herd, serve it on PHP 7.4:

```bash
herd link app-api
herd isolate 7.4 --site=app-api     # -> http://app-api.test
```

`bin/console request` runs a request through the kernel without any web server -
handy for a quick check, or from inside a pipeline stage:

```bash
php bin/console request GET /api/health
php bin/console routes
```

## Tests

```bash
cd src
php vendor/bin/phpunit
```

The suite talks to the real MySQL (database `app_api_test`, recreated and
reseeded before every test), so nothing is proven against a different engine than
the one running in production. Point it elsewhere with `DB_HOST`, `DB_PORT`,
`DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` - they override `phpunit.xml`.

- **Unit** - the router (matching, parameters, 404/405), the config loader, and
  characterisation tests that pin the behaviour of every legacy helper.
- **Feature** - all nine endpoints end to end: payload shape, filters,
  validation, the API key, the Prometheus format and the error responses.

Five of the six documented incompatibilities make this suite fail on PHP 8.3,
which is how the pipeline stops a broken upgrade before it reaches a server. The
sixth is invisible to the tests on purpose - see the last section of
[MIGRATION.md](MIGRATION.md).
