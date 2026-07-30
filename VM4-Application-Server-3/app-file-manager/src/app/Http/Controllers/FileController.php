<?php

namespace App\Http\Controllers;

use App\Models\StoredFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Upload, list, download and delete files.
 *
 * The bytes live on the "uploads" disk, which is a Docker volume - the reason
 * this application exists in the project is to demonstrate volume persistence.
 */
class FileController extends Controller
{
    /** Maximum accepted upload, in kilobytes. */
    private const MAX_KILOBYTES = 8192;

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $files = StoredFile::query()
            ->when($search !== '', fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('original_name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('files.index', [
            'files' => $files,
            'search' => $search,
            'totalFiles' => StoredFile::count(),
            'totalBytes' => (int) StoredFile::sum('size'),
            'maxKilobytes' => self::MAX_KILOBYTES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:'.self::MAX_KILOBYTES],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'file.required' => 'Choose a file to upload.',
            'file.max' => 'The file may not be larger than '.(self::MAX_KILOBYTES / 1024).' MB.',
        ]);

        $upload = $validated['file'];

        // The stored name is random: two uploads with the same original name
        // must never overwrite each other, and a user supplied name must never
        // reach the filesystem.
        $path = Str::ulid().'.'.($upload->getClientOriginalExtension() ?: 'bin');

        Storage::disk('uploads')->putFileAs('', $upload, $path);

        StoredFile::create([
            'original_name' => $upload->getClientOriginalName(),
            'path' => $path,
            'size' => $upload->getSize(),
            'mime_type' => $upload->getClientMimeType() ?: 'application/octet-stream',
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('files.index')
            ->with('status', 'Uploaded '.$upload->getClientOriginalName().'.');
    }

    public function download(StoredFile $file): StreamedResponse|RedirectResponse
    {
        if (! $file->existsOnDisk()) {
            return redirect()->route('files.index')
                ->with('error', 'The bytes of "'.$file->original_name.'" are no longer on disk.');
        }

        return Storage::disk('uploads')->download($file->path, $file->original_name);
    }

    public function destroy(StoredFile $file): RedirectResponse
    {
        $name = $file->original_name;

        // The model deletes the bytes as well - see StoredFile::booted().
        $file->delete();

        return redirect()->route('files.index')
            ->with('status', 'Deleted '.$name.'.');
    }
}