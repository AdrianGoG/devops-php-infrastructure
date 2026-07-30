<?php

use App\Http\Controllers\FileController;
use App\Models\StoredFile;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| app-file-manager · VM4 Application Server 3 · Laravel 13 · port 8082
|
| The container runs PHP 8.2 on purpose, one version below what Laravel 13
| requires, so the application answers HTTP 500 until the Ansible playbook
| raises it to 8.3. See readme.md.
|
*/

Route::get('/', [FileController::class, 'index'])->name('files.index');
Route::post('/files', [FileController::class, 'store'])->name('files.store');
Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

/*
| Health endpoint with the same contract as the other applications of the estate.
| It also reports whether the uploads volume is writable, which is the failure
| this application is most likely to hit after a container rebuild.
*/
Route::get('/health', function () {
    $database = 'ok';
    $files = null;
    $bytes = null;

    try {
        $files = StoredFile::count();
        $bytes = (int) StoredFile::sum('size');
    } catch (\Throwable $exception) {
        $database = 'unavailable';
        report($exception);
    }

    $storage = is_writable(storage_path('app/uploads')) ? 'writable' : 'not-writable';

    $healthy = $database === 'ok' && $storage === 'writable';

    return response()->json([
        'status' => $healthy ? 'ok' : 'degraded',
        'application' => 'app-file-manager',
        'server' => 'VM4-Application-Server-3',
        'php' => PHP_VERSION,
        'laravel' => app()->version(),
        'environment' => app()->environment(),
        'database' => $database,
        'storage' => $storage,
        'files' => $files,
        'bytes_stored' => $bytes,
        'checked_at' => now()->toIso8601String(),
    ], $healthy ? 200 : 503);
})->name('health');