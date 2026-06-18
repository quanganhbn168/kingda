<?php

namespace App\Models;

use App\Enums\ContactMessageStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContactMessage extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DONE = 'done';
    public const STATUS_SPAM = 'spam';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'company',
        'subject',
        'message',
        'source',
        'related_type',
        'related_id',
        'status',
        'admin_note',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsRead(): bool
    {
        return $this->forceFill([
            'read_at' => now(),
        ])->save();
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', ContactMessageStatus::New->value);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }
}
