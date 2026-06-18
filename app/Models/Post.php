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

        return $query->whereHas('translations', function (Builder $query) use ($locale) {
            $query->where('locale', $locale)->where('is_published', true);
        });
    }
}
