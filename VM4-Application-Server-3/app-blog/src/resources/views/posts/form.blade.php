@extends('layouts.app')

@section('title', $post->exists ? 'Edit post' : 'New post')

@section('content')
    <div class="d-flex align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $post->exists ? 'Edit post' : 'New post' }}</h1>
            <p class="page-subtitle mb-0">
                The slug is generated from the title, and kept unique automatically.
            </p>
        </div>
        <a href="{{ route('posts.manage') }}" class="btn btn-ghost btn-sm">Back to editor</a>
    </div>

    @if ($errors->any())
        <div class="alert-soft alert-soft-err mb-4">
            The form has {{ $errors->count() }} {{ Str::plural('error', $errors->count()) }}.
            Please check the fields below.
        </div>
    @endif

    <form method="POST"
          action="{{ $post->exists ? route('posts.update', $post) : route('posts.store') }}"
          class="card-surface card-pad" novalidate>
        @csrf
        @if ($post->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-8">
                <label for="title" class="form-label">Title *</label>
                <input type="text" id="title" name="title" value="{{ old('title', $post->title) }}"
                       class="form-control @error('title') is-invalid @enderror">
                @error('title')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">Status *</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach (\App\Models\Post::STATUSES as $one)
                        <option value="{{ $one }}" @selected(old('status', $post->status) === $one)>
                            {{ ucfirst($one) }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="author" class="form-label">Author</label>
                <input type="text" id="author" name="author" value="{{ old('author', $post->author) }}"
                       class="form-control @error('author') is-invalid @enderror">
                @error('author')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="excerpt" class="form-label">Excerpt *</label>
                <textarea id="excerpt" name="excerpt" rows="2"
                          class="form-control @error('excerpt') is-invalid @enderror"
                          placeholder="One or two sentences shown in the listing">{{ old('excerpt', $post->excerpt) }}</textarea>
                @error('excerpt')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="body" class="form-label">Body *</label>
                <textarea id="body" name="body" rows="12"
                          class="form-control @error('body') is-invalid @enderror">{{ old('body', $post->body) }}</textarea>
                @error('body')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-accent">
                    {{ $post->exists ? 'Save changes' : 'Create post' }}
                </button>
                <a href="{{ route('posts.manage') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
@endsection