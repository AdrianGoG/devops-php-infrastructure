<?php

use App\Http\Controllers\TicketController;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| app-ticket-system · VM3 Application Server 2 · PHP 8.1 · Laravel 10 · port 8083
|
*/

Route::get('/', fn () => redirect()->route('tickets.index'));

Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
Route::patch('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

/*
| Health endpoint with the same contract as the other applications of the
| estate, so the Python monitoring utility treats all nine the same way.
*/
Route::get('/health', function () {
    $database = 'ok';
    $tickets = null;
    $unresolved = null;

    try {
        $tickets = Ticket::count();
        $unresolved = Ticket::unresolved()->count();
    } catch (\Throwable $exception) {
        $database = 'unavailable';
        report($exception);
    }

    return response()->json([
        'status' => $database === 'ok' ? 'ok' : 'degraded',
        'application' => 'app-ticket-system',
        'server' => 'VM3-Application-Server-2',
        'php' => PHP_VERSION,
        'laravel' => app()->version(),
        'environment' => app()->environment(),
        'database' => $database,
        'tickets' => $tickets,
        'unresolved' => $unresolved,
        'checked_at' => now()->toIso8601String(),
    ], $database === 'ok' ? 200 : 503);
})->name('health');
