<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Frontend\FrontendUrlBuilder;
use App\Services\Frontend\ListingPageService;
use App\Services\Frontend\ProductCatalogService;
use App\Services\Frontend\SeoSchemaBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalog,
        private readonly FrontendUrlBuilder $urls,
        private readonly ListingPageService $listingPages,
        private readonly SeoSchemaBuilder $schema,
    ) {}

    public function index(Request $request): View
    {
        return $this->renderListing($request, app()->getLocale());
    }

    public function category(Request $request, string $categorySlug): View
    {
        return $this->renderListing($request, app()->getLocale(), $categorySlug);
    }

    public function localizedCategory(Request $request, string $locale, string $categorySlug): View
    {
        return $this->renderListing($request, app()->getLocale(), $categorySlug);
    }

    public function show(Request $request, string $categorySlug, string $productSlug): View
    {
        return $this->renderDetail(app()->getLocale(), $categorySlug, $productSlug);
    }

    public function localizedShow(Request $request, string $locale, string $categorySlug, string $productSlug): View
    {
        return $this->renderDetail(app()->getLocale(), $categorySlug, $productSlug);
    }

    private function renderListing(Request $request, string $locale, ?string $categorySlug = null): View
    {
        $data = $this->catalog->listing($request, $locale, $categorySlug);
        $translation = $this->listingPages->translation($locale, 'products');

        return view('frontend.pages.templates.products', [
            ...$data,
            'translation' => $translation,
            'alternateUrls' => $this->listingPages->alternateUrls('products'),
            'ogImage' => null,
            'schema' => $this->schema->collection(
                $translation,
                $data['activeCategory']?->translation?->name ?: __('ui.common.products'),
                $data['products']->getCollection()->map(fn (Product $product): array => [
                    'name' => $product->translation?->name,
                    'url' => $this->urls->product($product, $product->translation, $locale),
                ])->all(),
                [
                    ['name' => __('ui.common.home'), 'url' => $this->urls->home($locale)],
                    ['name' => __('ui.common.products'), 'url' => $this->urls->listing('products', $locale)],
                ]
            ),
        ]);
    }

    private function renderDetail(string $locale, string $categorySlug, string $productSlug): View
    {
        $data = $this->catalog->detail($locale, $categorySlug, $productSlug);
        $product = $data['product'];
        $translation = $data['translation'];
        $categoryTranslation = $data['categoryTranslation'];
        $categoryUrl = $product->category
            ? $this->urls->category($product->category, $categoryTranslation, $locale)
            : null;
        $productUrl = $this->urls->product($product, $translation, $locale);

        return view('frontend.pages.templates.product-detail', [
            ...$data,
            'categoryUrl' => $categoryUrl,
            'alternateUrls' => $this->modelAlternateUrls($product),
            'ogImage' => $this->schema->resolveOgImage($translation),
            'schema' => $this->schema->product($product, $translation, [
                ['name' => __('ui.common.home'), 'url' => $this->urls->home($locale)],
                ['name' => __('ui.common.products'), 'url' => $this->urls->listing('products', $locale)],
                ['name' => $categoryTranslation?->name, 'url' => $categoryUrl],
                ['name' => $translation->name, 'url' => $productUrl],
            ], $productUrl, $categoryTranslation?->name),
        ]);
    }

    private function modelAlternateUrls(Product $product): array
    {
        $product->loadMissing('translations', 'category.translations');

        return $product->translations
            ->filter(fn ($translation): bool => $translation->is_published)
            ->mapWithKeys(fn ($translation): array => [
                $translation->locale => $this->urls->product($product, $translation, $translation->locale),
            ])
            ->filter()
            ->all();
    }
}
