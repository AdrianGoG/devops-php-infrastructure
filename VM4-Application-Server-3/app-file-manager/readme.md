# app-file-manager

Upload, list, download and delete files - the second application of
**VM4 - Application Server 3**, served on port **8082**.

It exists in this project for one reason: it is the application that proves
**Docker volume persistence**. The database rows describe the files; the bytes
live on a named volume that survives every container rebuild, including the PHP
upgrade.

| | |
|---|---|
| Framework | **Laravel 13** |
| PHP in the container | **8.2** - one version below what Laravel 13 requires |
| Database | MySQL 8.0 (the `mysql` service of the stack) |
| Storage | the `uploads` volume, mounted at `storage/app/uploads` |
| Front-end | Bootstrap 5.3 vendored in `src/public/vendor` - no Vite, no npm |
| Tests | 11 PHPUnit tests |
| Status | **blocked** - down until the PHP upgrade |

> **Deliberately broken.** Like the other two applications of VM4, this one runs
> Laravel 13 on PHP 8.2 and answers HTTP 500 on every URL until the runtime is
> raised to 8.3. The full explanation is in
> [app-blog/readme.md](../app-blog/readme.md#the-application-is-deliberately-broken).

## What it does

| Route | Method | What it does |
|---|---|---|
| `/` | GET | Upload form and the list of stored files; `?q=` searches name and description |
| `/files` | POST | Upload a file (max 8 MB) with an optional description |
| `/files/{id}/download` | GET | Download under the original name |
| `/files/{id}` | DELETE | Delete the row **and** the bytes |
| `/health` | GET | JSON health check - also reports whether the uploads volume is writable |

## How the storage is handled

Three decisions worth reading, because they are the ones that usually go wrong in
a file manager:

- **The stored name is never the uploaded name.** Every file is written as a
  random ULID plus the original extension. Two uploads called `report.pdf` cannot
  overwrite each other, and a name like `../../evil.php` can never reach the
  filesystem. A test asserts exactly that.
- **The uploads disk is not public.** It lives outside `public/`, and downloads go
  through the controller. An uploaded `.php` file can never be executed by the web
  server - which is the classic hole in a naive file manager.
- **Deleting a row deletes the bytes.** `StoredFile::booted()` hooks the
  `deleting` event, so the volume never fills up with orphans.

The listing also flags a file whose bytes are gone (`missing on disk`) instead of
offering a download that would fail - metadata and storage can drift apart, for
instance after restoring a database dump without the matching volume.

```json
{
  "status": "ok",
  "application": "app-file-manager",
  "server": "VM4-Application-Server-3",
  "php": "8.3.31",
  "laravel": "13.23.0",
  "database": "ok",
  "storage": "writable",
  "files": 3,
  "bytes_stored": 184320,
  "checked_at": "2026-07-30T13:40:12+00:00"
}
```

`storage` is part of the contract on purpose: a container that boots with an
unwritable volume answers 200 everywhere else while being useless.

## Structure

```
app-file-manager/
├── Dockerfile                      # standalone image (php:8.2-fpm)
├── docker-compose.yml              # nginx + php-fpm + mysql + the uploads volume
├── docker/
│   ├── nginx/default.conf
│   ├── php/Dockerfile              # ← the one line the migration changes
│   └── mysql/init/                 # creates filemanager_test on first boot
└── src/
    ├── app/
    │   ├── Models/StoredFile.php           # humanSize(), existsOnDisk(), byte cleanup
    │   └── Http/Controllers/FileController.php
    ├── config/filesystems.php              # the "uploads" disk
    ├── database/migrations/…create_stored_files_table.php
    ├── public/{css/app.css, vendor/bootstrap/}
    ├── resources/views/{layouts/app,files/index}.blade.php
    ├── routes/web.php
    └── tests/Feature/FileManagerTest.php
```

The volume is declared in `docker-compose.yml`:

```yaml
php:
  volumes:
    - ./src:/var/www/html
    - uploads:/var/www/html/storage/app/uploads
```

Rebuild the container, upgrade PHP, replace the image entirely - the uploads stay.

## Running with Docker (the way it runs on VM4)

```bash
cd VM4-Application-Server-3/app-file-manager

docker compose up -d --build
docker compose exec php composer install --no-dev --optimize-autoloader
docker compose exec php cp -n .env.example .env
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --force
```

Served on <http://localhost:8082> (or `http://192.168.0.125:8082` on VM4) - once
the PHP version is raised to 8.3. The MySQL container publishes port **33068**.

## Local development

```bash
cd VM4-Application-Server-3/app-file-manager
docker compose up -d mysql          # publishes 127.0.0.1:33068

cd src
composer install
cp .env.example .env                # then DB_HOST=127.0.0.1, DB_PORT=33068
php artisan key:generate
php artisan migrate

herd link app-file-manager
herd isolate 8.3 --site=app-file-manager   # -> http://app-file-manager.test
```

## Tests

```bash
cd src
php artisan test
```

11 tests: uploading, the stored name never being the uploaded name, two files with
the same name not colliding, the size and required-file validation, the listing and
its search, downloading under the original name, a download whose bytes are gone
being reported instead of failing, deleting removing both row and bytes, and the
human-readable size helper.

`Storage::fake('uploads')` replaces the disk, so the suite never writes into the
real volume.