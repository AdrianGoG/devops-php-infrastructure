@extends('layouts.app')

@section('title', 'Editor')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Editor</h1>
            <p class="page-subtitle mb-0">Every post, drafts included.</p>
        </div>
        <a href="{{ route('posts.index') }}" class="btn btn-ghost btn-sm">View the public site</a>
    </div>

    <div class="card-surface">
        <div class="table-responsive">
            <table class="table table-crm align-middle">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>
                                <span class="cell-strong d-block">{{ $post->title }}</span>
                                <span class="text-dim small mono">/{{ $post->slug }}</span>
                            </td>
                            <td class="text-dim">{{ $post->author ?? '—' }}</td>
                            <td>
                                <span class="pill {{ $post->isPublished() ? 'pill-ok' : 'pill-warn' }}">
                                    {{ $post->status }}
                                </span>
                            </td>
                            <td class="text-dim small">
                                {{ optional($post->published_at)->format('d M Y') ?? '—' }}
                            </td>
                            <td class="text-end">
                                @if ($post->isPublished())
                                    <a href="{{ route('posts.show', $post) }}" class="btn btn-ghost btn-sm">View</a>
                                @endif

                                <a href="{{ route('posts.edit', $post) }}" class="btn btn-ghost btn-sm">Edit</a>

                                <form method="POST" action="{{ route('posts.destroy', $post) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this post?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-soft btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-dim py-4">No post yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection