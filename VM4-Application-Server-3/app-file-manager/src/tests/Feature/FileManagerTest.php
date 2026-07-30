<?php

namespace Tests\Feature;

use App\Models\StoredFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Upload, list, download and delete.
 *
 * Storage::fake replaces the uploads disk, so the suite never writes into the
 * real volume.
 */
class FileManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
    }

    public function test_the_listing_is_reachable_and_empty_at_first(): void
    {
        $response = $this->get(route('files.index'));

        $response->assertOk();
        $response->assertSee('No file stored yet.');
    }

    public function test_a_file_can_be_uploaded(): void
    {
        $response = $this->post(route('files.store'), [
            'file' => UploadedFile::fake()->create('report.pdf', 120, 'application/pdf'),
            'description' => 'Monthly report',
        ]);

        $response->assertRedirect(route('files.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('stored_files', [
            'original_name' => 'report.pdf',
            'description' => 'Monthly report',
        ]);

        $stored = StoredFile::first();

        Storage::disk('uploads')->assertExists($stored->path);
    }

    public function test_the_stored_name_is_not_the_uploaded_name(): void
    {
        $this->post(route('files.store'), [
            'file' => UploadedFile::fake()->create('../../evil.php', 10),
        ]);

        $stored = StoredFile::first();

        $this->assertNotSame('../../evil.php', $stored->path);
        $this->assertStringNotContainsString('..', $stored->path);
        $this->assertStringEndsWith('.php', $stored->path);
    }

    public function test_two_uploads_with_the_same_name_do_not_overwrite_each_other(): void
    {
        $this->post(route('files.store'), ['file' => UploadedFile::fake()->create('same.txt', 5)]);
        $this->post(route('files.store'), ['file' => UploadedFile::fake()->create('same.txt', 5)]);

        $this->assertSame(2, StoredFile::count());
        $this->assertSame(2, StoredFile::query()->distinct()->count('path'));
    }

    public function test_an_upload_without_a_file_is_rejected(): void
    {
        $response = $this->post(route('files.store'), []);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, StoredFile::count());
    }

    public function test_an_oversized_upload_is_rejected(): void
    {
        $response = $this->post(route('files.store'), [
            'file' => UploadedFile::fake()->create('huge.bin', 9000),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, StoredFile::count());
    }

    public function test_the_listing_shows_and_searches_files(): void
    {
        $this->post(route('files.store'), [
            'file' => UploadedFile::fake()->create('invoice-2026.pdf', 20),
            'description' => 'Accounting',
        ]);
        $this->post(route('files.store'), ['file' => UploadedFile::fake()->create('photo.jpg', 20)]);

        $all = $this->get(route('files.index'));
        $all->assertSee('invoice-2026.pdf');
        $all->assertSee('photo.jpg');

        $filtered = $this->get(route('files.index', ['q' => 'invoice']));
        $filtered->assertSee('invoice-2026.pdf');
        $filtered->assertDontSee('photo.jpg');
    }

    public function test_a_file_can_be_downloaded_under_its_original_name(): void
    {
        $this->post(route('files.store'), ['file' => UploadedFile::fake()->create('notes.txt', 5)]);

        $response = $this->get(route('files.download', StoredFile::first()));

        $response->assertOk();
        $response->assertDownload('notes.txt');
    }

    public function test_downloading_a_file_whose_bytes_are_gone_reports_the_problem(): void
    {
        $this->post(route('files.store'), ['file' => UploadedFile::fake()->create('ghost.txt', 5)]);

        $file = StoredFile::first();
        Storage::disk('uploads')->delete($file->path);

        $response = $this->get(route('files.download', $file));

        $response->assertRedirect(route('files.index'));
        $response->assertSessionHas('error');
    }

    public function test_deleting_a_file_removes_the_row_and_the_bytes(): void
    {
        $this->post(route('files.store'), ['file' => UploadedFile::fake()->create('temp.txt', 5)]);

        $file = StoredFile::first();
        $path = $file->path;

        $response = $this->delete(route('files.destroy', $file));

        $response->assertRedirect(route('files.index'));
        $this->assertSame(0, StoredFile::count());
        Storage::disk('uploads')->assertMissing($path);
    }

    public function test_the_human_size_helper_formats_bytes(): void
    {
        $file = new StoredFile(['size' => 2048]);
        $this->assertSame('2 KB', $file->humanSize());

        $file = new StoredFile(['size' => 500]);
        $this->assertSame('500 B', $file->humanSize());
    }
}