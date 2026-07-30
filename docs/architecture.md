# Architecture - what each application does and what talks to what

Nine PHP applications on three servers, plus a control node. This document says
what each one is for and, more importantly, **which ones talk to each other**.

## The short answer

**Six of the nine applications talk to nobody.** They are independent business
applications, each with its own database, its own PHP version and its own
container. That is deliberate: it is what a real legacy estate looks like, and it
is what makes the containerisation worth anything - upgrading one cannot break
another.

There are only three links in the whole estate:

```
app-user-dashboard  ──HTTP──►  app-api          the dashboard has no data of its own
app-monitor         ──HTTP──►  all 8 others     it probes their health endpoints
Jenkins             ──HTTP──►  app-api          it records every deployment
```

Everything else is a one-way street from the outside in: Prometheus scrapes,
the Python utility probes, nobody answers back.

## The whole picture

```
                          VM1 - control node
        ┌──────────────────────────────────────────────────┐
        │  Jenkins        Ansible        Prometheus         │
        │     │              │            Grafana           │
        └─────┼──────────────┼───────────────┼──────────────┘
              │              │               │
    POST /api/deployments   rsync +      scrape /metrics
              │            docker compose     │
              ▼              ▼                ▼
   ┌──────────────────────────────────────────────────────────┐
   │ VM2 :8081 app-company-website   (Laravel 13, PHP 8.3)    │
   │     :8082 app-user-dashboard    (Laravel 12, PHP 8.2)    │
   │     :8083 app-api               (plain PHP 7.4)          │
   │              ▲                                            │
   │              └──── the dashboard reads from the API       │
   ├──────────────────────────────────────────────────────────┤
   │ VM3 :8081 app-crm               (plain PHP 7.4)          │
   │     :8082 app-inventory         (Laravel 9,  PHP 8.0)    │
   │     :8083 app-ticket-system     (Laravel 10, PHP 8.1)    │
   ├──────────────────────────────────────────────────────────┤
   │ VM4 :8081 app-blog              (Laravel 13, PHP 8.2)    │
   │     :8082 app-file-manager      (Laravel 13, PHP 8.2)    │
   │     :8083 app-monitor           (Laravel 13, PHP 8.2)    │
   │              │                                            │
   │              └──── probes the health of the other eight   │
   └──────────────────────────────────────────────────────────┘
```

---

## VM2 - Application Server 1

The only server where two applications talk to each other.

### app-company-website · port 8081 · Laravel 13 / PHP 8.3

The presentation site of the project: the infrastructure, the pipeline, the
technologies, the monitoring.

**Talks to:** nothing. Every number it shows comes from `config/project.php`, a
file in its own repository. It has a database only because Laravel wants one for
sessions.

**Why it is like that:** a presentation site that goes blank because an API is
down is a bad presentation site. It is the one page that must always work.

### app-user-dashboard · port 8082 · Laravel 12 / PHP 8.2

Login and registration with Laravel Breeze, then one dashboard page showing the
state of the estate.

**Talks to:** `app-api`, over HTTP, through `app/Services/RegistryClient.php`:

```
GET http://192.168.0.170:8083/api/servers
GET http://192.168.0.170:8083/api/applications
GET http://192.168.0.170:8083/api/deployments
```

**It stores nothing about the infrastructure.** Its own database only holds user
accounts and sessions. Everything on the dashboard is fetched from the API on
each page load and cached for 15 seconds.

**If app-api is down**, the page still renders, with a `registry offline` badge
and empty panels. That matters, because app-api *will* be down during its own PHP
upgrade.

**This is the pair that proves the containerisation works:** a PHP 8.2
application and a PHP 7.4 application, on the same server, talking to each other
over HTTP.

### app-api · port 8083 · plain PHP 7.4

The registry of the infrastructure, and the only application written without a
framework.

| Endpoint | Who calls it |
|---|---|
| `GET /api/servers`, `/api/applications`, `/api/deployments` | app-user-dashboard |
| `POST /api/deployments` | **Jenkins**, at the end of every pipeline run |
| `GET /metrics` | Prometheus |
| `GET /api/health` | app-monitor, python-monitor |

It is the busiest application of the estate in terms of connections, and the
oldest in terms of PHP. That combination is on purpose: it is the one whose
upgrade is most visible when it breaks.

---

## VM3 - Application Server 2

**None of these three talk to each other.** Three separate business applications
that happen to share a server - which is exactly the situation the project is
modelling.

### app-crm · port 8081 · plain PHP 7.4

A client register: list with search and filter, add, edit, delete. No framework,
no Composer, no `vendor/` directory - every page is a real file in `public/`.

**Talks to:** its own MySQL, nothing else.

