# devops-php-infrastructure

A complete CI/CD pipeline for managing and modernising a PHP web infrastructure.
ITSchool final project, theme 2.

Four Ubuntu servers, nine PHP applications on five different PHP versions, all
containerised, deployed and upgraded from a single control node - and monitored
well enough to tell you exactly what broke and why.

| | |
|---|---|
| Servers | 4 (1 control node + 3 application servers) |
| Applications | 9 PHP applications, each in its own containers |
| PHP versions | 7.4 · 8.0 · 8.1 · 8.2 · 8.3, running side by side |
| Frameworks | Laravel 9 · 10 · 12 · 13, and two applications with no framework |
| Tests | 206 automated tests, run before every deployment |

**Start here:** [docs/architecture.md](docs/architecture.md) explains what each
application does and which ones talk to each other.

## The estate

| Application | Server | Port | PHP | Framework |
|---|---|---|---|---|
| app-company-website | VM2 | 8081 | 8.3 | Laravel 13 |
| app-user-dashboard | VM2 | 8082 | 8.2 | Laravel 12 + Breeze |
| app-api | VM2 | 8083 | 7.4 | plain PHP |
| app-crm | VM3 | 8081 | 7.4 | plain PHP |
| app-inventory | VM3 | 8082 | 8.0 | Laravel 9 |
| app-ticket-system | VM3 | 8083 | 8.1 | Laravel 10 |
| app-blog | VM4 | 8081 | 8.2 | Laravel 13 |
| app-file-manager | VM4 | 8082 | 8.2 | Laravel 13 |
| app-monitor | VM4 | 8083 | 8.2 | Laravel 13 |

## Repository layout

```
VM1-Jenkins-Ansible-Git/     the control node
  ansible/                   inventory, host_vars, playbooks
  jenkins/                   the two pipelines
  scripts/                   run-tests.sh
VM2-Application-Server-1/    3 applications
VM3-Application-Server-2/    3 applications
VM4-Application-Server-3/    3 applications
python-monitor/              infra_check.py - the check utility
monitoring/                  Prometheus + Grafana
docs/                        architecture
```

Every application folder has the same shape: `docker-compose.yml`,
`docker/php/Dockerfile`, `docker/nginx/default.conf`, `src/` and a `readme.md`.

---

# Installation

## The machines

| | Role | Address | User |
|---|---|---|---|
| VM1 | control node - Git, Jenkins, Ansible, Prometheus, Grafana | 192.168.0.106 | bartikus |
| VM2 | application server 1 | 192.168.0.169 | verloc |
| VM3 | application server 2 | 192.168.0.159 | blackwell |
| VM4 | application server 3 | 192.168.0.125 | cortana |

These addresses appear in
[`VM1-Jenkins-Ansible-Git/ansible/inventory.ini`](VM1-Jenkins-Ansible-Git/ansible/inventory.ini),
[`python-monitor/targets.json`](python-monitor/targets.json),
[`monitoring/prometheus/prometheus.yml`](monitoring/prometheus/prometheus.yml) and
in the `.env.example` of each application. Give the four machines **static
addresses** - a DHCP lease that expires halfway through a demo means editing all
four places again.

## 1. What each machine needs

**VM1 - control node**

```bash
sudo apt update
sudo apt install -y git ansible rsync python3-pip docker.io docker-compose-v2
```

**VM2, VM3, VM4 - application servers**

```bash
sudo apt install -y docker.io docker-compose-v2 rsync
sudo usermod -aG docker $USER      # then log out and back in

sudo mkdir -p /opt/devops-php-infrastructure /opt/monitoring
sudo chown -R $USER:$USER /opt/devops-php-infrastructure /opt/monitoring
```

`docker compose version` (with a space) has to answer on all four machines - the
playbooks use the v2 syntax. `rsync` has to be installed on VM1 **and** on the
three servers, because that is how the code is distributed.

Those two folders are the only thing on the servers that needs root, and they
need it exactly once. After the `chown`, **no playbook uses `become`** and the
deployment never asks for a sudo password - which is what makes it possible to
run it from Jenkins, unattended. Check it with:

```bash
touch /opt/devops-php-infrastructure/.write-test && rm /opt/devops-php-infrastructure/.write-test
```

If that fails, everything after it fails too.

## 2. SSH keys

Only VM1 talks to the servers, and it does it over SSH:

```bash
ssh-keygen -t ed25519
ssh-copy-id verloc@192.168.0.169
ssh-copy-id blackwell@192.168.0.159
ssh-copy-id cortana@192.168.0.125
```

The users and addresses are in
[`VM1-Jenkins-Ansible-Git/ansible/inventory.ini`](VM1-Jenkins-Ansible-Git/ansible/inventory.ini) -
change them there if yours are different.

## 3. Clone the repository on VM1

```bash
git clone https://github.com/AdrianGoG/devops-php-infrastructure.git
cd devops-php-infrastructure
pip3 install -r python-monitor/requirements.txt
```

