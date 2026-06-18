<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasFeaturedStatus
{
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
