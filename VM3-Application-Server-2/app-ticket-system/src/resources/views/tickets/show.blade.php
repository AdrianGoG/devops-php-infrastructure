@extends('layouts.app')

@section('title', $ticket->reference)

@section('content')
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <span class="mono text-dim">{{ $ticket->reference }}</span>
            <h1 class="page-title">{{ $ticket->subject }}</h1>
            <p class="page-subtitle mb-0">
                Opened {{ $ticket->created_at->diffForHumans() }} by {{ $ticket->requester }}
                @if ($ticket->resolved_at)
                    · resolved {{ $ticket->resolved_at->diffForHumans() }}
                @endif
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-ghost btn-sm">Edit</a>
            <a href="{{ route('tickets.index') }}" class="btn btn-ghost btn-sm">Back to queue</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-surface card-pad h-100">
                <h2 class="card-title-sm">Description</h2>
                <p class="card-text-sm" style="white-space: pre-line;">{{ $ticket->description }}</p>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-surface card-pad h-100">
                <h2 class="card-title-sm mb-3">Details</h2>

                <div class="table-responsive">
                    <table class="table table-crm">
                        <tbody>
                            <tr>
                                <td>Status</td>
                                <td class="text-end">
                                    <span class="pill {{ $ticket->statusClass() }}">{{ $ticket->statusLabel() }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Priority</td>
                                <td class="text-end">
                                    <span class="pill {{ $ticket->priorityClass() }}">{{ $ticket->priority }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Assignee</td>
                                <td class="text-end cell-strong">{{ $ticket->assignee ?? 'Unassigned' }}</td>
                            </tr>
                            <tr>
                                <td>Last update</td>
                                <td class="text-end text-dim">{{ $ticket->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($ticket->isUnresolved())
                    <form method="POST" action="{{ route('tickets.close', $ticket) }}" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-accent w-100">Close this ticket</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
