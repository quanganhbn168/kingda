<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ServiceTranslation extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'service_id',
        'locale',
        'slug',
        'title',
        'description',
        'content',
        'blocks',
        'seo_title',
        'seo_description',
        'og_title',
        'og_description',
        'canonical_url',
        'meta_robots',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'blocks' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'public_url',
        'meta_title',
        'meta_description',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->useDisk('public')->singleFile();
        $this->addMediaCollection('hero')->useDisk('public')->singleFile();
        $this->addMediaCollection('og_image')->useDisk('public')->singleFile();
        $this->addMediaCollection('gallery')->useDisk('public');
    }

    public function getPublicUrlAttribute(): string
    {
        $defaultLocale = config('locales.default', 'vi');
        $locale = $this->locale ?: $defaultLocale;
        $segment = $locale === 'vi' ? 'dich-vu' : 'services';
        $path = $segment . '/' . trim($this->slug, '/');

        return $locale === $defaultLocale
            ? url('/' . $path)
            : url('/' . $locale . '/' . $path);
    }

    public function getMetaTitleAttribute(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->seo_description ?: $this->description;
    }

    public function scopeLocale(Builder $query, ?string $locale = null): Builder
    {
        return $query->where('locale', $locale ?: app()->getLocale());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }
}
