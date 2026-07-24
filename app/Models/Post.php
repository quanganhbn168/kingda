<?php

namespace App\Models;

use App\Enums\Locale;
use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\HasFeaturedStatus;
use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        static::creating(function (self $post): void {
            if ((int) ($post->sort_order ?? 0) <= 0) {
                $post->sort_order = ((int) self::query()->max('sort_order')) + 10;
            }
        });

        static::saving(function (self $post): void {
            if (! $post->category_id) {
                if ($post->exists && ! $post->isDirty('category_id')) {
                    return;
                }

                throw ValidationException::withMessages([
                    'category_id' => 'Bài viết phải thuộc một danh mục bài viết.',
                ]);
            }

            if ($post->exists && ! $post->isDirty('category_id')) {
                return;
            }

            $category = Category::query()->find($post->category_id);

            if (! $category || $category->type !== Category::TYPE_POST) {
                throw ValidationException::withMessages([
                    'category_id' => 'Danh mục bài viết không hợp lệ.',
                ]);
            }
        });
    }

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
        return $this->translation();
    }

    public function translationFor(?string $locale = null): HasOne
    {
        return $this->hasOne(PostTranslation::class)
            ->where('locale', $locale ?: app()->getLocale());
    }

    public function translationVi(): HasOne
    {
        return $this->hasOne(PostTranslation::class)
            ->where('locale', Locale::Vietnamese->value);
    }

    public function translationEn(): HasOne
    {
        return $this->hasOne(PostTranslation::class)
            ->where('locale', Locale::English->value);
    }

    public function translationZh(): HasOne
    {
        return $this->hasOne(PostTranslation::class)
            ->where('locale', Locale::Chinese->value);
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
            return $translation;
        }

        if ($locale === $fallbackLocale) {
            return null;
        }

        $fallback = $translations->first(
            fn (PostTranslation $item): bool => $item->locale === $fallbackLocale && $item->isUsable(),
        );

        return $fallback;
    }

    public function useResolvedTranslation(?string $locale = null, bool $publishedOnly = false): static
    {
        $translation = $this->resolveTranslation($locale, publishedOnly: $publishedOnly);
        $translation?->setRelation('post', $this);
        $this->setRelation('translation', $translation);

        return $this;
    }

    public function getSlugUrlAttribute(): ?string
    {
        $translation = $this->resolveTranslation('vi');

        if (! $translation) {
            return null;
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
            $query->where('locale', $locale)->usable();
        })->with(['translation' => fn ($query) => $query->where('locale', $locale)]);
    }

    public function scopeWithRoutableCategory(Builder $query): Builder
    {
        return $query->whereHas('category', fn (Builder $query): Builder => $query
            ->post()
            ->active());
    }

    public function scopeWithPublishedTranslation(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();

        if ($locale === 'vi') {
            return $query->whereHas('translations', fn (Builder $query): Builder => $query
                ->locale('vi')
                ->usable());
        }

        return $query->where(function (Builder $query) use ($locale): void {
            $query
                ->whereHas('translations', fn (Builder $translationQuery): Builder => $translationQuery
                    ->locale($locale)
                    ->usable())
                ->orWhere(function (Builder $fallbackQuery) use ($locale): void {
                    $fallbackQuery
                        ->whereDoesntHave('translations', fn (Builder $translationQuery): Builder => $translationQuery
                            ->locale($locale)
                            ->usable())
                        ->whereHas('translations', fn (Builder $translationQuery): Builder => $translationQuery
                            ->locale('vi')
                            ->usable());
                });
        });
    }
}
