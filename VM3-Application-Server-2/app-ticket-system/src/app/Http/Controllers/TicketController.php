<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The whole application: a ticket queue with filters and the usual
 * create / edit / delete, plus a one click "close".
 */
class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $priority = (string) $request->query('priority', '');

        if (! in_array($status, Ticket::STATUSES, true)) {
            $status = '';
        }

        if (! in_array($priority, Ticket::PRIORITIES, true)) {
            $priority = '';
        }

        $tickets = Ticket::query()
            ->when($search !== '', fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhere('requester', 'like', '%'.$search.'%');
            }))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($priority !== '', fn ($query) => $query->where('priority', $priority))
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderByDesc('id')
            ->get();

        return view('tickets.index', [
            'tickets' => $tickets,
            'search' => $search,
            'status' => $status,
            'priority' => $priority,
            'totalTickets' => Ticket::count(),
            'unresolvedCount' => Ticket::unresolved()->count(),
            'urgentCount' => Ticket::unresolved()->where('priority', 'urgent')->count(),
        ]);
    }

    public function create(): View
    {
        return view('tickets.form', [
            'ticket' => new Ticket(['priority' => 'normal', 'status' => 'open']),
        ]);
    }

    public function store(TicketRequest $request): RedirectResponse
    {
        $ticket = new Ticket($request->validated());
        $ticket->reference = Ticket::nextReference();
        $ticket->save();

        return redirect()->route('tickets.index')
            ->with('status', 'Ticket '.$ticket->reference.' created.');
    }

    public function show(Ticket $ticket): View
    {
        return view('tickets.show', ['ticket' => $ticket]);
    }

    public function edit(Ticket $ticket): View
    {
        return view('tickets.form', ['ticket' => $ticket]);
    }

    public function update(TicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validated();

        // Stamp the moment a ticket leaves the queue, and clear it if the ticket
        // is reopened.
        $wasUnresolved = $ticket->isUnresolved();
        $becomesResolved = in_array($data['status'], ['resolved', 'closed'], true);

        $ticket->fill($data);

        if ($wasUnresolved && $becomesResolved) {
            $ticket->resolved_at = now();
        } elseif (! $becomesResolved) {
            $ticket->resolved_at = null;
        }

        $ticket->save();

        return redirect()->route('tickets.index')
            ->with('status', 'Ticket '.$ticket->reference.' updated.');
    }

    /**
     * Close a ticket without going through the whole form.
     */
    public function close(Ticket $ticket): RedirectResponse
    {
        // resolved_at is deliberately not fillable - it must never come from a
        // form - so it is assigned directly instead of through update().
        $ticket->status = 'closed';
        $ticket->resolved_at = now();
        $ticket->save();

        return redirect()->route('tickets.index')
            ->with('status', 'Ticket '.$ticket->reference.' closed.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $reference = $ticket->reference;

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('status', 'Ticket '.$reference.' deleted.');
    }
}