The servers never clone anything. VM1 is the only machine that talks to GitHub.

## 4. Check that Ansible reaches the servers

```bash
cd VM1-Jenkins-Ansible-Git/ansible
ansible-playbook playbooks/ping.yml
```

**If this fails, nothing else will work.** Fix the SSH keys before going on.

---

# Bringing the infrastructure online

Everything below runs on VM1, from `VM1-Jenkins-Ansible-Git/ansible`.

## 5. Which applications can be set up, and which cannot

Composer refuses to install on a PHP version that does not satisfy
`composer.json`, so `composer install` cannot run for four of the nine
applications until PHP is raised. Those four carry `blocked: true` in
[`host_vars`](VM1-Jenkins-Ansible-Git/ansible/host_vars/): the playbook copies
their files and starts their containers, but skips the dependencies, the key
generation and the health gate. Nothing has to be passed on the command line.

| Application | PHP in the container | `composer.json` requires | Full setup? |
|---|---|---|---|
| app-api | 7.4 | `^7.2` | yes |
| app-user-dashboard | 8.2 | `^8.2` | yes |
| app-crm | 7.4 | - | yes |
| app-inventory | 8.0 | `^8.0` | yes |
| app-ticket-system | 8.1 | `^8.1` | yes |
| **app-company-website** | 8.2 | `^8.3` | **no - `blocked`** |
| **app-blog** | 8.2 | `^8.3` | **no - `blocked`** |
| **app-file-manager** | 8.2 | `^8.3` | **no - `blocked`** |
| **app-monitor** | 8.2 | `^8.3` | **no - `blocked`** |

Those four are the ones the PHP upgrade fixes. They stay down on purpose until
then - that is the "before" the upgrade is measured against.

## 6. Deploy

```bash
ansible-playbook playbooks/deploy.yml --limit vm2
ansible-playbook playbooks/deploy.yml --limit vm3
ansible-playbook playbooks/deploy.yml --limit vm4
```

One command per server. The `blocked` applications are handled by the playbook,
so nothing has to be selected by hand.

For each application the playbook copies the folder with rsync, creates `.env`
from `.env.example` if it does not exist, runs `docker compose up -d`, generates
the Laravel application key on the first run, installs the dependencies, runs the
migrations, restarts PHP and waits for the health endpoint.

Nothing has to be copied by hand and `docker build` never has to be run:
`docker compose up -d` builds the image the first time it needs one.

The first run takes a few minutes - the `php`, `nginx` and `mysql` images are
downloaded and the PHP images are built. Later runs take seconds.

All three end green. The report at the end names the four blocked applications
explicitly:

```
app-company-website: DOWN - blocked by the PHP version, expected until the upgrade
app-user-dashboard: OK
app-api: OK
```

Five applications answering and four not is the correct state before the upgrade.

## 7. The upgrade - what the whole project is about

```bash
ansible-playbook playbooks/upgrade-php.yml --limit vm4 -e only_blocked=true
ansible-playbook playbooks/deploy.yml --limit vm4 -e after_upgrade=true
```

The first command rewrites `FROM php:8.2-fpm` to `php:8.3-fpm` in each
`docker/php/Dockerfile` and rebuilds. The second one ignores the `blocked` flag -
that is what `after_upgrade=true` means - and now succeeds where it could not
before: Composer is satisfied, the dependencies install, the applications answer.
The health gate is armed again, so if one of them still fails, the playbook says
so.

Same two commands for `app-company-website` on VM2:

```bash
ansible-playbook playbooks/upgrade-php.yml --limit vm2 -e only_blocked=true
ansible-playbook playbooks/deploy.yml --limit vm2 -e after_upgrade=true
```

`only_blocked=true` restricts the upgrade to the applications marked `blocked` -
on VM2 that is `app-company-website` alone, leaving `app-api` on PHP 7.4 where it
belongs for now. Raising *that* one is the other half of the story: it is the
application that needs code changes, not just a newer runtime. See
[app-api/MIGRATION.md](VM2-Application-Server-1/app-api/MIGRATION.md).

Run [`python-monitor/infra_check.py`](python-monitor/infra_check.py) before and
after - the difference between the two reports is the result.

## 8. Set the real passwords

`.env.example` ships with the same credentials as `docker-compose.yml`, so the
first deployment works as it is. Before anything is reachable from outside the
lab, change them on each server:

```bash
nano /opt/devops-php-infrastructure/VM2-Application-Server-1/app-api/src/.env
```

`.env` is never overwritten by a later deployment.

## 9. Monitoring

```bash
cd ~/devops-php-infrastructure/monitoring
docker compose up -d

cd ../VM1-Jenkins-Ansible-Git/ansible
ansible-playbook playbooks/monitoring.yml
```

- Prometheus: <http://192.168.0.106:9090> - `/targets` should show everything UP
- Grafana: <http://192.168.0.106:3000>, user `admin`, password `admin` - **change it**

