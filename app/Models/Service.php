<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'code',
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

    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ServiceTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function publishedTranslation(): HasOne
    {
        return $this->translation()->where('is_published', true);
    }

    public function translationFor(?string $locale = null): HasOne
    {
        return $this->hasOne(ServiceTranslation::class)
            ->where('locale', $locale ?: app()->getLocale());
    }

    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'linkable');
    }

    public function contactMessages(): MorphMany
    {
        return $this->morphMany(ContactMessage::class, 'related');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeWithPublishedTranslation(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();

        return $query->whereHas('translations', function (Builder $query) use ($locale) {
            $query->where('locale', $locale)->where('is_published', true);
        });
    }
}
