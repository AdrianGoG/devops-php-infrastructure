# grafana

Grafana configures itself from the files in this folder - nothing is set up
through the interface, so everything is versioned in Git.

```
provisioning/datasources/prometheus.yml   the Prometheus connection
provisioning/dashboards/dashboards.yml    where to look for dashboards
dashboards/estate.json                    the dashboard
```

At startup Grafana reads `provisioning/` and creates the datasource and the
dashboard by itself. `updateIntervalSeconds: 30` means an edited JSON file is
picked up without restarting the container.

## Logging in

<http://localhost:3000>, user `admin`, password `admin` - set in
`monitoring/docker-compose.yml`. **Change it** before the port is reachable by
anyone else.

## Changing a dashboard

The panels can be edited in the interface (`allowUiUpdates: true`), but those
changes stay in Grafana's database and are **not** written back to the JSON file.
To keep a change:

1. Edit the panel in the interface.
2. **Dashboard settings** -> **JSON Model** -> copy everything.
3. Paste it into `dashboards/estate.json` and commit.

Otherwise the change is lost the first time the container is recreated.

## Adding a dashboard

Drop another `.json` file in `dashboards/`. It is picked up within 30 seconds; no
restart and no configuration needed.

Ready made dashboards from [grafana.com/dashboards](https://grafana.com/grafana/dashboards/)
work the same way - number **1860** ("Node Exporter Full") is the usual one for
servers and covers far more than the CPU and memory panels here.