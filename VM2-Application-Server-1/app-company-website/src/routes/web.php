<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Presentation site routes
|--------------------------------------------------------------------------
|
| app-company-website · VM2 Application Server 1
|
| The route names are used by config/project.php (navigation) and by the
| feature tests Jenkins runs before every deployment.
|
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/infrastructure', [PageController::class, 'infrastructure'])->name('infrastructure');
Route::get('/pipeline', [PageController::class, 'pipeline'])->name('pipeline');
Route::get('/technologies', [PageController::class, 'technologies'])->name('technologies');
Route::get('/monitoring', [PageController::class, 'monitoring'])->name('monitoring');

/*
| Endpoint polled by the Python monitoring utility and by the smoke test stage
| of the Jenkins pipeline. Laravel already exposes /up, but this one also
| reports the PHP version the application is running on - the key piece of
| information after an Ansible-driven upgrade.
*/
Route::get('/health', [PageController::class, 'health'])->name('health');
