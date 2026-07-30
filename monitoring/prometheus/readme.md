# prometheus

`prometheus.yml` is the only file here: it says what to scrape and how often.

Four jobs:

| Job | Targets | What it gives |
|---|---|---|
| `prometheus` | itself | whether Prometheus is healthy |
| `servers` | `:9100` on all four VMs | CPU, memory, disk |
| `estate` | `vm4:8083/metrics` (app-monitor) | availability and response time of the applications |
| `registry` | `vm2:8083/metrics` (app-api) | the registry counters and the deployment history |

Every target carries a `server` or `application` label, which is what the
dashboard groups by.

## Adding a target

```yaml
  - job_name: 'servers'
    static_configs:
      - targets: ['192.168.0.173:9100']
        labels:
          server: vm5
```

Then reload without restarting the container:

```bash
docker compose kill -s SIGHUP prometheus
```

## Checking

<http://localhost:9090/targets> shows every target and why a failing one fails.
The scrape interval is 15 seconds, so a change takes about that long to appear.

Data is kept for 30 days (`--storage.tsdb.retention.time` in the compose file) in
the `prometheus_data` volume, so it survives a container restart.