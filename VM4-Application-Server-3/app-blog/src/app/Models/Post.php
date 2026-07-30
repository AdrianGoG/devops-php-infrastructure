<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A blog post.
 *
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $body
 * @property string $status
 * @property string|null $author
 */
class Post extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'published'];

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'author',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * The public site only shows published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Posts are addressed by slug, not by id.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * A unique slug built from the title.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    /**
     * Rough reading time, for the post header.
     */
    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 200));
    }
}