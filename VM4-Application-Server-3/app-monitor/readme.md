# app-monitor

The estate monitor - the third application of **VM4 - Application Server 3**,
served on port **8083**.

It probes the health endpoint of every other application in the infrastructure and
exposes the result in two forms: a dashboard for a human, and a Prometheus
exposition endpoint for Grafana.

| | |
|---|---|
| Framework | **Laravel 13** |
| PHP in the container | **8.2** - one version below what Laravel 13 requires |
| Front-end | Bootstrap 5.3 vendored in `src/public/vendor` - no Vite, no npm |
| Tests | 9 PHPUnit tests |
| Status | **blocked** - down until the PHP upgrade |

> **Deliberately broken.** Like the other two applications of VM4, this one runs
> Laravel 13 on PHP 8.2 and answers HTTP 500 until the runtime is raised to 8.3.
> The full explanation is in
> [app-blog/readme.md](../app-blog/readme.md#the-application-is-deliberately-broken).

## What it does

| Route | What it does |
|---|---|
| `/` | Dashboard: one row per application - PHP version, HTTP status, response time, state |
| `/metrics` | Prometheus exposition format |
| `/health` | Its own health check, same contract as everything it probes |

Eight applications are probed - everything except itself. `app-monitor` does not
probe itself on purpose: if it were down, nothing here would be running to report
it. That job belongs to Prometheus, whose scrape failure is the signal.

### Why this exists next to the Python utility

They answer different questions:

| | `python-monitor` | `app-monitor` |
|---|---|---|
| Runs | on the control node, around a deployment | continuously, on VM4 |
| Triggered by | Jenkins, before and after an upgrade | Prometheus, every scrape |
| Output | a report and a log file to compare | live gauges for Grafana |

Both read the **same health contract**, which is the reason every application in
this estate exposes `status`, `php`, `database` and a timestamp in the same shape.
One contract, two consumers.

## The metrics

```
estate_applications_total 8
estate_applications_healthy 7
estate_applications_down 1
estate_application_up{application="app-blog",server="vm4"} 0
estate_application_response_milliseconds{application="app-api",server="vm2"} 46
estate_application_php_version_info{application="app-api",version="7.4.33"} 1
```

`estate_application_php_version_info` is the interesting one during a migration:
Grafana shows which PHP version each application *actually* answers on, so a
playbook that reported success while leaving a container on the old image is
visible immediately.

## Configuration

`config/estate.php` holds the list of probes. In production the URLs are built
from the three server addresses:

```dotenv
ESTATE_BASE_VM2=http://192.168.0.169
ESTATE_BASE_VM3=http://192.168.0.159
ESTATE_BASE_VM4=http://192.168.0.125
ESTATE_TIMEOUT=3
ESTATE_CACHE_SECONDS=10
```

Any single probe can be overridden with its own variable - which is how the local
setup points at the Herd `.test` domains instead:

```dotenv
ESTATE_URL_API=http://app-api.test/api/health
ESTATE_URL_CRM=http://app-crm.test/health.php
```

Results are cached for `ESTATE_CACHE_SECONDS`, so refreshing the dashboard does
not re-probe the whole estate.

## How a failure is reported

A broken application does not return friendly JSON - a PHP version mismatch
returns a 500 with an **empty body**. The probe handles all three cases
separately, and the tests cover each one:

| Situation | `reachable` | `http_status` | `status` |
|---|---|---|---|
| Healthy | true | 200 | `ok` |
| Answering, but erroring | true | 500 | `down` |
| Not answering at all | false | `null` | `unreachable` |

## Structure

```
app-monitor/
├── Dockerfile                      # standalone image (php:8.2-fpm)
├── docker-compose.yml              # nginx + php-fpm + mysql, port 8083
├── docker/
│   ├── nginx/default.conf
│   ├── php/Dockerfile              # ← the one line the migration changes
│   └── mysql/init/
└── src/
    ├── app/
    │   ├── Services/EstateProbe.php        # the prober
    │   └── Http/Controllers/MonitorController.php
    ├── config/estate.php                   # what to probe
    ├── public/{css/app.css, vendor/bootstrap/}
    ├── resources/views/{layouts/app,monitor/index}.blade.php
    ├── routes/web.php
    └── tests/Feature/MonitorTest.php
```

## Prometheus

```yaml
scrape_configs:
  - job_name: 'estate'
    metrics_path: /metrics
    scrape_interval: 30s
    static_configs:
      - targets: ['192.168.0.125:8083']
```

One target gives Prometheus the availability of all nine applications, because
`app-monitor` does the fan-out.

## Local development

```bash
cd VM4-Application-Server-3/app-monitor
docker compose up -d mysql          # publishes 127.0.0.1:33069

cd src
composer install
cp .env.example .env                # then point the ESTATE_URL_* at the .test sites
php artisan key:generate
php artisan migrate

herd link app-monitor
herd isolate 8.3 --site=app-monitor   # -> http://app-monitor.test
```

## Tests

```bash
cd src
php artisan test
```

9 tests: the dashboard listing every probe, the healthy/down counters, an
application answering 500 without a body being reported as `down`, an unreachable
application not crashing the page, the reported PHP versions, the Prometheus
content type, one gauge per application, the PHP version metric, and its own
health endpoint.

The estate is **always faked** with `Http::fake`, so the suite passes in the
pipeline whether or not the other eight applications happen to be running.