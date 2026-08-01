# jenkins

Pipelines that run on VM1.

| File | What it does |
|---|---|
| `Jenkinsfile.upgrade` | Upgrades the PHP version of the whole infrastructure through Ansible |

## Jenkinsfile.upgrade

The pipeline does six things, in this order:

1. **Checkout** - takes the code from GitHub.
2. **Check the servers** - `ansible-playbook playbooks/ping.yml`, so the upgrade
   does not start if a server is not answering.
3. **Status before** - `infra_check.py --report before.json`.
4. **Upgrade PHP** - `ansible-playbook playbooks/upgrade-php.yml`.
5. **Status after** - `infra_check.py --report after.json --compare before.json`.
6. **Rollback** - only if something broke *and* the parameter is checked.

The two status checks are the point. Without them the pipeline can only say that
Ansible finished, not that the applications still work.

### Parameters

| Parameter | Default | What it is |
|---|---|---|
| `PHP_VERSION` | `8.3` | The version to upgrade to |
| `SERVER` | `all` | `all`, `vm2`, `vm3` or `vm4` |
| `ROLLBACK_IF_BROKEN` | unchecked | Put the old version back if an application stops working |

### Why the build becomes UNSTABLE and not FAILED

When an application stops working after the upgrade, the build is marked
**UNSTABLE** (yellow), not FAILED (red).

That is on purpose. In this project an application breaking after a PHP upgrade
is the *expected* result for the legacy code - `app-crm` and `app-api` are
written exactly so that this happens. It is the beginning of the work, not a
failure of the pipeline: you read the report, fix the source code, commit, and
the deploy pipeline puts the fixed version back.

A red build would say "the automation is broken", which is not true. Yellow says
"the automation worked, now there is code to fix", which is what happened.

Rollback is therefore **off by default**. Turning it on undoes the upgrade, which
is only what you want if you were not expecting anything to break.

### Setting up the job

1. **New Item** -> **Pipeline**, name it `upgrade-php`.
2. Check **This project is parameterised** - Jenkins reads the parameters from
   the file on the first run, so you can also just run it once and let it
   populate them.
3. **Pipeline** -> **Pipeline script from SCM**
   - SCM: Git
   - Repository URL: `https://github.com/AdrianGoG/devops-php-infrastructure.git`
   - Branch: `*/main`
   - **Script Path**: `VM1-Jenkins-Ansible-Git/jenkins/Jenkinsfile.upgrade`
4. Save, then **Build with Parameters**.

This job is **not** triggered by a webhook. Upgrading the PHP version of the
whole estate is a decision, not something that should happen because someone
pushed a commit. The deployment pipeline is the one that reacts to pushes.

### What VM1 needs

```bash
sudo apt install ansible python3 python3-pip
pip3 install -r python-monitor/requirements.txt
```

Plus SSH keys from the Jenkins user to `verloc@192.168.0.169`,
`blackwell@192.168.0.105` and `cortana@192.168.0.125` - the same keys Ansible
uses. Check with:

```bash
cd VM1-Jenkins-Ansible-Git/ansible
ansible-playbook playbooks/ping.yml
```

### Artefacts

Every build keeps `before.json`, `after.json` and the monitor log. Those two
files are the proof of what the upgrade did - which applications were working
before, which are working after, and on what PHP version each one answers.

## Not written yet

- `Jenkinsfile.deploy` - the pipeline triggered by a push: tests, build, deploy,
  smoke test, notification.
- E-mail or Discord notification at the end of a build.