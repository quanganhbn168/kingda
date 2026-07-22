<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductTranslation extends Model implements HasMedia
{
    use HasPublicUrl;
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'locale',
        'slug',
        'name',
        'description',
        'content',
        'specifications',
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
        'specifications' => 'array',
        'blocks' => 'array',
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
        static::saving(function (ProductTranslation $translation): void {
            if (filled($translation->slug) || blank($translation->name)) {
                $translation->slug = $translation->slug ? trim($translation->slug, '/') : null;

                return;
            }

            $translation->slug = static::uniqueSlug(
                Str::slug($translation->name),
                $translation->locale,
                $translation->getKey()
            );
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->useDisk('public')->singleFile();
        $this->addMediaCollection('hero')->useDisk('public')->singleFile();
        $this->addMediaCollection('og_image')->useDisk('public')->singleFile();
        $this->addMediaCollection('gallery')->useDisk('public');
    }

    protected function routeSegmentKey(): string
    {
        return 'products';
    }

    protected function urlSegments(): array
    {
        static $categorySlugCache = [];
        $locale = $this->locale ?: config('locales.default', 'vi');

        $product = $this->relationLoaded('product') ? $this->product : null;
        $category = $product?->category;
        $categoryTranslation = null;

        if ($category?->relationLoaded('translation')) {
            $categoryTranslation = $category->translation;
        } elseif ($category?->relationLoaded('translations')) {
            $categoryTranslation = $category->translations->firstWhere('locale', $locale);
        } elseif ($category) {
            $categoryTranslation = $category->translationFor($locale)->first();
        }

        $categorySlug = $categoryTranslation?->slug;

        if (
            ! $categorySlug
            && $this->product_id
            && ! ($product?->relationLoaded('category') ?? false)
        ) {
            $cacheKey = $this->product_id.':'.$locale;

            if (! array_key_exists($cacheKey, $categorySlugCache)) {
                $categorySlugCache[$cacheKey] = CategoryTranslation::query()
                    ->where('locale', $locale)
                    ->whereHas('category.products', fn (Builder $query): Builder => $query->whereKey($this->product_id))
                    ->value('slug');
            }

            $categorySlug = $categorySlugCache[$cacheKey];
        }

        return [$categorySlug, $this->slug];
    }

    public function getMetaTitleAttribute(): string
    {
        return $this->seo_title ?: $this->name;
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

    private static function uniqueSlug(string $slug, string $locale, int|string|null $ignoreId = null): string
    {
        $slug = $slug ?: 'product';
        $baseSlug = $slug;
        $suffix = 2;

        while (static::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
