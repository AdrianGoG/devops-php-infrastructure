<?php

use App\Http\Controllers\MonitorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| app-monitor · VM4 Application Server 3 · Laravel 13 · port 8083
|
| The container runs PHP 8.2 on purpose, one version below what Laravel 13
| requires, so the application answers HTTP 500 until the Ansible playbook
| raises it to 8.3. See readme.md.
|
*/

Route::get('/', [MonitorController::class, 'index'])->name('monitor.index');
Route::get('/metrics', [MonitorController::class, 'metrics'])->name('monitor.metrics');

/*
| Its own health endpoint, with the same contract as everything it probes.
| app-monitor deliberately does not probe itself: if it were down, nothing here
| would be running to report it.
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'application' => 'app-monitor',
        'server' => 'VM4-Application-Server-3',
        'php' => PHP_VERSION,
        'laravel' => app()->version(),
        'environment' => app()->environment(),
        'probes_configured' => count((array) config('estate.applications')),
        'checked_at' => now()->toIso8601String(),
    ]);
})->name('health');