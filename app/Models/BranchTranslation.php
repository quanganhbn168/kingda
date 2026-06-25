<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchTranslation extends Model
{
    use \App\Models\Concerns\HasPublicUrl;
    protected $fillable = [
        'branch_id',
        'locale',
        'name',
        'slug',
        'short_address',
        'display_address',
        'description',
        'working_hours',
        'seo_title',
        'seo_description',
        'meta_robots',
        'canonical_url',
    ];

    protected $appends = [
        'public_url',
        'resolved_seo_title',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    protected function routeSegmentKey(): string
    {
        return 'branches';
    }

    public function getResolvedSeoTitleAttribute(): string
    {
        return $this->seo_title ?: $this->name;
    }

    public function scopeLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    public function scopeSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', trim($slug, '/'));
    }
}