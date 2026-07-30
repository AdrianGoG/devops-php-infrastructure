@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <article class="mx-auto" style="max-width: 760px;">
        <a href="{{ route('posts.index') }}" class="btn-link-muted small">← Back to articles</a>

        <h1 class="page-title mt-3 mb-2">{{ $post->title }}</h1>

        <p class="page-subtitle mb-4">
            {{ optional($post->published_at)->format('d M Y') }}
            · {{ $post->readingMinutes() }} min read
            @if ($post->author)
                · by {{ $post->author }}
            @endif
        </p>

        <div class="card-surface card-pad">
            <p class="card-text-sm mb-4" style="font-style: italic;">{{ $post->excerpt }}</p>

            <hr class="divider-soft">

            <div class="card-text-sm" style="white-space: pre-line;">{{ $post->body }}</div>
        </div>

        <div class="mt-4">
            <a href="{{ route('posts.edit', $post) }}" class="btn btn-ghost btn-sm">Edit this post</a>
        </div>
    </article>
@endsection