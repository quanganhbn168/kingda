<?php

namespace App\Models;

use App\Enums\PageTemplate;
use App\Enums\PageType;
use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasActiveStatus;
    use HasSortOrder;
    use SoftDeletes;

    public const TYPE_PAGE = 'page';
    public const TYPE_LANDING = 'landing';
    public const TYPE_POLICY = 'policy';

    public const TEMPLATE_DEFAULT = 'default';
    public const TEMPLATE_HOME = 'home';
    public const TEMPLATE_ABOUT = 'about';
    public const TEMPLATE_CONTACT = 'contact';
    public const TEMPLATE_LANDING = 'landing';

    protected $fillable = [
        'key',
        'type',
        'template',
        'is_home',
        'is_system',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_home' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (Page $page): void {
            if (! $page->is_home) {
                return;
            }

            static::query()
                ->whereKeyNot($page->getKey())
                ->where('is_home', true)
                ->update(['is_home' => false]);

            $page->translations()->update(['slug' => null]);
        });
    }

    public static function types(): array
    {
        return PageType::options();
    }

    public static function templates(): array
    {
        return PageTemplate::options();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function currentTranslation(): HasOne
    {
        return $this->hasOne(PageTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function translation(?string $locale = null): ?PageTranslation
    {
        $locale ??= app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first();
    }

    public function translationFor(?string $locale = null): HasMany
    {
        return $this->translations()
            ->where('locale', $locale ?: app()->getLocale());
    }

    public function publishedTranslation(?string $locale = null): ?PageTranslation
    {
        $locale ??= app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return $this->translations
                ->where('locale', $locale)
                ->where('is_published', true)
                ->first();
        }

        return $this->translations()
            ->where('locale', $locale)
            ->where('is_published', true)
            ->first();
    }

    public function getTemplateViewAttribute(): string
    {
        $template = $this->template ?: PageTemplate::Default->value;

        $view = 'frontend.pages.templates.' . $template;

        return view()->exists($view)
            ? $view
            : 'frontend.pages.templates.default';
    }

    public function scopeHome(Builder $query): Builder
    {
        return $query->where('is_home', true);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeTemplate(Builder $query, string $template): Builder
    {
        return $query->where('template', $template);
    }

    public function scopeWithPublishedTranslation(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', function (Builder $query) use ($locale) {
            $query->where('locale', $locale)
                ->where('is_published', true);
        });
    }

    public function scopeByTranslatedSlug(Builder $query, string $locale, string $slug): Builder
    {
        return $query->whereHas('translations', function (Builder $query) use ($locale, $slug) {
            $query->where('locale', $locale)
                ->where('slug', trim($slug, '/'));
        });
    }
}
