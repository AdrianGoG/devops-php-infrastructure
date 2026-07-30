# ansible

Playbooks for the three application servers, run from VM1.

## Files

| File | What it is |
|---|---|
| `ansible.cfg` | Uses `inventory.ini`, no host key checking |
| `inventory.ini` | The three servers and their users |
| `host_vars/vm2.yml` … `vm4.yml` | The applications of each server: folder, port, health endpoint |
| `playbooks/ping.yml` | Checks that the servers answer |
| `playbooks/deploy.yml` | Copies the applications from VM1 to the servers and starts them |
| `playbooks/upgrade-php.yml` | Changes the PHP version and rebuilds the containers |
| `playbooks/rollback-php.yml` | Puts back the previous version |

## How the code gets onto the servers

**The code is not inside the Docker images.** Every application mounts its `src`
folder into the container:

```yaml
php:
  volumes:
    - ./src:/var/www/html
```

So the image only contains PHP and its extensions, and the container reads the
code straight off the server's disk. Deploying means **copying files onto the
server**, not rebuilding an image.

```
you  --git push-->  GitHub  --Jenkins checkout-->  VM1  --rsync-->  VM2 / VM3 / VM4
                                                                        |
                                                              ./src mounted into
                                                                  the container
```

Only VM1 talks to GitHub. `deploy.yml` copies each application folder to the
server that hosts it, so the servers need no GitHub access and each one receives
only its own three applications.

That also means a code change needs no image rebuild - it is a file copy and a
`docker compose restart php`, which takes seconds. A rebuild is only needed when
the Dockerfile itself changes, and that is what `upgrade-php.yml` does.

## Deploying

```bash
# everything
ansible-playbook playbooks/deploy.yml

# one server
ansible-playbook playbooks/deploy.yml --limit vm3

# one application
ansible-playbook playbooks/deploy.yml -e '{"only_apps": ["app-crm"]}'

# from the Jenkins checkout instead of this repository
ansible-playbook playbooks/deploy.yml -e repo_dir=$WORKSPACE
```

For each application:

1. `rsync` the folder to the server, deleting files that were removed.
2. Create `.env` from `.env.example` **only if it does not exist** - the `.env`
   on the server has the real passwords and is never overwritten.
3. `docker compose up -d`.
4. Run the setup commands from `host_vars` inside the php container -
   `composer install`, `php artisan migrate --force`.
5. `docker compose restart php`.
6. Wait for the health endpoint, and fail if it does not answer.

### What is never copied

`vendor/`, `node_modules/`, `.env`, `storage/logs/`, the framework caches and any
SQLite file. `vendor/` is rebuilt on the server by `composer install`, which is
why it must not be deleted by the rsync.

### Requirements

`ansible.posix.synchronize` needs the collection, which the Ubuntu `ansible`
package already includes. If not:

```bash
ansible-galaxy collection install ansible.posix
```

rsync has to be installed on VM1 **and** on the three servers.

### The first deployment

Nothing has to be copied by hand, and `docker build` never has to be run:
`docker compose up -d` builds the image the first time it needs one, because the
compose file says `build:` and not `image:`.

The application key is generated automatically the first time - the playbook
looks for an empty `APP_KEY=` in `.env` and only then runs `key:generate`, so a
later deployment never invalidates existing sessions.

The database credentials in `.env.example` already match the ones in
`docker-compose.yml`, so the first deployment works as it is. Change them on the
server before anything is reachable from outside the lab; `.env` is never
overwritten afterwards.

### VM4 needs two passes

The three applications on VM4 run Laravel 13 on PHP 8.2, and Composer refuses to
install on a PHP version that does not satisfy `composer.json`. Their first
deployment therefore has to skip the setup commands:

```bash
ansible-playbook playbooks/deploy.yml --limit vm4 -e skip_setup=true
ansible-playbook playbooks/upgrade-php.yml --limit vm4
ansible-playbook playbooks/deploy.yml --limit vm4
```

Copy the files, raise PHP to 8.3, then deploy properly. VM2 and VM3 need none of
this - they deploy in one pass.

## Before running anything

```bash
cd VM1-Jenkins-Ansible-Git/ansible
ansible-playbook playbooks/ping.yml
```

If that fails, nothing else will work: the SSH keys are not set up.

## Upgrading PHP

```bash
# everything, to 8.3
ansible-playbook playbooks/upgrade-php.yml

# only the third server
ansible-playbook playbooks/upgrade-php.yml --limit vm3

# only one application
ansible-playbook playbooks/upgrade-php.yml -e '{"only_apps": ["app-crm"]}'

# a different version
ansible-playbook playbooks/upgrade-php.yml -e php_version=8.4
```

What it does for each application:

1. Copies `docker/php/Dockerfile` to `Dockerfile.backup`.
2. Replaces the `FROM php:X-fpm` line with the version asked for.
3. Runs `docker compose up -d --build`, but only where the file actually changed.
4. Waits up to 60 seconds for the application to answer 200 on its health
   endpoint.
5. Prints `OK` or `STILL DOWN` for each one, then fails if any is still down.

Step 5 is why the playbook is useful: it does not just change a file, it says
whether the estate survived.

## Rolling back

```bash
ansible-playbook playbooks/rollback-php.yml
ansible-playbook playbooks/rollback-php.yml --limit vm3
```

It puts back `Dockerfile.backup` and rebuilds. An application without a backup is
skipped with a message.

## Variables

| Variable | Default | What it is |
|---|---|---|
| `php_version` | `8.3` | The version to install |
| `project_dir` | `/opt/devops-php-infrastructure` | Where the repository is on the servers |
| `only_apps` | `[]` (all) | Which applications to touch |
| `health_retries` / `health_delay` | 12 / 5 | How long to wait after the rebuild |

**Change `project_dir` if the repository is somewhere else on the servers.** It is
the one value that has to match your setup.

## Adding an application

Add it to `host_vars/` for its server:

```yaml
  - name: app-new
    dir: VM3-Application-Server-2/app-new
    port: 8084
    health: /health
```

The playbooks pick it up automatically. The same list also has to be added to
`python-monitor/targets.json` for the monitor to check it.

## Not written yet

Playbooks for the rest of point 2.4 of the project: installing packages
(Docker, nginx), changing configuration files and restarting services. Only the
PHP upgrade and its rollback exist for now.