<?php

/*
|--------------------------------------------------------------------------
| DevOps project data
|--------------------------------------------------------------------------
|
| All content shown on the presentation site is described here, as a single
| source of truth. The Blade views only iterate over these structures, so
| updating the infrastructure (PHP versions, ports, new applications) happens
| in one place, without touching any markup.
|
| The IP addresses match the Ansible inventory in
| VM1-Jenkins-Ansible-Git/ansible/inventory.ini
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Project identity
    |----------------------------------------------------------------------
    */
    'meta' => [
        'name' => 'DevOps PHP Infrastructure',
        'short_name' => 'DPI',
        'theme' => 'Theme 2',
        'difficulty' => 4,
        'title' => 'A complete CI/CD pipeline for managing and modernising a PHP web infrastructure',
        'tagline' => 'End-to-end automation for a legacy estate: from the Git commit all the way to containerised deployment and production monitoring.',
        'description' => 'ITSchool final DevOps project - an infrastructure of 4 Ubuntu servers hosting 9 PHP applications on different PHP versions, managed by a fully automated CI/CD pipeline built with Jenkins, Docker, Ansible and Python.',
        'repository' => 'https://github.com/AdrianGoG/devops-php-infrastructure',
        'author' => 'Adrian G.',
        'course' => 'ITSchool - DevOps',
    ],

    /*
    |----------------------------------------------------------------------
    | Numbers shown in the hero / stats section
    |----------------------------------------------------------------------
    */
    'stats' => [
        [
            'value' => '4',
            'label' => 'Ubuntu Server machines',
            'hint' => '1 control node + 3 app servers',
        ],
        [
            'value' => '9',
            'label' => 'independent PHP applications',
            'hint' => '3 applications per server',
        ],
        [
            'value' => '5',
            'label' => 'PHP versions running side by side',
            'hint' => '7.4 → 8.3 in containers',
        ],
        [
            'value' => '0',
            'label' => 'manual steps to deploy',
            'hint' => 'a git push does everything',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Main goals of the project
    |----------------------------------------------------------------------
    */
    'objectives' => [
        [
            'icon' => 'git',
            'tone' => 'purple',
            'title' => 'Disciplined versioning',
            'text' => 'Source code hosted on GitHub, dedicated development branches, documented commits and the ability to roll back to any previous version.',
        ],
        [
            'icon' => 'pipeline',
            'tone' => 'cyan',
            'title' => 'Automated delivery',
            'text' => 'Every commit triggers the Jenkins pipeline: checkout, structure validation, tests, build, distribution to the target servers and notification.',
        ],
        [
            'icon' => 'docker',
            'tone' => 'accent',
            'title' => 'Isolation through containers',
            'text' => 'Each application runs in its own container with the exact PHP version it needs, on the same server, with no dependency conflicts.',
        ],
        [
            'icon' => 'ansible',
            'tone' => 'amber',
            'title' => 'Reproducible configuration',
            'text' => 'Ansible playbooks install packages, upgrade PHP versions, rewrite configuration files and restart services identically across all servers.',
        ],
        [
            'icon' => 'python',
            'tone' => 'green',
            'title' => 'Validation before and after',
            'text' => 'A Python utility checks connectivity and the HTTP status of every application, writes logs and prints a centralised infrastructure report.',
        ],
        [
            'icon' => 'chart',
            'tone' => 'cyan',
            'title' => 'Real observability',
            'text' => 'Prometheus collects the metrics, Grafana visualises them, and the ELK stack centralises both application and web server logs.',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Infrastructure: servers and hosted applications
    |----------------------------------------------------------------------
    | status: ok | legacy | pending
    */
    'servers' => [
        [
            'key' => 'vm1',
            'name' => 'VM1 - Control Node',
            'role' => 'CI/CD & automation',
            'host' => null,
            'os' => 'Ubuntu Server 24.04 LTS',
            'stack' => ['Jenkins', 'Ansible', 'Git'],
            'is_controller' => true,
            'summary' => 'The control node of the infrastructure. It runs the Jenkins server that orchestrates the pipeline, owns the Ansible playbooks and holds the SSH keys to the three application servers.',
            'apps' => [],
        ],
        [
            'key' => 'vm2',
            'name' => 'VM2 - Application Server 1',
            'role' => 'Web server & applications',
            'host' => '192.168.0.169',
            'os' => 'Ubuntu Server 24.04 LTS',
            'stack' => ['NGINX', 'Docker', 'Docker Compose'],
            'is_controller' => false,
            'summary' => 'The first application server. It hosts the project presentation site, the user dashboard and the internal API.',
            'apps' => [
                [
                    'name' => 'app-company-website',
                    'title' => 'Presentation site',
                    'php' => '8.3',
                    'framework' => 'Laravel 13',
                    'port' => 8081,
                    'status' => 'ok',
                    'note' => 'The reference application - the very site you are reading now.',
                ],
                [
                    'name' => 'app-user-dashboard',
                    'title' => 'User dashboard',
                    'php' => '8.2',
                    'framework' => 'Laravel + Breeze',
                    'port' => 8082,
                    'status' => 'pending',
                    'note' => 'Authentication, registration and a management panel for users.',
                ],
                [
                    'name' => 'app-api',
                    'title' => 'Internal API',
                    'php' => '7.4',
                    'framework' => 'Legacy PHP',
                    'port' => 8083,
                    'status' => 'legacy',
                    'note' => 'An old service, first in line for the migration to PHP 8.3.',
                ],
            ],
        ],
        [
            'key' => 'vm3',
            'name' => 'VM3 - Application Server 2',
            'role' => 'Web server & applications',
            'host' => '192.168.0.105',
            'os' => 'Ubuntu Server 24.04 LTS',
            'stack' => ['NGINX', 'Docker', 'Docker Compose'],
            'is_controller' => false,
            'summary' => 'The second application server, dedicated to business applications: CRM, inventory management and the ticketing system.',
            'apps' => [
                [
                    'name' => 'app-crm',
                    'title' => 'CRM',
                    'php' => '7.4',
                    'framework' => 'Legacy PHP',
                    'port' => 8081,
                    'status' => 'legacy',
                    'note' => 'Uses syntax that PHP 8 rejects - the source code has to be adapted.',
                ],
                [
                    'name' => 'app-inventory',
                    'title' => 'Inventory management',
                    'php' => '8.0',
                    'framework' => 'PHP + Composer',
                    'port' => 8082,
                    'status' => 'legacy',
                    'note' => 'Dependencies pinned to old versions, resolved during the upgrade.',
                ],
                [
                    'name' => 'app-ticket-system',
                    'title' => 'Ticketing system',
                    'php' => '8.1',
                    'framework' => 'PHP + Composer',
                    'port' => 8083,
                    'status' => 'pending',
                    'note' => 'Compatible today, scheduled to align with the target version.',
                ],
            ],
        ],
        [
            'key' => 'vm4',
            'name' => 'VM4 - Application Server 3',
            'role' => 'Web server & applications',
            'host' => '192.168.0.125',
            'os' => 'Ubuntu Server 24.04 LTS',
            'stack' => ['NGINX', 'Docker', 'Docker Compose'],
            'is_controller' => false,
            'summary' => 'The third application server. All three of its applications run Laravel 13 on a PHP runtime one version too low, so the whole server is down until the upgrade - the opposite problem to the legacy code on VM3.',
            'apps' => [
                [
                    'name' => 'app-blog',
                    'title' => 'Public blog',
                    'php' => '8.2',
                    'framework' => 'Laravel 13',
                    'port' => 8081,
                    'status' => 'blocked',
                    'note' => 'Laravel 13 requires PHP 8.3. The runtime is 8.2, so the application cannot boot.',
                ],
                [
                    'name' => 'app-file-manager',
                    'title' => 'File manager',
                    'php' => '8.2',
                    'framework' => 'Laravel 13',
                    'port' => 8082,
                    'status' => 'blocked',
                    'note' => 'Uploads persisted through a Docker volume. Same version mismatch.',
                ],
                [
                    'name' => 'app-monitor',
                    'title' => 'Monitoring',
                    'php' => '8.2',
                    'framework' => 'Laravel 13',
                    'port' => 8083,
                    'status' => 'blocked',
                    'note' => 'Probes every application of the estate and exposes it to Prometheus. Kubernetes candidate.',
                ],
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Jenkins pipeline stages
    |----------------------------------------------------------------------
    */
    'pipeline' => [
        [
            'stage' => 'Trigger',
            'tool' => 'GitHub Webhook',
            'text' => 'A push to the development branch or a merge into main notifies Jenkins through a webhook. Nothing is started by hand.',
        ],
        [
            'stage' => 'Checkout',
            'tool' => 'Git',
            'text' => 'Jenkins pulls the source code at the exact commit that triggered the build, so the result is reproducible.',
        ],
        [
            'stage' => 'Structure validation',
            'tool' => 'Shell / Python',
            'text' => 'Checks that the required files are present (Dockerfile, docker-compose.yml, composer.json) and that each application follows the expected layout.',
        ],
        [
            'stage' => 'Automated tests',
            'tool' => 'PHPUnit',
            'text' => 'Runs the application test suite. A failing test stops the pipeline before anything is deployed.',
        ],
        [
            'stage' => 'Build',
            'tool' => 'Docker / Composer',
            'text' => 'Builds the Docker image on the declared PHP version and installs the dependencies optimised for production.',
        ],
        [
            'stage' => 'Deployment',
            'tool' => 'SSH / Docker Compose',
            'text' => 'Files are distributed to the target server and the stack is restarted on the new version, using volumes to persist the source code.',
        ],
        [
            'stage' => 'Smoke test',
            'tool' => 'Python',
            'text' => 'The monitoring utility queries the application right after deployment and confirms an HTTP 200 response.',
        ],
        [
            'stage' => 'Notification',
            'tool' => 'E-mail / Discord',
            'text' => 'The pipeline result is sent automatically. On failure it can trigger a rollback to the last working version.',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Short flow shown on the home page
    |----------------------------------------------------------------------
    */
    'flow' => [
        ['title' => 'Commit', 'text' => 'The change is committed on the development branch.'],
        ['title' => 'Webhook', 'text' => 'GitHub notifies the Jenkins server instantly.'],
        ['title' => 'Test', 'text' => 'PHPUnit validates the application before any build.'],
        ['title' => 'Build', 'text' => 'The Docker image is built on the target PHP version.'],
        ['title' => 'Deploy', 'text' => 'The stack is updated on the application server.'],
        ['title' => 'Verify', 'text' => 'The Python script confirms the HTTP status codes.'],
    ],

    /*
    |----------------------------------------------------------------------
    | Technologies used
    |----------------------------------------------------------------------
    */
    'technologies' => [
        [
            'name' => 'Git & GitHub',
            'category' => 'Version control',
            'icon' => 'git',
            'tone' => 'purple',
            'role' => 'The full history of the infrastructure. Separate branches for development, documented commits and a way back to any previous version.',
            'details' => ['main + development branches', 'documented commits', 'rollback by tag'],
        ],
        [
            'name' => 'Jenkins',
            'category' => 'CI/CD',
            'icon' => 'pipeline',
            'tone' => 'cyan',
            'role' => 'The orchestrator of the whole flow. A declarative pipeline defined in a Jenkinsfile, triggered automatically by GitHub webhooks.',
            'details' => ['declarative pipeline', 'webhook trigger', 'notifications on finish'],
        ],
        [
            'name' => 'Docker',
            'category' => 'Containerisation',
            'icon' => 'docker',
            'tone' => 'accent',
            'role' => 'Runs applications on different PHP versions at the same time, on the same server, using the official PHP-FPM images.',
            'details' => ['official php:*-fpm images', 'volumes for source code', 'one stack per application'],
        ],
        [
            'name' => 'Docker Compose',
            'category' => 'Containerisation',
            'icon' => 'layers',
            'tone' => 'accent',
            'role' => 'Describes the services of each application - NGINX, PHP-FPM, MySQL - and starts them as one reproducible stack.',
            'details' => ['nginx + php-fpm + mysql', 'dedicated port per application', 'persistent volumes'],
        ],
        [
            'name' => 'Ansible',
            'category' => 'Configuration management',
            'icon' => 'ansible',
            'tone' => 'amber',
            'role' => 'Applies the same configuration to every server: packages, PHP versions, configuration files and service restarts.',
            'details' => ['inventory of 3 hosts', 'PHP upgrade playbook', 'server health checks'],
        ],
        [
            'name' => 'Python',
            'category' => 'Automation',
            'icon' => 'python',
            'tone' => 'green',
            'role' => 'The monitoring utility runs before and after every update, checking connectivity and the HTTP status of all nine applications.',
            'details' => ['connectivity checks', 'centralised report', 'timestamped logs'],
        ],
        [
            'name' => 'Prometheus & Grafana',
            'category' => 'Monitoring',
            'icon' => 'chart',
            'tone' => 'cyan',
            'role' => 'Collect and visualise the infrastructure metrics: availability, response time, CPU and memory usage.',
            'details' => ['node_exporter on every VM', 'dashboards per server', 'alerts on downtime'],
        ],
        [
            'name' => 'ELK Stack',
            'category' => 'Logging',
            'icon' => 'logs',
            'tone' => 'purple',
            'role' => 'Centralises application and web server logs, searchable and filterable in Kibana.',
            'details' => ['Elasticsearch + Logstash + Kibana', 'NGINX and application logs', 'correlation per server'],
        ],
        [
            'name' => 'Kubernetes',
            'category' => 'Extension',
            'icon' => 'kubernetes',
            'tone' => 'accent',
            'role' => 'An extension of the project: one of the applications is published to a cluster with its own Deployment, Service, ConfigMap and Namespace.',
            'details' => ['Deployment + Service', 'ConfigMap for configuration', 'dedicated Namespace'],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Monitoring: what is being watched
    |----------------------------------------------------------------------
    */
    'monitoring' => [
        'metrics' => [
            ['title' => 'Application availability', 'text' => 'Each of the nine applications is polled regularly; downtime raises an alert.'],
            ['title' => 'Response time', 'text' => 'HTTP latency, tracked per application and per web server.'],
            ['title' => 'CPU usage', 'text' => 'Processor load on every server, collected through node_exporter.'],
            ['title' => 'Memory usage', 'text' => 'Memory consumed by the containers and by the host system.'],
            ['title' => 'Application logs', 'text' => 'PHP errors and Laravel exceptions, shipped to Elasticsearch.'],
            ['title' => 'Web server logs', 'text' => 'NGINX access and error logs, centralised and filterable in Kibana.'],
        ],
        'http_codes' => [
            ['code' => '200', 'tone' => 'ok', 'label' => 'OK', 'text' => 'The application responds normally. The expected state after every successful deployment.'],
            ['code' => '404', 'tone' => 'warn', 'label' => 'Not Found', 'text' => 'A missing route, or files missing after an incomplete distribution.'],
            ['code' => '500', 'tone' => 'danger', 'label' => 'Server Error', 'text' => 'An error inside the application - usually a syntax incompatibility with the new PHP version.'],
            ['code' => '503', 'tone' => 'danger', 'label' => 'Service Unavailable', 'text' => 'The PHP-FPM container fails to start after the upgrade. The central scenario of this project, resolved by adapting the source code.'],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | The legacy code migration process
    |----------------------------------------------------------------------
    */
    'migration' => [
        ['title' => 'Automatic backup', 'text' => 'Before any upgrade, the Ansible playbook backs up the application code and its database.'],
        ['title' => 'PHP upgrade', 'text' => 'Ansible changes the PHP version of the container image and rebuilds the stack.'],
        ['title' => 'Error detection', 'text' => 'The Python script reports every application answering with 500 or 503 after the upgrade.'],
        ['title' => 'Code adaptation', 'text' => 'Syntax incompatibilities and deprecated calls are fixed in the source code.'],
        ['title' => 'Commit & push', 'text' => 'The changes are committed on the development branch and pushed to GitHub.'],
        ['title' => 'Automatic redeployment', 'text' => 'The webhook triggers Jenkins, the application is rebuilt and returns to HTTP 200.'],
    ],

    /*
    |----------------------------------------------------------------------
    | Additional objectives (advanced level)
    |----------------------------------------------------------------------
    */
    'extras' => [
        'automatic rollback to the last working version',
        'e-mail or Discord notifications when the pipeline finishes',
        'automatic backup before every PHP upgrade',
        'Blue-Green or Rolling Update deployments',
        'vulnerability scanning of the Docker images',
        'additional automated tests for each application',
        'integration of a private Docker registry',
    ],

    /*
    |----------------------------------------------------------------------
    | Portfolio deliverables
    |----------------------------------------------------------------------
    */
    'deliverables' => [
        ['title' => 'Source code', 'text' => 'All nine PHP applications, organised per server in the repository.'],
        ['title' => 'Jenkinsfile', 'text' => 'The declarative definition of the CI/CD pipeline.'],
        ['title' => 'Dockerfile & Compose', 'text' => 'The containerisation configuration of every application.'],
        ['title' => 'Ansible playbooks', 'text' => 'Inventory and playbooks for administering the servers.'],
        ['title' => 'Python scripts', 'text' => 'The monitoring utility and the reports it generates.'],
        ['title' => 'Documentation', 'text' => 'Installation, configuration and operations guide for the infrastructure.'],
    ],

    /*
    |----------------------------------------------------------------------
    | Site navigation
    |----------------------------------------------------------------------
    */
    'navigation' => [
        ['route' => 'home', 'label' => 'Home'],
        ['route' => 'infrastructure', 'label' => 'Infrastructure'],
        ['route' => 'pipeline', 'label' => 'CI/CD Pipeline'],
        ['route' => 'technologies', 'label' => 'Technologies'],
        ['route' => 'monitoring', 'label' => 'Monitoring'],
    ],

];
