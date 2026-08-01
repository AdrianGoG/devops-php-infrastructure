# app-ticket-system

A support ticket queue - the third application of **VM3 - Application Server 2**,
served on port **8083**.

One model, one controller, three views: list the queue, open a ticket, create it,
edit it, close it, delete it.

| | |
|---|---|
| Framework | **Laravel 10** on **PHP 8.1** |
| Database | MySQL 8.0 (the `mysql` service of the stack) |
| Front-end | Bootstrap 5.3 vendored in `src/public/vendor` - no Vite, no Tailwind, no npm |
| Tests | 19 PHPUnit tests |
| Status | **pending** - compatible with PHP 8.3, only waiting to be aligned |

> **This is the easy migration, and that is the point.** VM3 holds one example of
> each kind of upgrade:
>
> | Application | Blocked by | Work needed to reach PHP 8.3 |
> |---|---|---|
> | `app-crm` | its own source code | three one-line diffs |
> | `app-inventory` | its dependencies (Laravel 9) | a staged framework upgrade |
> | `app-ticket-system` | **nothing** | change one line in the Dockerfile, run the tests |
>
> Laravel 10 supports PHP 8.3, so this application moves by editing
> `docker/php/Dockerfile` from `php:8.1-fpm` to `php:8.3-fpm`, rebuilding, and
> letting the 19 tests confirm it. Having one application in this state makes the
> other two measurable.

## What it does

| Route | Method | What it does |
|---|---|---|
| `/` | GET | Redirects to the queue |
| `/tickets` | GET | The queue; `?q=` searches reference/subject/requester, `?status=`, `?priority=` |
| `/tickets/create` | GET | New ticket form |
| `/tickets` | POST | Store a ticket (the reference is assigned automatically) |
| `/tickets/{id}` | GET | Ticket detail page |
| `/tickets/{id}/edit` | GET | Edit form |
| `/tickets/{id}` | PUT | Update |
| `/tickets/{id}/close` | PATCH | Close in one click, straight from the queue |
| `/tickets/{id}` | DELETE | Delete |
| `/health` | GET | JSON health check, same contract as the other applications |

A ticket has an auto-assigned reference (`TCK-0001`), a subject, a description, a
requester email, a priority (`low`, `normal`, `high`, `urgent`), a status (`open`,
`in_progress`, `resolved`, `closed`) and an optional assignee.

The queue is ordered by priority first - urgent at the top - through a
`FIELD(priority, ...)` clause, then newest first.

```bash
curl -s http://192.168.0.105:8083/health
```

```json
{
  "status": "ok",
  "application": "app-ticket-system",
  "server": "VM3-Application-Server-2",
  "php": "8.1.34",
  "laravel": "10.50.2",
  "environment": "production",
  "database": "ok",
  "tickets": 5,
  "unresolved": 3,
  "checked_at": "2026-07-30T13:14:07+00:00"
}
```

## Structure

```
app-ticket-system/
├── Dockerfile                      # standalone image (php:8.1-fpm)
├── docker-compose.yml              # nginx + php-fpm + mysql, port 8083
├── docker/
│   ├── nginx/default.conf
│   ├── php/Dockerfile
│   └── mysql/init/                 # creates tickets_test on first boot
└── src/
    ├── app/
    │   ├── Models/Ticket.php               # statuses, priorities, nextReference()
    │   ├── Http/Controllers/TicketController.php
    │   └── Http/Requests/TicketRequest.php
    ├── database/
    │   ├── migrations/…create_tickets_table.php
    │   ├── factories/TicketFactory.php     # open() / closed() / urgent() states
    │   └── seeders/DatabaseSeeder.php      # 5 tickets, idempotent
    ├── public/{css/app.css, vendor/bootstrap/}
    ├── resources/views/
    │   ├── layouts/app.blade.php
    │   └── tickets/{index,form,show}.blade.php
    ├── routes/web.php
    └── tests/Feature/{TicketTest,HealthTest}.php
```

### Design notes

- **`resolved_at` is not fillable.** A resolution timestamp must never arrive from
  a form, so it is assigned in the controller. That also means `update()` silently
  ignores it - a trap worth knowing about, and the reason `close()` assigns the
  property directly instead of going through mass assignment. A test covers it.
- **Reopening clears the timestamp.** Moving a ticket back to `open` or
  `in_progress` sets `resolved_at` to null, so the field never claims a ticket was
  resolved when it is not.
- **No front-end build step.** Laravel 10 scaffolds Vite; it was removed and
  Bootstrap is served from `public/vendor`, so a deployment only copies files.

### One note on installing Laravel 10

Composer refuses to install Laravel 10 out of the box: the framework is out of
support and all its releases carry open security advisories. The exception is
recorded in `src/composer.json`, in this project only:

```json
"config": {
    "audit": { "block-insecure": false }
}
```

That is acceptable for a lab that deliberately models a legacy estate, and it is
the reason these applications must stay on the lab network. It is also the
argument for actually finishing the upgrades rather than only upgrading PHP -
see [app-inventory/MIGRATION.md](../app-inventory/MIGRATION.md).

## Running with Docker (the way it runs on VM3)

```bash
cd VM3-Application-Server-2/app-ticket-system

docker compose up -d --build

docker compose exec php composer install --no-dev --optimize-autoloader
docker compose exec php cp -n .env.example .env
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --force
docker compose exec php php artisan db:seed --force
```

Served on <http://localhost:8083> (or `http://192.168.0.105:8083` on VM3). The
MySQL container publishes port **33066** on the host and creates two databases on
first boot: `tickets` and `tickets_test`.

## Local development

```bash
cd VM3-Application-Server-2/app-ticket-system
docker compose up -d mysql          # publishes 127.0.0.1:33066

cd src
composer install
cp .env.example .env                # then DB_HOST=127.0.0.1, DB_PORT=33066
php artisan key:generate
php artisan migrate
php artisan db:seed

herd link app-ticket-system
herd isolate 8.1 --site=app-ticket-system   # -> http://app-ticket-system.test
```

## Tests

```bash
cd src
php artisan test
```

19 tests: the queue, the search, both filters, an unknown filter value being
ignored, the create flow and the incrementing reference, all validation rules, the
detail page, the update flow, the resolution timestamp being stamped on resolve
and cleared on reopen, the one click close, the delete and the health contract.

They run against the real MySQL, on the dedicated `tickets_test` database created
by the container's init script, so nothing touches application data.
