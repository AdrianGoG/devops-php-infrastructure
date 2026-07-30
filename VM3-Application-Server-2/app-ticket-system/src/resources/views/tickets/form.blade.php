@extends('layouts.app')

@section('title', $ticket->exists ? 'Edit '.$ticket->reference : 'New ticket')

@section('content')
    <div class="d-flex align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                {{ $ticket->exists ? 'Edit '.$ticket->reference : 'New ticket' }}
            </h1>
            <p class="page-subtitle mb-0">
                @if ($ticket->exists)
                    Opened {{ $ticket->created_at->diffForHumans() }}.
                @else
                    The reference is assigned automatically.
                @endif
            </p>
        </div>
        <a href="{{ route('tickets.index') }}" class="btn btn-ghost btn-sm">Back to queue</a>
    </div>

    @if ($errors->any())
        <div class="alert-soft alert-soft-err mb-4">
            The form has {{ $errors->count() }} {{ Str::plural('error', $errors->count()) }}.
            Please check the fields below.
        </div>
    @endif

    <form method="POST"
          action="{{ $ticket->exists ? route('tickets.update', $ticket) : route('tickets.store') }}"
          class="card-surface card-pad" novalidate>
        @csrf
        @if ($ticket->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-12">
                <label for="subject" class="form-label">Subject *</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject', $ticket->subject) }}"
                       class="form-control @error('subject') is-invalid @enderror">
                @error('subject')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="requester" class="form-label">Requester email *</label>
                <input type="email" id="requester" name="requester" value="{{ old('requester', $ticket->requester) }}"
                       class="form-control @error('requester') is-invalid @enderror" placeholder="name@example.com">
                @error('requester')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="assignee" class="form-label">Assignee</label>
                <input type="text" id="assignee" name="assignee" value="{{ old('assignee', $ticket->assignee) }}"
                       class="form-control @error('assignee') is-invalid @enderror" placeholder="Unassigned">
                @error('assignee')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="priority" class="form-label">Priority *</label>
                <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror">
                    @foreach (\App\Models\Ticket::PRIORITIES as $one)
                        <option value="{{ $one }}" @selected(old('priority', $ticket->priority) === $one)>
                            {{ ucfirst($one) }}
                        </option>
                    @endforeach
                </select>
                @error('priority')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status *</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach (\App\Models\Ticket::STATUSES as $one)
                        <option value="{{ $one }}" @selected(old('status', $ticket->status) === $one)>
                            {{ ucfirst(str_replace('_', ' ', $one)) }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description *</label>
                <textarea id="description" name="description" rows="6"
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $ticket->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-accent">
                    {{ $ticket->exists ? 'Save changes' : 'Create ticket' }}
                </button>
                <a href="{{ route('tickets.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
@endsection
