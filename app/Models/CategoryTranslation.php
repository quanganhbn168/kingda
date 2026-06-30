<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Enums\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CategoryTranslation extends Model implements HasMedia
{
    use InteractsWithMedia;
    use \App\Models\Concerns\HasPublicUrl;

    protected $fillable = [
        'category_id',
        'locale',
        'slug',
        'name',
        'description',
        'content',
        'seo_title',
        'seo_description',
        'og_title',
        'og_description',
        'canonical_url',
        'meta_robots',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $appends = [
        'public_url',
        'meta_title',
        'meta_description',
    ];

    protected static function booted(): void
    {
        static::saving(function (CategoryTranslation $translation): void {
            if (filled($translation->slug) || blank($translation->name)) {
                $translation->slug = $translation->slug ? trim($translation->slug, '/') : null;

                return;
            }

            $slugSource = $translation->name;

            if ($translation->locale === Locale::Chinese->value && $translation->category_id) {
                $slugSource = static::query()
                    ->where('category_id', $translation->category_id)
                    ->where('locale', Locale::English->value)
                    ->value('slug') ?: $slugSource;
            }

            $translation->slug = static::uniqueSlug(
                Str::slug($slugSource),
                $translation->locale,
                $translation->getKey()
            );
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->useDisk('public')->singleFile();
        $this->addMediaCollection('hero')->useDisk('public')->singleFile();
        $this->addMediaCollection('og_image')->useDisk('public')->singleFile();
    }

    protected function routeSegmentKey(): string
    {
        $type = $this->category?->type;

        return match ($type) {
            CategoryType::Product->value => 'products',
            CategoryType::Post->value => 'news',
            CategoryType::Service->value => 'services',
            default => 'categories',
        };
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

    private static function uniqueSlug(string $slug, string $locale, int | string | null $ignoreId = null): string
    {
        $slug = $slug ?: 'category';
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
