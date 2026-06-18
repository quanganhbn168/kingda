<?php

namespace App\Services\Frontend;

use Illuminate\Support\Arr;

class LocalizedContent
{
    public static function block(?array $data, string $locale, ?string $fallback = null): array
    {
        $fallback ??= config('app.fallback_locale', 'vi');

        return Arr::get($data ?: [], $locale)
            ?: Arr::get($data ?: [], $fallback)
            ?: [];
    }

    public static function items(?array $items, string $locale, ?string $fallback = null): array
    {
        $fallback ??= config('app.fallback_locale', 'vi');

        return collect($items ?: [])
            ->map(function (array $item) use ($locale, $fallback): array {
                $localized = Arr::get($item, $locale)
                    ?: Arr::get($item, $fallback)
                    ?: [];

                return [
                    ...collect($item)->except(['vi', 'en', 'zh'])->all(),
                    ...$localized,
                ];
            })
            ->values()
            ->all();
    }

    public static function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
