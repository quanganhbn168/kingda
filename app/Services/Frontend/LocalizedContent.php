<?php

namespace App\Services\Frontend;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class LocalizedContent
{
    public static function block(?array $data, string $locale, ?string $fallback = null): array
    {
        $fallback ??= config('app.fallback_locale', 'vi');

        return Arr::get($data ?: [], $locale)
            ?: Arr::get($data ?: [], $fallback)
            ?: [];
    }

    public static function items(?array $items, string $locale, ?string $fallback = null): Collection
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
            ->values();
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

    public static function toFluent(mixed $data): mixed
    {
        if ($data instanceof Collection) {
            return $data->map(fn ($item) => static::toFluent($item));
        }

        if (! is_array($data)) {
            return $data;
        }

        if (empty($data)) {
            // Default empty array to a new Fluent object so property access $empty->title returns null safely
            return new Fluent();
        }

        $isAssoc = array_keys($data) !== range(0, count($data) - 1);

        if ($isAssoc) {
            $fluentData = [];
            foreach ($data as $key => $value) {
                $fluentData[$key] = static::toFluent($value);
            }
            return new Fluent($fluentData);
        }

        // Sequential arrays become collections of fluent objects
        return collect($data)->map(fn ($item) => static::toFluent($item));
    }
}