**Its role in the project:** it is the application whose **source code** blocks
the PHP upgrade. Three constructs removed in PHP 8 live in
`src/includes/functions.php`, and the migration is three one-line diffs. See
[MIGRATION.md](../VM3-Application-Server-2/app-crm/MIGRATION.md).

### app-inventory · port 8082 · Laravel 9 / PHP 8.0

Stock management: products with SKU, quantity, reorder level, unit price. Marks
what has to be reordered.

**Talks to:** its own MySQL, nothing else.

**Its role in the project:** it is the application whose **dependencies** block
the upgrade. Its own code is clean; Laravel 9 simply never supported PHP 8.3. The
migration is four major framework upgrades, not a code diff.

### app-ticket-system · port 8083 · Laravel 10 / PHP 8.1

A support ticket queue: reference, priority, status, assignee, one-click close.

**Talks to:** its own MySQL, nothing else.

**Its role in the project:** it is the application that is **not blocked at all**.
Laravel 10 supports PHP 8.3, so its migration is one line in a Dockerfile. Having
one of these makes the other two measurable.

> VM3 in one sentence: three applications that produce the same HTTP 500 after a
> PHP upgrade, for three completely different reasons, needing three completely
> different amounts of work.

---

## VM4 - Application Server 3

All three run **Laravel 13 on PHP 8.2**, which is one version below what Laravel
13 requires. They answer HTTP 500 on every URL until the Ansible playbook raises
them to 8.3 - the mirror image of VM3, where the upgrade is what *breaks* things.

### app-blog · port 8081 · Laravel 13 / PHP 8.2

A public blog: article list, article page, and a small editor. Drafts return 404
on the public site.

**Talks to:** its own MySQL, nothing else.

### app-file-manager · port 8082 · Laravel 13 / PHP 8.2

Upload, list, download and delete files.

**Talks to:** its own MySQL, and a **named Docker volume** for the bytes:

```yaml
volumes:
  - uploads:/var/www/html/storage/app/uploads
```

**Its role in the project:** it is the application that proves volume
persistence. Rebuild the container, upgrade PHP, replace the image entirely - the
uploaded files stay.

### app-monitor · port 8083 · Laravel 13 / PHP 8.2

The only application that talks to all the others.

```
app-monitor  ──GET /health──►  the other eight applications
             ──GET /metrics──  scraped by Prometheus
```

It probes every health endpoint, works out whether each application is `ok`,
`degraded` or `down`, and exposes the result as Prometheus metrics. One scrape
target gives Prometheus the availability of the whole estate.

**It does not probe itself** on purpose: if it were down, nothing here would be
running to report it. That is Prometheus's job - a failed scrape is the signal.

---

## What makes one monitor enough for nine applications

Every application, whatever its framework or PHP version, answers the same shape
on its health endpoint:

```json
{
  "status": "ok",
  "application": "app-crm",
  "server": "VM3-Application-Server-2",
  "php": "7.4.33",
  "database": "ok",
  "checked_at": "2026-07-30T12:54:57+00:00"
}
```

Nine different code bases, one contract. That is why `app-monitor`,
`python-monitor` and the Jenkins smoke test can all treat them identically, and
why the `php` field appears everywhere - it is the field that tells you whether
an upgrade actually reached a container.

The health paths differ slightly, because the applications differ:

| Application | Path |
|---|---|
| app-api | `/api/health` |
| app-crm | `/health.php` |
| the other seven | `/health` |

## Databases

**One MySQL container per application. Nothing is shared.**

| Application | Database | Host port |
|---|---|---|
| app-company-website | `company_site` | 33061 |
| app-user-dashboard | `user_dashboard` | 33062 |
| app-api | `app_api` | 33063 |
| app-crm | `crm` | 33064 |
| app-inventory | `inventory` | 33065 |
| app-ticket-system | `tickets` | 33066 |
| app-blog | `blog` | 33067 |
| app-file-manager | `filemanager` | 33068 |
| app-monitor | `monitor` | 33069 |

A shared database would mean a schema change in one application could break
another, and that upgrading PHP for one would need a maintenance window for all.
Separate databases are what make the applications independently upgradable.

The published host ports are only for administration and for the test suites -
the applications reach their database over the internal Docker network, as
`mysql:3306`.

## Where the code lives

The code is **not** inside the Docker images. Every application mounts its `src`
folder into the container:

```yaml
php:
  volumes:
    - ./src:/var/www/html
```

The image holds PHP and its extensions; the container reads the code off the
server's disk. So deploying is a file copy, and only a change to the Dockerfile
itself needs an image rebuild.

```
you --git push--> GitHub --checkout--> VM1 --rsync--> VM2 / VM3 / VM4
```

Only VM1 talks to GitHub, and each server receives only its own three
applications.