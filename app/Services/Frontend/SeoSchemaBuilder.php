<?php

namespace App\Services\Frontend;

use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\SiteSettings;

class SeoSchemaBuilder
{
    public function __construct(
        private readonly FrontendUrlBuilder $urls,
    ) {}

    public function collection(object $translation, string $name, array $items, array $breadcrumbs): array
    {
        $url = $translation->public_url ?? url()->current();

        return $this->graph([
            $this->organization(),
            $this->website(),
            [
                '@type' => 'CollectionPage',
                '@id' => $url . '#webpage',
                'url' => $url,
                'name' => $translation->seo_title ?? $translation->title ?? $name,
                'description' => $translation->seo_description ?? $translation->description ?? $translation->excerpt ?? null,
                'inLanguage' => app()->getLocale(),
                'isPartOf' => ['@id' => url('/#website')],
                'publisher' => ['@id' => url('/#organization')],
                'mainEntity' => ['@id' => $url . '#itemlist'],
            ],
            $this->itemList($items, $name, $url . '#itemlist'),
            $this->breadcrumb($breadcrumbs),
        ]);
    }

    public function page(object $translation, string $type = 'WebPage', array $breadcrumbs = []): array
    {
        $url = $translation->public_url ?? url()->current();
        $name = $translation->seo_title
            ?? $translation->title
            ?? $translation->name
            ?? app(SiteSettings::class)->site_name;
        $description = $translation->seo_description
            ?? $translation->description
            ?? $translation->excerpt
            ?? null;

        return $this->graph([
            $this->organization(),
            $this->website(),
            [
                '@type' => $type,
                '@id' => $url . '#webpage',
                'url' => $url,
                'name' => $name,
                'description' => $description,
                'inLanguage' => app()->getLocale(),
                'isPartOf' => ['@id' => url('/#website')],
                'publisher' => ['@id' => url('/#organization')],
            ],
            $this->breadcrumb($breadcrumbs),
        ]);
    }

    public function product(Product $product, ProductTranslation $translation, array $breadcrumbs, ?string $productUrl = null, ?string $categoryName = null): array
    {
        $image = $this->resolveOgImage($translation);
        $price = $product->effective_price;
        $productUrl ??= $this->urls->product($product, $translation, app()->getLocale()) ?: $translation->public_url;

        $productSchema = [
            '@type' => 'Product',
            '@id' => $productUrl . '#product',
            'name' => $translation->name,
            'description' => $translation->meta_description,
            'sku' => $product->sku,
            'image' => $image ? [$image] : null,
            'category' => $categoryName,
            'brand' => ['@id' => url('/#organization')],
            'url' => $productUrl,
        ];

        if ($price) {
            $productSchema['offers'] = [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'VND',
                'availability' => 'https://schema.org/InStock',
                'url' => $productUrl,
            ];
        }

        return $this->graph([
            $this->organization(),
            $this->website(),
            [
                '@type' => 'WebPage',
                '@id' => $productUrl . '#webpage',
                'url' => $productUrl,
                'name' => $translation->meta_title,
                'description' => $translation->meta_description,
                'inLanguage' => app()->getLocale(),
                'isPartOf' => ['@id' => url('/#website')],
                'mainEntity' => ['@id' => $productUrl . '#product'],
            ],
            $productSchema,
            $this->breadcrumb($breadcrumbs),
        ]);
    }

    public function newsArticle(Post $post, PostTranslation $translation, array $breadcrumbs): array
    {
        $image = $this->resolveOgImage($translation);

        return $this->graph([
            $this->organization(),
            $this->website(),
            [
                '@type' => 'NewsArticle',
                '@id' => $translation->public_url . '#article',
                'headline' => $translation->title,
                'description' => $translation->meta_description,
                'image' => $image ? [$image] : null,
                'datePublished' => $translation->published_at?->toIso8601String(),
                'dateModified' => $translation->updated_at?->toIso8601String(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $post->author?->name ?: app(SiteSettings::class)->site_name,
                ],
                'publisher' => ['@id' => url('/#organization')],
                'mainEntityOfPage' => ['@id' => $translation->public_url . '#webpage'],
                'url' => $translation->public_url,
            ],
            [
                '@type' => 'WebPage',
                '@id' => $translation->public_url . '#webpage',
                'url' => $translation->public_url,
                'name' => $translation->meta_title,
                'description' => $translation->meta_description,
                'inLanguage' => app()->getLocale(),
                'isPartOf' => ['@id' => url('/#website')],
            ],
            $this->breadcrumb($breadcrumbs),
        ]);
    }

    public function resolveOgImage(object $translation): ?string
    {
        if (method_exists($translation, 'getFirstMediaUrl') && $translation->getFirstMediaUrl('og_image')) {
            return $translation->getFirstMediaUrl('og_image');
        }

        if (method_exists($translation, 'getFirstMediaUrl') && $translation->getFirstMediaUrl('hero')) {
            return $translation->getFirstMediaUrl('hero');
        }

        if (method_exists($translation, 'getFirstMediaUrl') && $translation->getFirstMediaUrl('thumbnail')) {
            return $translation->getFirstMediaUrl('thumbnail');
        }

        return null;
    }

    private function organization(): array
    {
        $site = app(SiteSettings::class);
        $company = app(CompanySettings::class);
        $contact = app(ContactSettings::class);
        $logo = $site->logo ? asset('storage/' . $site->logo) : null;

        return [
            '@type' => 'Organization',
            '@id' => url('/#organization'),
            'name' => $company->company_name ?: $site->site_name,
            'alternateName' => collect([$company->company_short_name, $company->company_english_name])->filter()->values()->all() ?: null,
            'url' => url('/'),
            'logo' => $logo,
            'email' => collect($contact->emails ?? [])->first(),
            'telephone' => collect($contact->phones ?? $contact->hotlines ?? [])->first(),
            'address' => $company->office_address ?: $company->registered_address ?: $contact->default_address,
            'sameAs' => collect($contact->social_links ?? [])->pluck('url')->filter()->values()->all() ?: null,
        ];
    }

    private function website(): array
    {
        $site = app(SiteSettings::class);

        return [
            '@type' => 'WebSite',
            '@id' => url('/#website'),
            'url' => url('/'),
            'name' => $site->site_name,
            'description' => $site->site_slogan,
            'publisher' => ['@id' => url('/#organization')],
            'inLanguage' => app()->getLocale(),
        ];
    }

    private function breadcrumb(array $items): ?array
    {
        $items = collect($items)
            ->filter(fn (array $item): bool => filled($item['name'] ?? null) && filled($item['url'] ?? null))
            ->values();

        if ($items->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items->map(fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ])->all(),
        ];
    }

    private function itemList(array $items, string $name, string $id): ?array
    {
        $items = collect($items)
            ->filter(fn (array $item): bool => filled($item['name'] ?? null) && filled($item['url'] ?? null))
            ->values();

        if ($items->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'ItemList',
            '@id' => $id,
            'name' => $name,
            'itemListElement' => $items->map(fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => $item['url'],
                'name' => $item['name'],
            ])->all(),
        ];
    }

    private function graph(array $nodes): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => collect($nodes)
                ->filter()
                ->map(fn (array $node): array => array_filter($node, fn ($value): bool => $value !== null && $value !== []))
                ->values()
                ->all(),
        ];
    }
}
