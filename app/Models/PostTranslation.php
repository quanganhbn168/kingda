<?php

namespace App\Models;

use App\Enums\Locale;
use App\Enums\MetaRobots;
use App\Models\Concerns\HasPublicUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PostTranslation extends Model implements HasMedia
{
    use HasPublicUrl;
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
        'meta_robots',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'public_url',
        'meta_title',
        'meta_description',
    ];

    protected static function booted(): void
    {
        static::saving(function (PostTranslation $translation): ?bool {
            if (blank($translation->title)) {
                if ($translation->locale === Locale::Vietnamese->value || $translation->hasTranslationContent()) {
                    throw ValidationException::withMessages([
                        'title' => 'Tiêu đề là bắt buộc khi lưu bản dịch bài viết.',
                    ]);
                }

                return false;
            }

            if (blank($translation->seo_title)) {
                $translation->seo_title = static::seoTitleFor($translation->title);
            }

            if (blank($translation->seo_description)) {
                $translation->seo_description = static::seoDescriptionFor(
                    $translation->description,
                    $translation->content,
                    $translation->title,
                );
            }
            $translation->meta_robots = MetaRobots::tryFrom((string) $translation->meta_robots)?->value
                ?? MetaRobots::IndexFollow->value;

            if (blank($translation->published_at)) {
                $translation->published_at = now();
            }

            if ($translation->isDirty('title') && ! $translation->isDirty('slug')) {
                $translation->slug = null;
            }

            if (filled($translation->slug) || blank($translation->title)) {
                $translation->slug = $translation->slug
                    ? static::uniqueSlug(trim($translation->slug, '/'), $translation->locale, $translation->getKey())
                    : null;

                return null;
            }

            $translation->slug = static::uniqueSlug(
                Str::slug($translation->title),
                $translation->locale,
                $translation->getKey()
            );

            return null;
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
    }

    protected function routeSegmentKey(): string
    {
        return 'news';
    }

    protected function urlSegments(): array
    {
        static $categorySlugCache = [];
        $locale = $this->locale ?: config('locales.default', 'vi');

        $post = $this->relationLoaded('post') ? $this->post : null;
        $category = $post?->category;
        $categoryTranslation = null;

        if (
            $category?->relationLoaded('translation')
            && ($category->translation?->locale === $locale)
        ) {
            $categoryTranslation = $category->translation;
        } elseif ($category?->relationLoaded('translations')) {
            $categoryTranslation = $category->translations->firstWhere('locale', $locale);
        } elseif ($category) {
            $categoryTranslation = $category->translationFor($locale)->first();
        }

        $categorySlug = $categoryTranslation?->slug;

        if (
            ! $categorySlug
            && $this->post_id
            && ! ($post?->relationLoaded('category') ?? false)
        ) {
            $cacheKey = $this->post_id.':'.$locale;

            if (! array_key_exists($cacheKey, $categorySlugCache)) {
                $categorySlugCache[$cacheKey] = CategoryTranslation::query()
                    ->where('locale', $locale)
                    ->whereHas('category.posts', fn (Builder $query): Builder => $query->whereKey($this->post_id))
                    ->value('slug');
            }

            $categorySlug = $categorySlugCache[$cacheKey];
        }

        return [$categorySlug, $this->slug];
    }

    public function getMetaTitleAttribute(): ?string
    {
        return $this->seo_title;
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->seo_description;
    }

    public function scopeLocale(Builder $query, ?string $locale = null): Builder
    {
        return $query->where('locale', $locale ?: app()->getLocale());
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->whereNotNull('slug')
            ->where('slug', '!=', '');
    }

    public function isUsable(): bool
    {
        return filled($this->title) && filled($this->slug);
    }

    public function scopeSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    private static function uniqueSlug(string $slug, string $locale, int|string|null $ignoreId = null): string
    {
        $slug = $slug ?: 'post';
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

    public static function seoTitleFor(?string $title): string
    {
        return Str::limit(static::plainText($title), 255, '');
    }

    public static function seoDescriptionFor(?string $description, ?string $content, ?string $title): string
    {
        $source = filled($description)
            ? $description
            : (filled($content) ? $content : $title);

        return Str::limit(static::plainText($source), 160, '');
    }

    private function hasTranslationContent(): bool
    {
        return collect([
            $this->slug,
            $this->description,
            $this->content,
        ])->contains(fn (?string $value): bool => filled($value));
    }

    private static function plainText(?string $value): string
    {
        return Str::squish(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
