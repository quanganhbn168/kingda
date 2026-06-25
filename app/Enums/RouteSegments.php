<?php

namespace App\Enums;

/**
 * Single source of truth for all localized URL segment mappings.
 *
 * Instead of hardcoding `$locale === 'vi' ? 'san-pham' : 'products'`
 * in 11+ files, every part of the codebase should use this registry.
 */
final class RouteSegments
{
    /**
     * Segment mapping: key => [vi => vietnamese_slug, * => default_slug].
     * The '*' key is used for all non-Vietnamese locales (en, zh, etc.).
     */
    private const MAP = [
        'products'   => ['vi' => 'san-pham',    '*' => 'products'],
        'news'       => ['vi' => 'tin-tuc',     '*' => 'news'],
        'contact'    => ['vi' => 'lien-he',     '*' => 'contact'],
        'about'      => ['vi' => 'gioi-thieu',  '*' => 'about'],
        'industries' => ['vi' => 'linh-vuc',    '*' => 'industries'],
        'services'   => ['vi' => 'dich-vu',     '*' => 'services'],
        'branches'   => ['vi' => 'chi-nhanh',   '*' => 'branches'],
    ];

    private const DEFAULT_LOCALE = 'vi';

    /**
     * Get the URL segment for a given key and locale.
     *
     * Example: RouteSegments::for('products', 'vi') → 'san-pham'
     * Example: RouteSegments::for('products', 'en') → 'products'
     */
    public static function for(string $key, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $segments = self::MAP[$key] ?? null;

        if (! $segments) {
            return $key;
        }

        return $segments[$locale] ?? $segments['*'] ?? $key;
    }

    /**
     * Get the locale prefix for URLs.
     * Vietnamese (default locale) has no prefix; others get '/{locale}'.
     *
     * Example: RouteSegments::prefix('vi') → ''
     * Example: RouteSegments::prefix('en') → '/en'
     */
    public static function prefix(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return self::isDefault($locale) ? '' : '/' . $locale;
    }

    /**
     * Build a full URL for a given section key and locale, with optional path segments.
     *
     * Example: RouteSegments::url('products', 'vi') → 'https://kingda.com/san-pham'
     * Example: RouteSegments::url('products', 'en', 'category-slug', 'product-slug')
     *        → 'https://kingda.com/en/products/category-slug/product-slug'
     */
    public static function url(string $key, ?string $locale = null, string ...$segments): string
    {
        $locale = $locale ?: app()->getLocale();
        $prefix = self::prefix($locale);
        $section = self::for($key, $locale);

        $path = collect([$section, ...$segments])
            ->filter()
            ->join('/');

        return url($prefix . '/' . $path);
    }

    /**
     * Build a home URL for the given locale.
     *
     * Example: RouteSegments::home('vi') → 'https://kingda.com/'
     * Example: RouteSegments::home('en') → 'https://kingda.com/en'
     */
    public static function home(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return url(self::isDefault($locale) ? '/' : '/' . $locale);
    }

    /**
     * Build a path (without domain) for the given locale and path segments.
     *
     * Example: RouteSegments::path('vi', 'san-pham') → '/san-pham'
     * Example: RouteSegments::path('en', 'products') → '/en/products'
     */
    public static function path(?string $locale, string $pathSegment = ''): string
    {
        $locale = $locale ?: app()->getLocale();
        $pathSegment = trim($pathSegment, '/');

        if (self::isDefault($locale)) {
            return $pathSegment === '' ? '/' : '/' . $pathSegment;
        }

        return $pathSegment === '' ? '/' . $locale : '/' . $locale . '/' . $pathSegment;
    }

    /**
     * Check if the given locale is the default (Vietnamese).
     */
    public static function isDefault(?string $locale = null): bool
    {
        return ($locale ?: app()->getLocale()) === self::DEFAULT_LOCALE;
    }

    /**
     * Get all registered segment keys.
     */
    public static function keys(): array
    {
        return array_keys(self::MAP);
    }
}
