# app-blog

The public blog of the project - the first application of **VM4 - Application
Server 3**, served on port **8081**.

A small Laravel application: a public article list, an article page, and a plain
editor for writing and publishing.

| | |
|---|---|
| Framework | **Laravel 13** |
| PHP in the container | **8.2** - one version below what Laravel 13 requires |
| Database | MySQL 8.0 (the `mysql` service of the stack) |
| Front-end | Bootstrap 5.3 vendored in `src/public/vendor` - no Vite, no npm |
| Tests | 18 PHPUnit tests |
| Status | **blocked** - down until the PHP upgrade. See below. |

## The application is deliberately broken

All three applications of VM4 run **Laravel 13 on PHP 8.2**. Laravel 13 requires
PHP `^8.3`, so the application never gets to run a single line of its own code:

```
Fatal error: Uncaught RuntimeException: Composer detected issues in your platform:
Your Composer dependencies require a PHP version ">= 8.3.0". You are running 8.2.31.
in vendor/composer/platform_check.php:22
```

Nginx turns that into **HTTP 500 with an empty body** on every URL, including
`/health`.

This is the mirror image of the applications on VM3:

| | VM3 (`app-crm`, `app-inventory`) | VM4 (this server) |
|---|---|---|
| Now, before the upgrade | working | **down** |
| After the PHP upgrade | **down** | working |
| What fixes it | changing the source code | the upgrade itself |

So the estate has both directions covered: an upgrade that *breaks* applications
and needs code changes afterwards, and an upgrade that *is* the fix. The Python
monitor and `app-monitor` report both the same way - a status code and a PHP
version - and only the error tells you which is which.

### The migration

One line, in `docker/php/Dockerfile`:

```diff
-FROM php:8.2-fpm
+FROM php:8.3-fpm
```

Then `docker compose up -d --build php`. Nothing else changes: no source diff, no
`composer update`. That is what the Ansible playbook automates.

## What it does

| Route | Method | What it does |
|---|---|---|
| `/` | GET | Public list of published articles; `?q=` searches title and excerpt |
| `/posts/manage` | GET | The editor - every post, drafts included |
| `/posts/create` | GET | New post form |
| `/posts` | POST | Store a post (the slug is generated and kept unique) |
| `/posts/{slug}` | GET | The article page - **404 for a draft** |
| `/posts/{slug}/edit` | GET | Edit form |
| `/posts/{slug}` | PUT | Update |
| `/posts/{slug}` | DELETE | Delete |
| `/health` | GET | JSON health check, same contract as the other applications |

A post has a title, an auto-generated unique slug, an excerpt for the listing, a
body, a status (`draft` or `published`), an optional author and a publication
date. Publishing stamps `published_at`; pulling a post back to draft clears it.

There is no authentication: this is an internal lab application, and
`app-user-dashboard` already demonstrates Breeze.

## Structure

```
app-blog/
├── Dockerfile                      # standalone image (php:8.2-fpm)
├── docker-compose.yml              # nginx + php-fpm + mysql, port 8081
├── docker/
│   ├── nginx/default.conf
│   ├── php/Dockerfile              # ← the one line the migration changes
│   └── mysql/init/                 # creates blog_test on first boot
└── src/
    ├── app/
    │   ├── Models/Post.php                 # published() scope, uniqueSlug(), readingMinutes()
    │   ├── Http/Controllers/PostController.php
    │   └── Http/Requests/PostRequest.php
    ├── database/
    │   ├── migrations/…create_posts_table.php
    │   ├── factories/PostFactory.php       # draft() state
    │   └── seeders/DatabaseSeeder.php      # 4 articles, idempotent
    ├── public/{css/app.css, vendor/bootstrap/}
    ├── resources/views/
    │   ├── layouts/app.blade.php
    │   └── posts/{index,show,manage,form}.blade.php
    ├── routes/web.php
    └── tests/Feature/{PostTest,HealthTest}.php
```

### Design notes

- **The editor routes are declared before `/posts/{post}`**, otherwise `manage`
  and `create` would be read as slugs.
- **Slugs stay unique automatically.** Two posts with the same title become
  `same-title` and `same-title-2`. A test covers it.
- **A draft returns 404 on the public site**, not a redirect: from the outside
  the article simply does not exist yet.

## Running with Docker (the way it runs on VM4)

```bash
cd VM4-Application-Server-3/app-blog

docker compose up -d --build
# Every request answers 500 at this point - that is expected, see above.

docker compose exec php composer install --no-dev --optimize-autoloader
docker compose exec php cp -n .env.example .env
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --force
docker compose exec php php artisan db:seed --force
```

`composer install` will refuse to run on PHP 8.2 as well, for the same reason.
Both problems disappear with the one line change above.

## Local development

Locally the application is served on **PHP 8.3**, so it actually runs:

```bash
cd VM4-Application-Server-3/app-blog
docker compose up -d mysql          # publishes 127.0.0.1:33067

cd src
composer install
cp .env.example .env                # then DB_HOST=127.0.0.1, DB_PORT=33067
php artisan key:generate
php artisan migrate
php artisan db:seed

herd link app-blog
herd isolate 8.3 --site=app-blog    # -> http://app-blog.test
```

To see the failure the container will produce, switch the site to 8.2:

```bash
herd isolate 8.2 --site=app-blog
curl -i http://app-blog.test/health     # HTTP 500
herd isolate 8.3 --site=app-blog
```

## Tests

```bash
cd src
php artisan test
```

18 tests: the public listing, drafts staying hidden, the search, the article page,
the editor, the create flow and slug generation, duplicate titles getting distinct
slugs, the publication date being stamped and cleared, all validation rules, the
update and delete flows, and the health contract.

They run against the real MySQL, on the dedicated `blog_test` database created by
the container's init script.