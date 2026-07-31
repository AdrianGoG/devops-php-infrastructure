# app-company-website

The presentation site of the **DevOps PHP Infrastructure** project (ITSchool final project, Theme 2).
It is one of the nine PHP applications of the infrastructure and runs on **VM2 - Application Server 1**,
in its own Docker stack, on port **8081**.

The site describes the project itself: the four servers, the nine applications and their PHP versions,
the stages of the Jenkins pipeline, the technologies used, and the monitoring and logging solution.

| | |
|---|---|
| Framework | Laravel 13 (Blade views only, no front-end build step) |
| PHP | `docker/php/Dockerfile` (currently 8.2 - the target of the Ansible upgrade is 8.3) |
| Front-end | Bootstrap 5.3, vendored in `src/public/vendor/bootstrap` + custom theme in `src/public/css/site.css` |
| Database | the `mysql` service of the stack, or SQLite for local development |
| Tests | 13 PHPUnit tests, executed by the pipeline before every deployment |

> **Note on the PHP version.** Laravel 13 requires PHP >= 8.3, while `docker/php/Dockerfile` still
> builds on 8.2. This is intentional: the application is one of the migration targets of the project,
> and it starts serving traffic once the Ansible playbook raises the image to 8.3. Locally the site runs
> on the PHP version Herd provides (8.4).

## Structure

```
app-company-website/
├── Dockerfile                  # standalone image (php 8.1)
├── docker-compose.yml          # nginx + php-fpm + mysql, port 8081
├── docker/
│   ├── nginx/default.conf      # virtual host of the application
│   └── php/Dockerfile          # the php-fpm image used by compose
└── src/                        # the Laravel application
    ├── app/Http/Controllers/
    │   └── PageController.php       # the five pages + /health
    ├── config/project.php           # ALL site content lives here
    ├── public/
    │   ├── css/site.css             # the custom theme
    │   └── vendor/bootstrap/        # Bootstrap 5.3, served locally
    ├── resources/views/
    │   ├── components/              # layout, icon, feature-card, stat-tile, ...
    │   ├── partials/                # navbar, footer
    │   ├── pages/                   # home, infrastructure, pipeline, technologies, monitoring
    │   └── errors/                  # branded 404 and 503 pages
    ├── routes/web.php
    └── tests/Feature/               # PagesTest, HealthEndpointTest
```

### Content is data, not markup

Every number, server, application, PHP version, pipeline stage and technology shown on the site is
read from **`src/config/project.php`**. Adding an application or changing a PHP version means editing
that one file - no Blade template has to be touched.

## Routes

| Method | URI | Name | Purpose |
|---|---|---|---|
| GET | `/` | `home` | Project scope, key numbers, delivery flow |
| GET | `/infrastructure` | `infrastructure` | The four servers, the nine applications, the PHP matrix |
| GET | `/pipeline` | `pipeline` | The Jenkins stages, branching strategy, migration process |
| GET | `/technologies` | `technologies` | The role of each DevOps technology |
| GET | `/monitoring` | `monitoring` | Prometheus, Grafana, ELK, the Python utility |
| GET | `/health` | `health` | JSON health check for monitoring and smoke tests |

### `/health`

Consumed by the Python monitoring utility and by the smoke test stage of the pipeline. It reports the
PHP version the application actually ended up running on, which is exactly what has to be verified
after an Ansible-driven upgrade:

```json
{
  "status": "ok",
  "application": "app-company-website",
  "server": "VM2-Application-Server-1",
  "php": "8.3.x",
  "laravel": "13.23.0",
  "environment": "production",
  "checked_at": "2026-07-30T11:36:47+00:00"
}
```

## Running with Docker (the way it runs on VM2)

```bash
cd VM2-Application-Server-1/app-company-website

docker compose up -d --build
docker compose exec php composer install --no-dev --optimize-autoloader
docker compose exec php cp -n .env.example .env
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --force
```

The site is then served on <http://localhost:8081> (or `http://192.168.0.169:8081` on VM2).

To use the `mysql` service of the stack instead of SQLite, set this in `src/.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=company_site
DB_USERNAME=laravel
DB_PASSWORD=secret
```

With the default `DB_CONNECTION=sqlite` the application only needs
`src/database/database.sqlite` to exist (`php artisan migrate` creates it).

## Local development (Laravel Herd)

```bash
cd VM2-Application-Server-1/app-company-website/src

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate            # creates database/database.sqlite
herd link company-website      # -> http://company-website.test
```

There is **no `npm run build` step**: Bootstrap is served from `public/vendor` and the theme from
`public/css/site.css`, so a deployment only has to copy files.

## Tests

```bash
cd src
php artisan test
```

- **PagesTest** - every public page answers 200 and renders the content from `config/project.php`;
  unknown routes return 404.
- **HealthEndpointTest** - `/health` returns the exact JSON contract the monitoring relies on.

These are the tests the `Test` stage of the Jenkins pipeline runs; a failure stops the deployment
before the Docker image is built.
