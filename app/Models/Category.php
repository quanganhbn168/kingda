<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Enums\Locale;
use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        static::saving(function (self $category): void {
            if (
                $category->type === CategoryType::Product->value
                && (! $category->parent_id || (int) $category->parent_id === 0)
            ) {
                $category->parent_id = null;
            }

            if (
                $category->type !== CategoryType::Product->value
                || ! $category->isDirty('parent_id')
                || ! $category->parent_id
            ) {
                return;
            }

            $parent = self::query()->find($category->parent_id);
            $reason = $parent
                ? $parent->productChildCreationBlockReason(fresh: true)
                : 'Danh mục cha không hợp lệ.';

            if ($reason) {
                throw ValidationException::withMessages([
                    'parent_id' => $reason,
                ]);
            }
        });

        static::creating(function (self $category): void {
            if (
                $category->type === CategoryType::Product->value
                && $category->is_active === null
            ) {
                $category->is_active = true;
            }

            if (
                $category->type === CategoryType::Product->value
                && (int) ($category->sort_order ?? 0) <= 0
            ) {
                $category->sort_order = self::nextProductCategorySortOrder($category->parent_id);
            }
        });

        static::updating(function (self $category): void {
            if (
                $category->type === CategoryType::Product->value
                && $category->isDirty('parent_id')
            ) {
                $category->sort_order = self::nextProductCategorySortOrder(
                    $category->parent_id,
                    $category->getKey(),
                );
            }
        });

        static::deleting(function (self $category): ?bool {
            if ($category->type === CategoryType::Product->value) {
                return $category->canDeleteProductCategory(fresh: true) ? null : false;
            }

            return $category->children()->exists() ? false : null;
        });
    }

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

    public function translationVi(): HasOne
    {
        return $this->translationFor(Locale::Vietnamese->value);
    }

    public function translationEn(): HasOne
    {
        return $this->translationFor(Locale::English->value);
    }

    public function translationZh(): HasOne
    {
        return $this->translationFor(Locale::Chinese->value);
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

    public function canAcceptProductChildren(bool $fresh = false): bool
    {
        return $this->productChildCreationBlockReason($fresh) === null;
    }

    public function productChildCreationBlockReason(bool $fresh = false): ?string
    {
        if ($this->type !== CategoryType::Product->value) {
            return 'Chỉ danh mục sản phẩm mới có thể làm danh mục cha.';
        }

        $hasProducts = (! $fresh && array_key_exists('products_count', $this->attributes))
            ? ((int) $this->attributes['products_count'] > 0)
            : $this->products()->exists();

        return $hasProducts
            ? 'Danh mục cha đang chứa sản phẩm. Hãy chuyển toàn bộ sản phẩm xuống một danh mục lá trước khi thêm danh mục con.'
            : null;
    }

    public function canReceiveProducts(bool $fresh = false): bool
    {
        return $this->productAssignmentBlockReason($fresh) === null;
    }

    public function productAssignmentBlockReason(bool $fresh = false): ?string
    {
        if ($this->type !== CategoryType::Product->value) {
            return 'Sản phẩm chỉ có thể thuộc một danh mục sản phẩm.';
        }

        $hasChildren = (! $fresh && array_key_exists('children_count', $this->attributes))
            ? ((int) $this->attributes['children_count'] > 0)
            : $this->children()->exists();

        return $hasChildren
            ? 'Sản phẩm chỉ có thể thuộc danh mục lá, không còn danh mục con.'
            : null;
    }

    public function canDeleteProductCategory(bool $fresh = false): bool
    {
        return $this->productCategoryDeletionBlockReason($fresh) === null;
    }

    public function productCategoryDeletionBlockReason(bool $fresh = false): ?string
    {
        if ($this->type !== CategoryType::Product->value) {
            return null;
        }

        $hasProducts = (! $fresh && array_key_exists('products_count', $this->attributes))
            ? ((int) $this->attributes['products_count'] > 0)
            : $this->products()->exists();

        $hasChildren = (! $fresh && array_key_exists('children_count', $this->attributes))
            ? ((int) $this->attributes['children_count'] > 0)
            : $this->children()->exists();

        if ($hasProducts && $hasChildren) {
            return 'Danh mục đang chứa sản phẩm và danh mục con. Hãy chuyển sản phẩm, sau đó chuyển hoặc xóa hết danh mục con trước.';
        }

        if ($hasProducts) {
            return 'Danh mục đang chứa sản phẩm. Hãy chuyển sản phẩm sang danh mục lá khác trước.';
        }

        if ($hasChildren) {
            return 'Danh mục đang có danh mục con. Hãy chuyển hoặc xóa hết danh mục con trước.';
        }

        return null;
    }

    public function displayImageUrl(?CategoryTranslation $translation = null, array $collections = ['thumbnail', 'hero'], string $fallbackLocale = 'vi'): ?string
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

    private static function nextProductCategorySortOrder(?int $parentId, int|string|null $ignoreId = null): int
    {
        $maximum = (int) self::query()
            ->product()
            ->where('parent_id', $parentId)
            ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->max('sort_order');

        return max(0, $maximum) + 10;
    }
}
