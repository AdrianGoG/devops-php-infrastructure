<?php

use App\Http\Controllers\PostController;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| app-blog · VM4 Application Server 3 · Laravel 13 · port 8081
|
| The container runs PHP 8.2 on purpose, one version below what Laravel 13
| requires, so the application answers HTTP 500 until the Ansible playbook
| raises it to 8.3. See readme.md.
|
*/

Route::get('/', [PostController::class, 'index'])->name('posts.index');

// The editor routes come before the {post} route, otherwise "manage" and
// "create" would be read as slugs.
Route::get('/posts/manage', [PostController::class, 'manage'])->name('posts.manage');
Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

/*
| Health endpoint with the same contract as the other applications of the estate.
*/
Route::get('/health', function () {
    $database = 'ok';
    $posts = null;
    $published = null;

    try {
        $posts = Post::count();
        $published = Post::published()->count();
    } catch (\Throwable $exception) {
        $database = 'unavailable';
        report($exception);
    }

    return response()->json([
        'status' => $database === 'ok' ? 'ok' : 'degraded',
        'application' => 'app-blog',
        'server' => 'VM4-Application-Server-3',
        'php' => PHP_VERSION,
        'laravel' => app()->version(),
        'environment' => app()->environment(),
        'database' => $database,
        'posts' => $posts,
        'published' => $published,
        'checked_at' => now()->toIso8601String(),
    ], $database === 'ok' ? 200 : 503);
})->name('health');