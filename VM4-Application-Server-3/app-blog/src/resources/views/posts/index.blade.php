@extends('layouts.app')

@section('title', 'Articles')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Articles</h1>
            <p class="page-subtitle mb-0">
                {{ $posts->count() }} published {{ Str::plural('article', $posts->count()) }}
                @if ($search !== '')
                    for "{{ $search }}" · <a href="{{ route('posts.index') }}">clear</a>
                @endif
            </p>
        </div>

        @if ($draftCount > 0)
            <span class="pill pill-warn">{{ $draftCount }} {{ Str::plural('draft', $draftCount) }}</span>
        @endif
    </div>

    <form method="GET" action="{{ route('posts.index') }}" class="card-surface card-pad mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-10">
                <label for="q" class="form-label">Search</label>
                <input type="text" id="q" name="q" value="{{ $search }}"
                       class="form-control" placeholder="Title or excerpt">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-ghost w-100">Search</button>
            </div>
        </div>
    </form>

    <div class="d-flex flex-column gap-3">
        @forelse ($posts as $post)
            <article class="card-surface card-pad">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="pill pill-ok">published</span>
                    <span class="text-dim small">
                        {{ optional($post->published_at)->format('d M Y') }}
                        · {{ $post->readingMinutes() }} min read
                        @if ($post->author)
                            · {{ $post->author }}
                        @endif
                    </span>
                </div>

                <h2 class="card-title-sm mb-2">
                    <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                </h2>

                <p class="card-text-sm">{{ $post->excerpt }}</p>
            </article>
        @empty
            <div class="card-surface card-pad text-center text-dim">
                No published article yet.
            </div>
        @endforelse
    </div>
@endsection