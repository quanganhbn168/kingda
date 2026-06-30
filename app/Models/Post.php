<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\HasFeaturedStatus;
use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use HasActiveStatus;
    use HasFeaturedStatus;
    use HasSortOrder;

    protected $fillable = [
        'category_id',
        'author_id',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(PostTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function publishedTranslation(): HasOne
    {
        return $this->translation()->where('is_published', true);
    }

    public function translationFor(?string $locale = null): HasOne
    {
        return $this->hasOne(PostTranslation::class)
            ->where('locale', $locale ?: app()->getLocale());
    }

    public function resolveTranslation(
        ?string $locale = null,
        string $fallbackLocale = 'vi',
        bool $publishedOnly = false,
    ): ?PostTranslation {
        $locale ??= app()->getLocale();
        $locales = array_values(array_unique([$locale, $fallbackLocale]));
        $translations = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->whereIn('locale', $locales)->get();

        $translation = $translations->first(
            fn (PostTranslation $item): bool => $item->locale === $locale && $item->isUsable(),
        );

        if ($translation) {
            return (! $publishedOnly || $translation->is_published) ? $translation : null;
        }

        if ($locale === $fallbackLocale) {
            return null;
        }

        $fallback = $translations->first(
            fn (PostTranslation $item): bool => $item->locale === $fallbackLocale && $item->isUsable(),
        );

        return ($fallback && (! $publishedOnly || $fallback->is_published)) ? $fallback : null;
    }

    public function useResolvedTranslation(?string $locale = null, bool $publishedOnly = false): static
    {
        $translation = $this->resolveTranslation($locale, publishedOnly: $publishedOnly);
        $translation?->setRelation('post', $this);
        $this->setRelation('translation', $translation);

        return $this;
    }

    public function getSlugUrlAttribute(): string
    {
        $translation = $this->resolveTranslation('vi');

        if (! $translation) {
            return '';
        }

        $translation->setRelation('post', $this);

        return $translation->public_url;
    }

    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'linkable');
    }

    public function scopeLatestPublished(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();

        return $query->whereHas('translations', function (Builder $query) use ($locale) {
            $query->where('locale', $locale)->where('is_published', true);
        })->with(['translation' => fn ($query) => $query->where('locale', $locale)]);
    }

    public function scopeWithPublishedTranslation(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();

        if ($locale === 'vi') {
            return $query->whereHas('translations', fn (Builder $query): Builder => $query
                ->locale('vi')
                ->published()
                ->usable());
        }

        return $query->where(function (Builder $query) use ($locale): void {
            $query
                ->whereHas('translations', fn (Builder $translationQuery): Builder => $translationQuery
                    ->locale($locale)
                    ->published()
                    ->usable())
                ->orWhere(function (Builder $fallbackQuery) use ($locale): void {
                    $fallbackQuery
                        ->whereDoesntHave('translations', fn (Builder $translationQuery): Builder => $translationQuery
                            ->locale($locale)
                            ->usable())
                        ->whereHas('translations', fn (Builder $translationQuery): Builder => $translationQuery
                            ->locale('vi')
                            ->published()
                            ->usable());
                });
        });
    }
}
