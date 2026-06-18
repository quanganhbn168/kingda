<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'location',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function activeItems(): HasMany
    {
        return $this->items()
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function rootItems(): HasMany
    {
        return $this->items()
            ->whereNull('parent_id')
            ->orderBy('sort_order');
    }

    public function activeRootItems(?string $locale = null): HasMany
    {
        return $this->rootItems()
            ->where('is_active', true)
            ->where('locale', $locale ?: app()->getLocale())
            ->with(['activeChildren' => fn ($query) => $query->where('locale', $locale ?: app()->getLocale())]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
