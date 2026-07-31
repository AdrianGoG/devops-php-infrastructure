# monitoring

Prometheus and Grafana, running on **VM1**.

They live on the control node and not on an application server on purpose: if VM3
goes down, you want the monitoring to still be up to tell you about it.

## What is scraped

```
VM1
├── Prometheus  :9090
└── Grafana     :3000
        │
        ├── node_exporter   vm1/vm2/vm3/vm4:9100      CPU, memory, disk
        ├── app-monitor     vm4:8083/metrics          availability + response time of all 8 applications
        └── app-api         vm2:8083/metrics          registry counters + deployment history
```

Only one target is needed for the availability of the whole estate: `app-monitor`
already probes the other eight applications and exposes the result.

| Requirement of the project (2.8) | Where it comes from |
|---|---|
| availability of the applications | `estate_application_up` |
| response time | `estate_application_response_milliseconds` |
| CPU usage | `node_cpu_seconds_total` |
| memory usage | `node_memory_*` |
| application and web server logs | the ELK stack, not this folder |

## Starting it

On VM1:

```bash
cd monitoring
docker compose up -d
```

- Prometheus: <http://192.168.0.106:9090> - VM1's address, port 9090
- Grafana: <http://192.168.0.106:3000>, user `admin`, password `admin`

**Change the Grafana password** in `docker-compose.yml` before this is reachable
by anyone else.

The `node-exporter` service in this compose file is the one for VM1 itself. The
three application servers get their own through Ansible:

```bash
cd VM1-Jenkins-Ansible-Git/ansible
ansible-playbook playbooks/monitoring.yml
```

That playbook copies `monitoring/node-exporter/docker-compose.yml` to each server
and starts it, then waits for `:9100/metrics` to answer.

## Checking that it works

```bash
curl -s localhost:9090/api/v1/targets | grep -o '"health":"[a-z]*"' | sort | uniq -c
```

Or open <http://localhost:9090/targets> - every target should say **UP**. A target
that stays DOWN is either a server that is off, or port 9100 / 8083 blocked by a
firewall.

## The dashboard

Grafana loads the datasource and the dashboard by itself from
`grafana/provisioning/` - there is nothing to click through in the interface, and
the dashboard is versioned in Git with the rest of the project.

`grafana/dashboards/estate.json` has:

| Panel | What it shows |
|---|---|
| Applications down / healthy | the two numbers that matter |
| Still to migrate | how many applications are still `legacy` or `blocked` |
| Failed deployments | from the deployment log of app-api |
| Application availability | one line per application, 1 or 0 |
| Response time | milliseconds per application |
| **PHP version per application** | a table, one row per application |
| CPU / memory / disk | per server, from node_exporter |

The PHP version table is the one to watch during a migration: each application
switches to 8.3 as its container is rebuilt. If a playbook reports success but
one container stayed on the old image, the table shows it - a status code would
not.

## Structure

```
monitoring/
├── docker-compose.yml              Prometheus + Grafana + node_exporter for VM1
├── prometheus/prometheus.yml       what to scrape
├── grafana/
│   ├── provisioning/
│   │   ├── datasources/            the Prometheus datasource
│   │   └── dashboards/             tells Grafana where to find the dashboards
│   └── dashboards/estate.json      the dashboard itself
└── node-exporter/docker-compose.yml   deployed to the three servers by Ansible
```

## What was verified and what was not

Started locally: Prometheus and Grafana come up, Grafana provisions the datasource
and the dashboard on its own, node_exporter is scraped, and every PromQL query in
the dashboard returns `success`.

Not verified: the four remote targets, because the servers do not exist outside
the lab. The disk panel uses `mountpoint="/"`, which is right on an Ubuntu server
but shows nothing on Docker Desktop, where the filesystem layout is different.