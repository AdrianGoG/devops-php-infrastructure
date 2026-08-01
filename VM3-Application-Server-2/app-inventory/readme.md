# app-inventory

Stock management - the second application of **VM3 - Application Server 2**,
served on port **8082**.

A deliberately small Laravel application: one model, one controller, two views.
List the stock, search it, spot what has to be reordered, add / edit / delete a
product.

| | |
|---|---|
| Framework | **Laravel 9** on **PHP 8.0** |
| Database | MySQL 8.0 (the `mysql` service of the stack) |
| Front-end | Bootstrap 5.3 vendored in `src/public/vendor` - no Vite, no Tailwind, no npm |
| Tests | 15 PHPUnit tests |
| Status | **legacy** - blocked by its *dependencies*, not by its source code. See [MIGRATION.md](MIGRATION.md) |

> **Why Laravel 9 / PHP 8.0.** This application demonstrates the second kind of
> legacy problem. Its own code is clean; what pins it down is a framework version
> that never supported PHP 8.3. The fix is a staged framework upgrade, not a code
> diff - which is a very different amount of work, and worth showing next to
> `app-crm`, whose whole migration is three one-line changes.

## What it does

| Route | Method | What it does |
|---|---|---|
| `/` | GET | Redirects to the stock list |
| `/products` | GET | Stock list; `?q=` searches SKU/name/location, `?low=1` shows only what needs reordering |
| `/products/create` | GET | New product form |
| `/products` | POST | Store a product |
| `/products/{id}/edit` | GET | Edit form (404 for an unknown id) |
| `/products/{id}` | PUT | Update |
| `/products/{id}` | DELETE | Delete |
| `/health` | GET | JSON health check, same contract as the other applications |

A product has a SKU (unique, stored uppercased), a name, quantity in stock, a
reorder level, a unit price and an optional location. A product counts as **low
stock** when `quantity <= reorder_level`, which is one `whereColumn` scope on the
model - the list header shows how many are in that state and the total value of
the stock currently displayed.

```bash
curl -s http://192.168.0.159:8082/health
```

```json
{
  "status": "ok",
  "application": "app-inventory",
  "server": "VM3-Application-Server-2",
  "php": "8.0.30",
  "laravel": "9.52.21",
  "environment": "production",
  "database": "ok",
  "products": 7,
  "checked_at": "2026-07-30T13:06:21+00:00"
}
```

## Structure

```
app-inventory/
├── Dockerfile                      # standalone image (php:8.0-fpm)
├── docker-compose.yml              # nginx + php-fpm + mysql, port 8082
├── docker/
│   ├── nginx/default.conf
│   ├── php/Dockerfile
│   └── mysql/init/                 # creates inventory_test on first boot
├── MIGRATION.md                    # the dependency-blocked upgrade dossier
└── src/
    ├── app/
    │   ├── Models/Product.php              # + lowStock() scope, stockValue()
    │   ├── Http/Controllers/ProductController.php
    │   └── Http/Requests/ProductRequest.php
    ├── database/
    │   ├── migrations/…create_products_table.php
    │   ├── factories/ProductFactory.php    # + lowStock() state
    │   └── seeders/DatabaseSeeder.php      # 7 products, idempotent
    ├── public/
    │   ├── css/app.css                     # shared palette with the other apps
    │   └── vendor/bootstrap/
    ├── resources/views/
    │   ├── layouts/app.blade.php
    │   └── products/{index,form}.blade.php  # form shared by create and edit
    ├── routes/web.php                      # products + /health
    └── tests/Feature/{ProductTest,HealthTest}.php
```

### Design notes

- **No front-end build step.** Laravel 9 scaffolds Vite; it was removed. Bootstrap
  is served from `public/vendor`, so a deployment only copies files. `public/build`
  is gitignored by Laravel, so keeping Vite would have meant shipping a site with
  no CSS.
- **One form for create and edit.** `products/form.blade.php` switches on
  `$product->exists`, so there is one place to change a field.
- **The unique SKU rule ignores the current row** on update, which is the usual
  trap with unique validation on an edit form. A test covers it.

## Running with Docker (the way it runs on VM3)

```bash
cd VM3-Application-Server-2/app-inventory

docker compose up -d --build

docker compose exec php composer install --no-dev --optimize-autoloader
docker compose exec php cp -n .env.example .env
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --force
docker compose exec php php artisan db:seed --force
```

Served on <http://localhost:8082> (or `http://192.168.0.159:8082` on VM3). The
MySQL container publishes port **33065** on the host and creates two databases on
first boot: `inventory` and `inventory_test`.

### If the build fails with a dpkg error

```
dpkg: error: corrupt info database format file '/var/lib/dpkg/info/format'
E: Sub-process /usr/bin/dpkg returned an error code (2)
```

That is not a problem with this Dockerfile - it is a corrupted Docker image store
on the server. The layer was written to disk incomplete, so files inside the image
come out truncated to zero bytes. Do not patch around it in the Dockerfile; the
next build will find a different file damaged.

Reset the store on the affected server and let the playbook rebuild:

```bash
docker rm -f $(docker ps -aq)
docker rmi -f $(docker images -q)
docker system prune -af --volumes
```

```bash
ansible-playbook playbooks/deploy.yml --limit vm3
```

## Local development

```bash
cd VM3-Application-Server-2/app-inventory
docker compose up -d mysql          # publishes 127.0.0.1:33065

cd src
composer install
cp .env.example .env                # then DB_HOST=127.0.0.1, DB_PORT=33065
php artisan key:generate
php artisan migrate
php artisan db:seed

herd link app-inventory
herd isolate 8.0 --site=app-inventory   # -> http://app-inventory.test
```

## Tests

```bash
cd src
php artisan test
```

15 tests: the stock list, the search, the low stock filter, the create / update /
delete flow, the validation rules (required fields, duplicate SKU, negative
quantity, unique SKU ignoring itself on update) and the health contract.

They run against the real MySQL, on the dedicated `inventory_test` database
created by the container's init script, so nothing touches application data.
