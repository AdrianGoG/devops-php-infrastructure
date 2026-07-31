<?php

/*
|--------------------------------------------------------------------------
| The applications app-monitor probes
|--------------------------------------------------------------------------
|
| Every application of the infrastructure exposes a health endpoint with the
| same contract, which is what makes one prober enough for all of them.
|
| By default the URLs are built from the address of each server, which is the
| shape they have in production. Any single one can be overridden through its
| own ESTATE_URL_* variable - that is how the local setup points at the Herd
| .test domains instead. See .env.example.
|
*/

$vm2 = env('ESTATE_BASE_VM2', 'http://192.168.0.169');
$vm3 = env('ESTATE_BASE_VM3', 'http://192.168.0.159');
$vm4 = env('ESTATE_BASE_VM4', 'http://192.168.0.125');

return [

    // How long to wait for one health endpoint before calling it unreachable.
    'timeout' => env('ESTATE_TIMEOUT', 3),

    // How long a probe result is cached, so a page refresh does not hammer the
    // whole estate.
    'cache_seconds' => env('ESTATE_CACHE_SECONDS', 10),

    'applications' => [
        [
            'name' => 'app-company-website',
            'server' => 'vm2',
            'url' => env('ESTATE_URL_COMPANY_WEBSITE', $vm2.':8081/health'),
        ],
        [
            'name' => 'app-user-dashboard',
            'server' => 'vm2',
            'url' => env('ESTATE_URL_USER_DASHBOARD', $vm2.':8082/health'),
        ],
        [
            'name' => 'app-api',
            'server' => 'vm2',
            'url' => env('ESTATE_URL_API', $vm2.':8083/api/health'),
        ],
        [
            'name' => 'app-crm',
            'server' => 'vm3',
            'url' => env('ESTATE_URL_CRM', $vm3.':8081/health.php'),
        ],
        [
            'name' => 'app-inventory',
            'server' => 'vm3',
            'url' => env('ESTATE_URL_INVENTORY', $vm3.':8082/health'),
        ],
        [
            'name' => 'app-ticket-system',
            'server' => 'vm3',
            'url' => env('ESTATE_URL_TICKET_SYSTEM', $vm3.':8083/health'),
        ],
        [
            'name' => 'app-blog',
            'server' => 'vm4',
            'url' => env('ESTATE_URL_BLOG', $vm4.':8081/health'),
        ],
        [
            'name' => 'app-file-manager',
            'server' => 'vm4',
            'url' => env('ESTATE_URL_FILE_MANAGER', $vm4.':8082/health'),
        ],

        // app-monitor does not probe itself: if it were down, nothing here
        // would be running to report it.
    ],

];