<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A support ticket.
 *
 * @property string $reference
 * @property string $subject
 * @property string $description
 * @property string $requester
 * @property string $priority
 * @property string $status
 * @property string|null $assignee
 */
class Ticket extends Model
{
    use HasFactory;

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public const STATUSES = ['open', 'in_progress', 'resolved', 'closed'];

    protected $fillable = [
        'reference',
        'subject',
        'description',
        'requester',
        'priority',
        'status',
        'assignee',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Tickets nobody has finished yet.
     */
    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function isUnresolved(): bool
    {
        return in_array($this->status, ['open', 'in_progress'], true);
    }

    /**
     * Human readable status, for the interface.
     */
    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Bootstrap pill class for the status.
     */
    public function statusClass(): string
    {
        return match ($this->status) {
            'open' => 'pill-warn',
            'in_progress' => 'pill-accent',
            'resolved' => 'pill-ok',
            default => 'pill',
        };
    }

    /**
     * Bootstrap pill class for the priority.
     */
    public function priorityClass(): string
    {
        return match ($this->priority) {
            'urgent' => 'pill-danger',
            'high' => 'pill-warn',
            default => 'pill',
        };
    }

    /**
     * The next free reference, in the form TCK-0001.
     */
    public static function nextReference(): string
    {
        $last = static::query()->orderByDesc('id')->value('reference');

        $number = $last ? (int) substr($last, 4) : 0;

        return sprintf('TCK-%04d', $number + 1);
    }
}
