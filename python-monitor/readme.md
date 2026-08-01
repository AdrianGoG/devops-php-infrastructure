# python-monitor

`infra_check.py` checks the servers and the applications of the infrastructure
and writes a report.

The point is to run it **before and after an update**, so you can see exactly
what broke.

## Install

```bash
cd python-monitor
pip install -r requirements.txt
```

Only one dependency: `requests`.

## How to use it

```bash
python infra_check.py                               # check everything
python infra_check.py --targets targets.local.json  # the local Herd sites
python infra_check.py --report before.json          # save the result
python infra_check.py --compare before.json         # compare with a saved result
```

Output:

```
  Infrastructure report - 30.07.2026 22:28:50
  ----------------------------------------------------------------------
  application           srv   php      code   time     state
  app-company-website   vm2   8.3.31   200    36 ms    ok
  app-user-dashboard    vm2   8.2.31   200    52 ms    ok
  app-api               vm2   7.4.33   200    30 ms    ok
  app-crm               vm3   7.4.33   200    14 ms    ok
  app-inventory         vm3   8.0.30   200    56 ms    ok
  app-ticket-system     vm3   8.1.34   200    41 ms    ok
  app-blog              vm4   8.3.31   200    44 ms    ok
  app-file-manager      vm4   8.3.31   200    67 ms    ok
  app-monitor           vm4   8.3.31   200    29 ms    ok
  ----------------------------------------------------------------------
  9 out of 9 applications are working
```

## Before and after an update

```bash
python infra_check.py --report before.json
ansible-playbook ../VM1-Jenkins-Ansible-Git/ansible/playbooks/upgrade-php.yml
python infra_check.py --compare before.json
```

```
  Comparison with before.json (from 2026-07-30 22:29:00)
  ----------------------------------------------------------------------
  app-blog               ok -> error   BROKE
```

## What the states mean

| State | Code | Meaning |
|---|---|---|
| `ok` | 200 | the application works |
| `degraded` | 200 | it answers, but says it has a problem (for example no database) |
| `not found` | 404 | the route does not exist, or files are missing after a deploy |
| `error` | 500 | error inside the application, usually a PHP incompatibility |
| `unavailable` | 503 | php-fpm is not answering behind the web server |
| `down` | - | nothing answered at all |

## Exit code

- `0` - everything works
- `1` - something is not working, or something broke since the saved report

So it can be used directly in Jenkins:

```groovy
stage('Smoke test') {
    steps {
        sh 'python3 python-monitor/infra_check.py'
    }
}
```

## Files

| File | What it is |
|---|---|
| `infra_check.py` | the script |
| `targets.json` | the list of servers and applications (the VMs) |
| `targets.local.json` | the same list, but for the local Herd sites |
| `logs/monitor.log` | the log, one line per check |
| `requirements.txt` | `requests` |

The servers are checked with a TCP connection on port 22, the SSH port, because
that is the port Ansible needs anyway.

To add an application, add a line in `targets.json`:

```json
{ "name": "app-new", "server": "vm3", "url": "http://192.168.0.105:8084/health" }
```