The dashboard and the datasource are provisioned from files; there is nothing to
click through.

## 10. Jenkins

```bash
sudo apt install -y openjdk-17-jre
# then Jenkins from the official repository
```

The `jenkins` user needs its own SSH keys to the servers, and access to Docker:

```bash
sudo -u jenkins ssh-keygen -t ed25519
sudo -u jenkins ssh-copy-id verloc@192.168.0.169      # and the other two
sudo usermod -aG docker jenkins
sudo systemctl restart jenkins
```

Two jobs, both **Pipeline → Pipeline script from SCM**, pointing at this
repository:

| Job | Script Path | Trigger |
|---|---|---|
| `deploy` | `VM1-Jenkins-Ansible-Git/jenkins/Jenkinsfile.deploy` | polls GitHub every 5 minutes |
| `upgrade-php` | `VM1-Jenkins-Ansible-Git/jenkins/Jenkinsfile.upgrade` | manual, with parameters |

A GitHub webhook needs Jenkins to be reachable from the internet. On a local
network it is not, so the deploy job polls instead. Replace `pollSCM` with
`githubPush()` if you expose Jenkins through a tunnel.

---

# Checking that it all works

```bash
cd ~/devops-php-infrastructure/python-monitor
python3 infra_check.py
```

```
  Infrastructure report - 31.07.2026 10:14:22
  ----------------------------------------------------------------------
  vm2    192.168.0.169    reachable
  vm3    192.168.0.159    reachable
  vm4    192.168.0.125    reachable
  ----------------------------------------------------------------------
  application           srv   php      code   time     state
  app-company-website   vm2   8.2      500    12 ms    error
  app-user-dashboard    vm2   8.2.31   200    52 ms    ok
  ...
```

## What you should see the first time

**Five applications working and four down - and that is correct.**

`app-company-website`, `app-blog`, `app-file-manager` and `app-monitor` run
Laravel 13 on PHP 8.2, one version below what the framework requires. They answer
HTTP 500 until PHP is raised to 8.3. That is the starting point of the migration,
not a broken installation.

---

# The migration

This is what the project is actually about. Raising PHP fixes four applications
and breaks three others - for three completely different reasons.

```bash
cd python-monitor
python3 infra_check.py --report before.json

cd ../VM1-Jenkins-Ansible-Git/ansible
ansible-playbook playbooks/upgrade-php.yml

cd ../../python-monitor
python3 infra_check.py --compare before.json
```

| | Applications | What fixes them |
|---|---|---|
| Fixed by the upgrade | app-company-website, app-blog, app-file-manager, app-monitor | the upgrade itself |
| Unaffected | app-user-dashboard, app-ticket-system | already compatible |
| Broken by the upgrade | **app-crm**, **app-api**, **app-inventory** | source code changes |

The three that break are documented line by line, with the exact diffs:

- [app-crm/MIGRATION.md](VM3-Application-Server-2/app-crm/MIGRATION.md) - three one-line changes
- [app-api/MIGRATION.md](VM2-Application-Server-1/app-api/MIGRATION.md) - six changes
- app-inventory - blocked by Laravel 9, needs a framework upgrade

After fixing the code, `git push` triggers Jenkins, which redeploys, and the
report comes back green.

If the upgrade goes wrong:

```bash
ansible-playbook playbooks/rollback-php.yml --limit vm3
```

---

# Local development

The applications also run locally with [Laravel Herd](https://herd.laravel.com),
each on the PHP version it needs:

```bash
cd VM3-Application-Server-2/app-crm
docker compose up -d mysql          # only the database

cd src
herd link app-crm
herd isolate 7.4 --site=app-crm     # -> http://app-crm.test
```

Then point the check utility at the local sites:

```bash
cd python-monitor
python3 infra_check.py --targets targets.local.json
```

Each application's `readme.md` has its own local setup section.

---

# Documentation

| Document | What it covers |
|---|---|
| [docs/architecture.md](docs/architecture.md) | what each application does and what talks to what |
| [VM1-Jenkins-Ansible-Git/ansible/readme.md](VM1-Jenkins-Ansible-Git/ansible/readme.md) | the playbooks, variables, how the code reaches the servers |
| [VM1-Jenkins-Ansible-Git/jenkins/readme.md](VM1-Jenkins-Ansible-Git/jenkins/readme.md) | the two pipelines and how to set up the jobs |
| [python-monitor/readme.md](python-monitor/readme.md) | the check utility, states, exit codes |
| [monitoring/readme.md](monitoring/readme.md) | Prometheus, Grafana, what is scraped |
| `*/readme.md` | one per application |
| `*/MIGRATION.md` | the PHP 8 incompatibilities and their fixes |

## A note on security

This is a lab. The passwords are in the repository on purpose, so the whole
estate can be brought up in one command, and two applications deliberately run
frameworks that are out of support and carry open security advisories. **Do not
put any of it on a public network.**