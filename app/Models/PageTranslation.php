<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PageTranslation extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'slug',
        'headline',
        'subheadline',
        'excerpt',
        'content',
        'seo_title',
        'seo_description',
        'meta_robots',
        'canonical_url',
        'og_title',
        'og_description',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'public_url',
        'resolved_seo_title',
        'resolved_og_title',
        'resolved_og_description',
    ];

    protected static function booted(): void
    {
        static::saving(function (PageTranslation $translation): void {
            $page = $translation->page ?: Page::query()->find($translation->page_id);

            if ($page?->is_home) {
                $translation->slug = null;

                return;
            }

            if (filled($translation->slug) || blank($translation->title)) {
                $translation->slug = $translation->slug ? trim($translation->slug, '/') : null;

                return;
            }

            $translation->slug = static::uniqueSlug(
                Str::slug($translation->title),
                $translation->locale,
                $translation->getKey()
            );
        });
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function getPublicUrlAttribute(): string
    {
        $page = $this->page;
        $locale = $this->locale ?: 'vi';

        if ($page?->is_home) {
            return $this->locale === 'vi'
                ? url('/')
                : url('/' . $this->locale);
        }

        $systemPath = $this->systemPath($page?->key, $locale);

        if ($systemPath !== null) {
            return url($systemPath);
        }

        $slug = trim((string) $this->slug, '/');

        return $locale === 'vi'
            ? url('/' . $slug)
            : url('/' . $locale . '/' . $slug);
    }

    public function getResolvedSeoTitleAttribute(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function getResolvedOgTitleAttribute(): string
    {
        return $this->og_title ?: $this->seo_title ?: $this->title;
    }

    public function getResolvedOgDescriptionAttribute(): ?string
    {
        return $this->og_description ?: $this->seo_description ?: $this->excerpt;
    }

    public function scopeLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', trim($slug, '/'));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')->useDisk('public')->singleFile();

        $this->addMediaCollection('thumbnail')->useDisk('public')->singleFile();

        $this->addMediaCollection('og_image')->useDisk('public')->singleFile();

        $this->addMediaCollection('gallery')->useDisk('public');
    }

    private function systemPath(?string $key, string $locale): ?string
    {
        $segments = match ($key) {
            'about' => ['vi' => 'gioi-thieu', 'en' => 'about', 'zh' => 'about'],
            'products' => ['vi' => 'san-pham', 'en' => 'products', 'zh' => 'products'],
            'news' => ['vi' => 'tin-tuc', 'en' => 'news', 'zh' => 'news'],
            'contact' => ['vi' => 'lien-he', 'en' => 'contact', 'zh' => 'contact'],
            default => null,
        };

        if ($segments === null) {
            return null;
        }

        $segment = $segments[$locale] ?? $segments['en'];

        return $locale === 'vi'
            ? '/' . $segment
            : '/' . $locale . '/' . $segment;
    }

    private static function uniqueSlug(string $slug, string $locale, int | string | null $ignoreId = null): string
    {
        $slug = $slug ?: 'page';
        $baseSlug = $slug;
        $suffix = 2;

        while (static::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }
}
