<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndustryTranslation extends Model
{
    protected $fillable = [
        'industry_id',
        'locale',
        'slug',
        'title',
        'description',
        'content',
        'seo_title',
        'seo_description',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $appends = [
        'public_url',
    ];

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function getPublicUrlAttribute(): string
    {
        $locale = $this->locale ?: 'vi';
        $segment = $locale === 'vi' ? 'linh-vuc' : 'industries';
        $path = $segment . '/' . trim((string) $this->slug, '/');

        return $locale === 'vi'
            ? url('/' . $path)
            : url('/' . $locale . '/' . $path);
    }

    public function scopeLocale(Builder $query, ?string $locale = null): Builder
    {
        return $query->where('locale', $locale ?: app()->getLocale());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
