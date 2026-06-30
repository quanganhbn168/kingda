<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'group_key',
        'parent_id',
        'locale',
        'label',
        'link_type',
        'linkable_type',
        'linkable_id',
        'url',
        'target',
        'rel',
        'icon',
        'css_class',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'resolved_url',
        'resolved_label',
        'resolved_target',
        'has_children',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
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

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function activeChildrenRecursive(): HasMany
    {
        return $this->children()
            ->where('locale', app()->getLocale())
            ->where('is_active', true)
            ->with(['linkable.translations', 'activeChildrenRecursive']);
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getResolvedUrlAttribute(): ?string
    {
        if ($this->link_type === 'custom') {
            return $this->url;
        }

        if (! $this->linkable) {
            return $this->url;
        }

        if ($this->linkable instanceof Post) {
            $translation = $this->linkable->resolveTranslation($this->locale, publishedOnly: true);

            return $translation
                ? $this->relativeUrl($translation->public_url)
                : $this->url;
        }

        if (method_exists($this->linkable, 'translationFor')) {
            if ($this->linkable->relationLoaded('translations')) {
                $translation = $this->linkable->translations->firstWhere('locale', $this->locale);
            } else {
                $translation = $this->linkable->translationFor($this->locale)->first();
            }

            if ($translation && isset($translation->public_url)) {
                return $this->relativeUrl($translation->public_url);
            }
        }

        if (isset($this->linkable->public_url)) {
            return $this->relativeUrl($this->linkable->public_url);
        }

        return $this->url;
    }

    public function getResolvedLabelAttribute(): string
    {
        return $this->label;
    }

    public function getResolvedTargetAttribute(): string
    {
        return $this->target ?: '_self';
    }

    public function getHasChildrenAttribute(): bool
    {
        if ($this->relationLoaded('childrenRecursive')) {
            return $this->childrenRecursive->isNotEmpty();
        }

        if ($this->relationLoaded('children')) {
            return $this->children->isNotEmpty();
        }

        return $this->children()->exists();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLocale(Builder $query, ?string $locale = null): Builder
    {
        return $query->where('locale', $locale ?: app()->getLocale());
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    private function relativeUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        if ($urlHost && $appHost && $urlHost === $appHost) {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $query = parse_url($url, PHP_URL_QUERY);

            return $query ? $path.'?'.$query : $path;
        }

        return $url;
    }
}
