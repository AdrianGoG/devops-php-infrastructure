# app-user-dashboard

The authenticated dashboard of the DevOps PHP Infrastructure project. It runs on
**VM2 - Application Server 1**, in its own Docker stack, on port **8082**.

It is deliberately small: sign in, look at the state of the infrastructure, manage
your own account. All the data comes from **app-api** over HTTP - the dashboard
stores nothing about the infrastructure itself.

```
app-user-dashboard (PHP 8.2)  ──GET /api/servers, /api/applications, /api/deployments──►  app-api (PHP 7.4)
```

That arrow is the point: two applications on the same server, on two different PHP
versions, talking over HTTP. It is what the containerisation buys you.

| | |
|---|---|
| Framework | Laravel 12 (PHP 8.2) |
| Authentication | **Laravel Breeze**, Blade stack - login, register, password reset, email verification, profile |
| Front-end | Bootstrap 5.3, vendored in `src/public/vendor` - **no Vite, no Tailwind, no npm** |
| Database | MySQL 8.0 (the `mysql` service of the stack) |
| Tests | 31 PHPUnit tests, run by the pipeline before every deployment |

> **Why Bootstrap instead of Breeze's Tailwind.** Breeze scaffolds Tailwind + Vite,
> which means the deployment needs `npm run build` - and `public/build` is in
> `.gitignore`, so a plain file copy would put a site with no CSS on the server.
> The Breeze logic (controllers, requests, routes, tests) is untouched; only the
> views were restyled with Bootstrap served from `public/vendor`.

## Pages

| Route | Auth | What it does |
|---|---|---|
| `/` | - | Redirects to `/dashboard` when signed in, to `/login` otherwise |
| `/login`, `/register` | guest | Breeze authentication |
| `/forgot-password`, `/reset-password/{token}` | guest | Breeze password reset |
| `/dashboard` | auth | The infrastructure state read from app-api |
| `/profile` | auth | Update name/email, change password, delete account |
| `/health` | - | JSON health check, same contract as the other applications |

### The dashboard

Four counters, then three panels:

- **Applications** - all nine PHP applications with their runtime version, port,
  migration status and a direct link to each one.
- **Recent deployments** - what the Jenkins pipeline recorded: build number,
  branch, outcome, timestamp.
- **Servers** - the machines of the estate and how many applications each hosts.

The `legacy` counter is the interesting one during the presentation: it is how many
applications still have to be migrated to PHP 8.3, and it goes down as the
migration progresses.

### When app-api is down

The dashboard degrades instead of failing. `RegistryClient` catches connection
errors, logs a warning and returns an empty list, so the page renders with a
`registry offline` badge and an explanatory banner. That matters because app-api is
*expected* to be down while it is being rebuilt on PHP 8.3 - a 500 page on the
dashboard would be a second incident on top of the first.

## Structure

```
app-user-dashboard/
├── Dockerfile                      # standalone image (php:8.2-fpm)
├── docker-compose.yml              # nginx + php-fpm + mysql, port 8082
├── docker/
│   ├── nginx/default.conf
│   ├── php/Dockerfile
│   └── mysql/init/                 # creates user_dashboard_test on first boot
└── src/
    ├── app/
    │   ├── Http/Controllers/
    │   │   ├── Auth/               # Breeze, untouched
    │   │   ├── DashboardController.php
    │   │   └── ProfileController.php
    │   └── Services/RegistryClient.php    # the HTTP client for app-api
    ├── config/registry.php         # base URL, timeout, cache TTL
    ├── public/
    │   ├── css/app.css             # the theme (shared palette with app-company-website)
    │   └── vendor/bootstrap/       # Bootstrap 5.3, served locally
    ├── resources/views/
    │   ├── auth/                   # login, register, reset, verify - Bootstrap
    │   ├── layouts/                # app, guest, navigation
    │   ├── profile/                # edit + 3 partials
    │   └── dashboard.blade.php
    ├── routes/
    │   ├── web.php                 # dashboard, profile, health
    │   └── auth.php                # Breeze
    └── tests/Feature/              # Auth/*, ProfileTest, DashboardTest
```

## Running with Docker (the way it runs on VM2)

```bash
cd VM2-Application-Server-1/app-user-dashboard

docker compose up -d --build

docker compose exec php composer install --no-dev --optimize-autoloader
docker compose exec php cp -n .env.example .env
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --force
docker compose exec php php artisan db:seed --force
```

Served on <http://localhost:8082> (or `http://192.168.0.170:8082` on VM2). The
MySQL container publishes port **33062** on the host and creates two databases on
first boot: `user_dashboard` and `user_dashboard_test`.

Set `REGISTRY_BASE_URL` in `src/.env` to wherever app-api answers. On VM2 that is
`http://192.168.0.170:8083`.

## Local development

```bash
cd VM2-Application-Server-1/app-user-dashboard
docker compose up -d mysql          # publishes 127.0.0.1:33062

cd src
composer install
cp .env.example .env                # then DB_HOST=127.0.0.1, DB_PORT=33062
php artisan key:generate
php artisan migrate
php artisan db:seed                 # creates the demo account

herd link app-user-dashboard
herd isolate 8.2 --site=app-user-dashboard   # -> http://app-user-dashboard.test
```

### Demo account

The seeder creates one account, and is idempotent so it can run on every deployment:

| Email | Password |
|---|---|
| `admin@devops.test` | `password` |

Change it before anything is exposed outside the lab.

## Tests

```bash
cd src
php artisan test
```

31 tests: the whole Breeze suite (registration, authentication, password reset and
confirmation, password update, email verification, profile) plus `DashboardTest`,
which covers the redirects, the counters, and both failure modes of the registry -
connection refused and an HTTP error response.

The registry is **always faked** in the tests (`Http::fake`), so the suite passes in
the pipeline whether or not app-api happens to be running. The database is the real
MySQL, on the dedicated `user_dashboard_test` schema.
