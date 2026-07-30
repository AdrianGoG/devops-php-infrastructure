<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Infrastructure registry (app-api)
    |--------------------------------------------------------------------------
    |
    | The dashboard reads the servers, the applications and the deployment log
    | from app-api, which runs on the same server (VM2) on port 8083.
    |
    */

    'base_url' => env('REGISTRY_BASE_URL', 'http://127.0.0.1:8083'),

    // Seconds to wait for the API before treating it as unreachable.
    'timeout' => env('REGISTRY_TIMEOUT', 4),

    // How long a successful response is cached, so one page render does not
    // trigger several identical requests.
    'cache_seconds' => env('REGISTRY_CACHE_SECONDS', 15),

];
