<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A file uploaded to the manager.
 *
 * The row holds the metadata; the bytes live on the "uploads" disk, which is a
 * Docker volume so the files survive a container rebuild.
 *
 * @property string $original_name
 * @property string $path
 * @property int $size
 * @property string $mime_type
 * @property string|null $description
 */
class StoredFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_name',
        'path',
        'size',
        'mime_type',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Human readable size, for the listing.
     */
    public function humanSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, $unit === 0 ? 0 : 1).' '.$units[$unit];
    }

    /**
     * The file extension, uppercased - shown as a badge.
     */
    public function extension(): string
    {
        $extension = pathinfo($this->original_name, PATHINFO_EXTENSION);

        return $extension === '' ? 'FILE' : strtoupper($extension);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Whether the bytes are actually still on disk.
     *
     * Metadata and storage can drift apart - a restored database dump without
     * the matching volume, for instance - and the listing should say so rather
     * than offer a download that fails.
     */
    public function existsOnDisk(): bool
    {
        return Storage::disk('uploads')->exists($this->path);
    }

    /**
     * Remove the bytes when the row goes away.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $file): void {
            Storage::disk('uploads')->delete($file->path);
        });
    }
}