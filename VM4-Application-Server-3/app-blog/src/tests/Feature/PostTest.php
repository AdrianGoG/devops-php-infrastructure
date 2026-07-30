<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The public listing, the article page and the small editor.
 *
 * Run by the "Test" stage of the Jenkins pipeline before every deployment.
 */
class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Deploying nine PHP applications',
            'excerpt' => 'How the pipeline distributes the estate across three servers.',
            'body' => str_repeat('Some content about the deployment pipeline. ', 20),
            'status' => 'published',
            'author' => 'Adrian G.',
        ], $overrides);
    }

    public function test_the_listing_shows_published_posts(): void
    {
        Post::factory()->create(['title' => 'Visible article']);

        $response = $this->get(route('posts.index'));

        $response->assertOk();
        $response->assertSee('Visible article');
    }

    public function test_the_listing_hides_drafts(): void
    {
        Post::factory()->draft()->create(['title' => 'Hidden draft']);

        $response = $this->get(route('posts.index'));

        $response->assertOk();
        $response->assertDontSee('Hidden draft');
    }

    public function test_the_listing_can_be_searched(): void
    {
        Post::factory()->create(['title' => 'Ansible playbooks explained']);
        Post::factory()->create(['title' => 'Something unrelated']);

        $response = $this->get(route('posts.index', ['q' => 'Ansible']));

        $response->assertOk();
        $response->assertSee('Ansible playbooks explained');
        $response->assertDontSee('Something unrelated');
    }

    public function test_an_article_page_is_reachable_by_slug(): void
    {
        $post = Post::factory()->create(['title' => 'Readable article', 'slug' => 'readable-article']);

        $response = $this->get(route('posts.show', $post));

        $response->assertOk();
        $response->assertSee('Readable article');
    }

    public function test_a_draft_is_not_reachable_from_the_public_site(): void
    {
        $post = Post::factory()->draft()->create(['slug' => 'secret-draft']);

        $this->get(route('posts.show', $post))->assertNotFound();
    }

    public function test_the_editor_lists_drafts_too(): void
    {
        Post::factory()->draft()->create(['title' => 'Draft in the editor']);

        $response = $this->get(route('posts.manage'));

        $response->assertOk();
        $response->assertSee('Draft in the editor');
    }

    public function test_a_post_can_be_created_and_gets_a_slug(): void
    {
        $response = $this->post(route('posts.store'), $this->validPayload());

        $response->assertRedirect(route('posts.manage'));

        $this->assertDatabaseHas('posts', [
            'title' => 'Deploying nine PHP applications',
            'slug' => 'deploying-nine-php-applications',
        ]);
    }

    public function test_publishing_stamps_the_publication_date(): void
    {
        $this->post(route('posts.store'), $this->validPayload());

        $this->assertNotNull(Post::first()->published_at);
    }

    public function test_a_draft_has_no_publication_date(): void
    {
        $this->post(route('posts.store'), $this->validPayload(['status' => 'draft']));

        $this->assertNull(Post::first()->published_at);
    }

    public function test_duplicate_titles_get_distinct_slugs(): void
    {
        $this->post(route('posts.store'), $this->validPayload(['title' => 'Same title']));
        $this->post(route('posts.store'), $this->validPayload(['title' => 'Same title']));

        $this->assertDatabaseHas('posts', ['slug' => 'same-title']);
        $this->assertDatabaseHas('posts', ['slug' => 'same-title-2']);
    }

    public function test_the_required_fields_are_validated(): void
    {
        $response = $this->post(route('posts.store'), []);

        $response->assertSessionHasErrors(['title', 'excerpt', 'body', 'status']);
        $this->assertSame(0, Post::count());
    }

    public function test_an_unknown_status_is_rejected(): void
    {
        $response = $this->post(route('posts.store'), $this->validPayload(['status' => 'whatever']));

        $response->assertSessionHasErrors('status');
    }

    public function test_a_post_can_be_updated(): void
    {
        $post = Post::factory()->create();

        $response = $this->put(route('posts.update', $post), $this->validPayload(['title' => 'Renamed']));

        $response->assertRedirect(route('posts.manage'));

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Renamed', 'slug' => 'renamed']);
    }

    public function test_pulling_a_post_back_to_draft_clears_the_publication_date(): void
    {
        $post = Post::factory()->create();

        $this->assertNotNull($post->published_at);

        $this->put(route('posts.update', $post), $this->validPayload(['status' => 'draft']));

        $this->assertNull($post->fresh()->published_at);
    }

    public function test_a_post_can_be_deleted(): void
    {
        $post = Post::factory()->create();

        $response = $this->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.manage'));
        $this->assertSame(0, Post::count());
    }

    public function test_an_unknown_slug_returns_404(): void
    {
        $this->get('/posts/no-such-article')->assertNotFound();
    }
}