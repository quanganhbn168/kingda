<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Category extends Model
{
    use HasActiveStatus;
    use HasSortOrder;

    public const TYPE_PRODUCT = 'product';
    public const TYPE_SERVICE = 'service';
    public const TYPE_POST = 'post';

    protected $fillable = [
        'parent_id',
        'type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(CategoryTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function publishedTranslation(): HasOne
    {
        return $this->translation()->where('is_published', true);
    }

    public function translationFor(?string $locale = null): HasOne
    {
        return $this->hasOne(CategoryTranslation::class)
            ->where('locale', $locale ?: app()->getLocale());
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'linkable');
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeProduct(Builder $query): Builder
    {
        return $query->where('type', CategoryType::Product->value);
    }

    public function scopeService(Builder $query): Builder
    {
        return $query->where('type', CategoryType::Service->value);
    }

    public function scopePost(Builder $query): Builder
    {
        return $query->where('type', CategoryType::Post->value);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function descendantIds(): array
    {
        $ids = [];

        $this->children()
            ->get(['id', 'parent_id'])
            ->each(function (self $child) use (&$ids): void {
                $ids[] = $child->id;
                $ids = [
                    ...$ids,
                    ...$child->descendantIds(),
                ];
            });

        return $ids;
    }

    public function descendantsAndSelfIds(): array
    {
        return [
            $this->id,
            ...$this->descendantIds(),
        ];
    }
}
