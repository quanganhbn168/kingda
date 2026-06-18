<?php

namespace App\Models;

use App\Enums\BranchContactType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchContact extends Model
{
    use SoftDeletes;

    public const TYPE_PHONE = 'phone';
    public const TYPE_HOTLINE = 'hotline';
    public const TYPE_SALES = 'sales';
    public const TYPE_SUPPORT = 'support';
    public const TYPE_EMAIL = 'email';
    public const TYPE_ZALO = 'zalo';
    public const TYPE_MESSENGER = 'messenger';
    public const TYPE_WHATSAPP = 'whatsapp';
    public const TYPE_WEBSITE = 'website';
    public const TYPE_MAP = 'map';

    protected $fillable = [
        'branch_id',
        'type',
        'label',
        'value',
        'display_value',
        'url',
        'contact_person',
        'position',
        'is_primary',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'label' => 'array',
        'is_primary' => 'boolean',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'resolved_label',
        'resolved_display_value',
        'href',
    ];

    public static function types(): array
    {
        return BranchContactType::options();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getResolvedLabelAttribute(): ?string
    {
        $locale = app()->getLocale();

        if (is_array($this->label)) {
            return $this->label[$locale]
                ?? $this->label['vi']
                ?? $this->label['en']
                ?? null;
        }

        return null;
    }

    public function getResolvedDisplayValueAttribute(): string
    {
        return $this->display_value ?: $this->value;
    }

    public function getHrefAttribute(): string
    {
        if ($this->url) {
            return $this->url;
        }

        return match ($this->type) {
            BranchContactType::Phone->value,
            BranchContactType::Hotline->value,
            BranchContactType::Sales->value,
            BranchContactType::Support->value => 'tel:' . $this->value,

            BranchContactType::Email->value => 'mailto:' . $this->value,

            BranchContactType::Zalo->value => 'https://zalo.me/' . $this->value,

            default => $this->value,
        };
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
