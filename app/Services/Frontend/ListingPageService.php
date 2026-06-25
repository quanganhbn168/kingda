<?php

namespace App\Services\Frontend;

use App\Settings\SiteSettings;

class ListingPageService
{
    public function __construct(
        private readonly FrontendUrlBuilder $urls,
    ) {}

    public function translation(string $locale, string $type): object
    {
        $title = match ($type) {
            'home' => __('ui.common.home', [], $locale),
            'about' => __('ui.common.about', [], $locale),
            'products' => __('ui.common.products', [], $locale),
            'news' => __('ui.common.news', [], $locale),
            'contact' => __('ui.common.contact', [], $locale),
            'industries' => __('ui.common.industries', [], $locale),
            default => ucfirst($type),
        };

        $description = match ($type) {
            'home' => __('ui.seo.home.description', [], $locale),
            'about' => __('ui.seo.about.description', [], $locale),
            'products' => __('ui.seo.products.description', [], $locale),
            'news' => __('ui.seo.news.description', [], $locale),
            'contact' => __('ui.seo.contact.description', [], $locale),
            'industries' => __('ui.seo.industries.description', [], $locale),
            default => '',
        };

        $url = $type === 'home'
            ? \App\Enums\RouteSegments::home($locale)
            : \App\Enums\RouteSegments::url($type, $locale);

        return (object) [
            'title' => $title,
            'headline' => $title,
            'subheadline' => $description,
            'excerpt' => $description,
            'content' => null,
            'seo_title' => $title,
            'seo_description' => $description,
            'meta_robots' => 'index,follow',
            'canonical_url' => $url,
            'public_url' => $url,
            'og_title' => $title,
            'og_description' => $description,
            'site_name' => app(SiteSettings::class)->site_name,
        ];
    }

    public function alternateUrls(string $type): array
    {
        return collect(['vi', 'en', 'zh'])
            ->mapWithKeys(fn (string $locale): array => [$locale => $this->translation($locale, $type)->public_url])
            ->all();
    }
}
