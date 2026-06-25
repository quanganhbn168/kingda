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

class Product extends Model
{
    use HasActiveStatus;
    use HasFeaturedStatus;
    use HasSortOrder;

    protected $fillable = [
        'category_id',
        'sku',
        'price',
        'sale_price',
        'unit',
        'is_featured',
        'is_active',
        'is_home',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_home' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ProductTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function publishedTranslation(): HasOne
    {
        return $this->translation()->where('is_published', true);
    }

    public function translationFor(?string $locale = null): HasOne
    {
        return $this->hasOne(ProductTranslation::class)
            ->where('locale', $locale ?: app()->getLocale());
    }

    public function displayImageUrl(?ProductTranslation $translation = null, array $collections = ['thumbnail', 'hero'], string $fallbackLocale = 'vi'): ?string
    {
        $translation ??= $this->relationLoaded('translation')
            ? $this->translation
            : $this->translationFor(app()->getLocale())->with('media')->first();

        foreach ($collections as $collection) {
            if (filled($url = $translation?->getFirstMediaUrl($collection))) {
                return $url;
            }
        }

        if ($translation?->locale === $fallbackLocale) {
            return null;
        }

        $fallback = $this->relationLoaded('translations')
            ? $this->translations->firstWhere('locale', $fallbackLocale)
            : $this->translationFor($fallbackLocale)->with('media')->first();

        foreach ($collections as $collection) {
            if (filled($url = $fallback?->getFirstMediaUrl($collection))) {
                return $url;
            }
        }

        return null;
    }

    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'linkable');
    }

    public function contactMessages(): MorphMany
    {
        return $this->morphMany(ContactMessage::class, 'related');
    }

    public function getEffectivePriceAttribute(): ?string
    {
        return $this->sale_price ?: $this->price;
    }

    public function scopeWithPublishedTranslation(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();

        return $query->whereHas('translations', function (Builder $query) use ($locale) {
            $query->where('locale', $locale)->where('is_published', true);
        });
    }
}
