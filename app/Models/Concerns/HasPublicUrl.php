<?php

namespace App\Models\Concerns;

use App\Enums\RouteSegments;
use Illuminate\Support\Collection;

trait HasPublicUrl
{
    /**
     * Define the root route segment key (e.g. 'products', 'news', 'industries')
     * This key must be registered in RouteSegments::MAP.
     */
    abstract protected function routeSegmentKey(): string;

    /**
     * Provide any additional segments after the root segment.
     * By default it returns the slug, but you can override this for nested structures
     * like categories and products.
     * 
     * @return array<int, string|null>
     */
    protected function urlSegments(): array
    {
        return [$this->slug ?? null];
    }

    /**
     * Get the public URL attribute.
     */
    public function getPublicUrlAttribute(): string
    {
        return RouteSegments::url(
            $this->routeSegmentKey(),
            $this->locale,
            ...$this->urlSegments()
        );
    }
}
