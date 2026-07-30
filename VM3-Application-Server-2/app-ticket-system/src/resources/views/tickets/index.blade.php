@extends('layouts.app')

@section('title', 'Queue')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Ticket queue</h1>
            <p class="page-subtitle mb-0">
                {{ $tickets->count() }} {{ Str::plural('ticket', $tickets->count()) }} shown
                @if ($search !== '' || $status !== '' || $priority !== '')
                    · <a href="{{ route('tickets.index') }}">clear filters</a>
                @endif
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="pill">Total: {{ $totalTickets }}</span>
            <span class="pill {{ $unresolvedCount > 0 ? 'pill-warn' : 'pill-ok' }}">
                Unresolved: {{ $unresolvedCount }}
            </span>
            <span class="pill {{ $urgentCount > 0 ? 'pill-danger' : 'pill-ok' }}">
                Urgent: {{ $urgentCount }}
            </span>
        </div>
    </div>

    <form method="GET" action="{{ route('tickets.index') }}" class="card-surface card-pad mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="q" class="form-label">Search</label>
                <input type="text" id="q" name="q" value="{{ $search }}"
                       class="form-control" placeholder="Reference, subject or requester">
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Ticket::STATUSES as $one)
                        <option value="{{ $one }}" @selected($status === $one)>
                            {{ ucfirst(str_replace('_', ' ', $one)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="priority" class="form-label">Priority</label>
                <select id="priority" name="priority" class="form-select">
                    <option value="">All</option>
                    @foreach (\App\Models\Ticket::PRIORITIES as $one)
                        <option value="{{ $one }}" @selected($priority === $one)>{{ ucfirst($one) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-ghost w-100">Filter</button>
            </div>
        </div>
    </form>

    <div class="card-surface">
        <div class="table-responsive">
            <table class="table table-crm align-middle">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Subject</th>
                        <th>Requester</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assignee</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td class="mono cell-strong">{{ $ticket->reference }}</td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="cell-strong">
                                    {{ Str::limit($ticket->subject, 60) }}
                                </a>
                                <span class="text-dim small d-block">
                                    opened {{ $ticket->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td class="text-dim small">{{ $ticket->requester }}</td>
                            <td><span class="pill {{ $ticket->priorityClass() }}">{{ $ticket->priority }}</span></td>
                            <td><span class="pill {{ $ticket->statusClass() }}">{{ $ticket->statusLabel() }}</span></td>
                            <td class="text-dim">{{ $ticket->assignee ?? '—' }}</td>
                            <td class="text-end">
                                @if ($ticket->isUnresolved())
                                    <form method="POST" action="{{ route('tickets.close', $ticket) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-ghost btn-sm">Close</button>
                                    </form>
                                @endif

                                <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-ghost btn-sm">Edit</a>

                                <form method="POST" action="{{ route('tickets.destroy', $ticket) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete {{ $ticket->reference }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-soft btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-dim py-4">
                                No ticket matches the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
