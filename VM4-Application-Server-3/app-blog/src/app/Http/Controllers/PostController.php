<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public blog and its small editor.
 *
 * There is no authentication: this is an internal lab application and adding
 * Breeze here would only duplicate what app-user-dashboard already demonstrates.
 */
class PostController extends Controller
{
    /**
     * The public listing - published posts only.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $posts = Post::query()
            ->published()
            ->when($search !== '', fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('excerpt', 'like', '%'.$search.'%');
            }))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('posts.index', [
            'posts' => $posts,
            'search' => $search,
            'draftCount' => Post::where('status', 'draft')->count(),
        ]);
    }

    /**
     * Everything, drafts included - the editor's view.
     */
    public function manage(): View
    {
        return view('posts.manage', [
            'posts' => Post::query()->orderByDesc('id')->get(),
        ]);
    }

    public function show(Post $post): View
    {
        // A draft is not reachable from the public site.
        abort_unless($post->isPublished(), 404);

        return view('posts.show', ['post' => $post]);
    }

    public function create(): View
    {
        return view('posts.form', ['post' => new Post(['status' => 'draft'])]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $post = new Post($request->validated());
        $post->slug = Post::uniqueSlug($post->title);

        if ($post->isPublished()) {
            $post->published_at = now();
        }

        $post->save();

        return redirect()->route('posts.manage')
            ->with('status', 'Post "'.$post->title.'" saved.');
    }

    public function edit(Post $post): View
    {
        return view('posts.form', ['post' => $post]);
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $wasPublished = $post->isPublished();

        $post->fill($request->validated());
        $post->slug = Post::uniqueSlug($post->title, $post->id);

        // Stamp the publication date the first time a post goes public, and
        // clear it if it is pulled back to draft.
        if (! $wasPublished && $post->isPublished()) {
            $post->published_at = now();
        } elseif (! $post->isPublished()) {
            $post->published_at = null;
        }

        $post->save();

        return redirect()->route('posts.manage')
            ->with('status', 'Post "'.$post->title.'" updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $title = $post->title;

        $post->delete();

        return redirect()->route('posts.manage')
            ->with('status', 'Post "'.$title.'" deleted.');
    }
}