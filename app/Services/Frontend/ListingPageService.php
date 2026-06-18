<?php

namespace App\Services\Frontend;

use App\Models\Page;
use App\Settings\SiteSettings;

class ListingPageService
{
    public function __construct(
        private readonly FrontendUrlBuilder $urls,
    ) {}

    public function translation(string $locale, string $type): object
    {
        $page = Page::query()
            ->active()
            ->where('key', $type)
            ->with([
                'translations' => fn ($query) => $query
                    ->where('locale', $locale)
                    ->where('is_published', true),
            ])
            ->first();

        $translation = $page?->translations->first();

        if ($translation) {
            return $translation;
        }

        $title = match ($type) {
            'about' => $locale === 'vi' ? 'Giới thiệu' : 'About',
            'products' => $locale === 'vi' ? 'Sản phẩm' : 'Products',
            'news' => $locale === 'vi' ? 'Tin tức' : 'News',
            'contact' => $locale === 'vi' ? 'Liên hệ' : 'Contact',
            'industries' => $locale === 'vi' ? 'Lĩnh vực' : 'Industries',
        };

        $description = match ($type) {
            'about' => $locale === 'vi'
                ? 'Kingda là doanh nghiệp cung cấp giải pháp vật liệu in ấn điện tử, sơn phủ và vật liệu chức năng cho sản xuất công nghiệp.'
                : 'Kingda provides electronic printing materials, coating and functional material solutions for industrial manufacturing.',
            'products' => $locale === 'vi'
                ? 'Danh mục sản phẩm và giải pháp vật liệu ứng dụng của Kingda.'
                : 'Kingda product categories and application material solutions.',
            'news' => $locale === 'vi'
                ? 'Cập nhật tin tức, hoạt động và kiến thức ứng dụng vật liệu.'
                : 'Latest updates, activities and material application insights.',
            'contact' => $locale === 'vi'
                ? 'Kết nối với Kingda để được tư vấn giải pháp phù hợp.'
                : 'Contact Kingda for tailored solution consulting.',
            'industries' => $locale === 'vi'
                ? 'Các lĩnh vực ứng dụng vật liệu, mực in và sơn phủ công nghiệp của Kingda.'
                : 'Industries applying Kingda materials, inks and industrial coating solutions.',
        };

        $url = $this->urls->listing($type, $locale);

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
