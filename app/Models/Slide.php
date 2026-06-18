<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Slide extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'key',
        'location',
        'theme',
        'text_position',
        'media_type',
        'video_embed_url',
        'is_active',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(SlideTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(SlideTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function translationFor(?string $locale = null): HasOne
    {
        return $this->hasOne(SlideTranslation::class)
            ->where('locale', $locale ?: app()->getLocale());
    }

    public function resolveTranslation(?string $locale = null): ?SlideTranslation
    {
        $locale ??= app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first();
    }

    public function publishedTranslation(?string $locale = null): ?SlideTranslation
    {
        $locale ??= app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return $this->translations
                ->where('locale', $locale)
                ->where('is_published', true)
                ->first();
        }

        return $this->translations()
            ->where('locale', $locale)
            ->where('is_published', true)
            ->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }

    public function scopeLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('desktop')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('mobile')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('video')
            ->useDisk('public')
            ->singleFile();
    }
}
