<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\HasFeaturedStatus;
use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Industry extends Model implements HasMedia
{
    use HasActiveStatus;
    use HasFeaturedStatus;
    use InteractsWithMedia;
    use HasSortOrder;

    protected $fillable = [
        'icon',
        'url',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(IndustryTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(IndustryTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function publishedTranslation(): HasOne
    {
        return $this->translation()->where('is_published', true);
    }

    public function scopeWithPublishedTranslation(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();

        return $query->whereHas('translations', fn (Builder $query) => $query
            ->where('locale', $locale)
            ->where('is_published', true));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->useDisk('public')->singleFile();

        $this->addMediaCollection('hero')->useDisk('public')->singleFile();
    }
}
