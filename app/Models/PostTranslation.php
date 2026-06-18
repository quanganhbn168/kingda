<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PostTranslation extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'post_id',
        'locale',
        'slug',
        'title',
        'description',
        'content',
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
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'public_url',
        'meta_title',
        'meta_description',
    ];

    protected static function booted(): void
    {
        static::saving(function (PostTranslation $translation): void {
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

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
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
        $segment = $locale === 'vi' ? 'tin-tuc' : 'news';
        static $categorySlugCache = [];

        $post = $this->relationLoaded('post') ? $this->post : null;
        $category = $post?->category;
        $categoryTranslation = null;

        if ($category?->relationLoaded('translation')) {
            $categoryTranslation = $category->translation;
        } elseif ($category?->relationLoaded('translations')) {
            $categoryTranslation = $category->translations->firstWhere('locale', $locale);
        } elseif ($category) {
            $categoryTranslation = $category->translationFor($locale)->first();
        }

        $categorySlug = $categoryTranslation?->slug;

        if (! $categorySlug && $this->post_id) {
            $cacheKey = $this->post_id . ':' . $locale;
            $categorySlug = $categorySlugCache[$cacheKey] ??= CategoryTranslation::query()
                ->where('locale', $locale)
                ->whereHas('category.posts', fn (Builder $query): Builder => $query->whereKey($this->post_id))
                ->value('slug');
        }

        $path = $segment . '/' . trim(collect([$categorySlug, $this->slug])->filter()->join('/'), '/');

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

    private static function uniqueSlug(string $slug, string $locale, int | string | null $ignoreId = null): string
    {
        $slug = $slug ?: 'post';
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
