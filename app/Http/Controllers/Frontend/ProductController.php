<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\ContactMessageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreProductConsultationRequest;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Services\Frontend\FrontendUrlBuilder;
use App\Services\Frontend\ListingPageService;
use App\Services\Frontend\ProductCatalogService;
use App\Services\Frontend\SeoSchemaBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function storeConsultation(StoreProductConsultationRequest $request, Product $product): RedirectResponse
    {
        return $this->createConsultation($request, $product);
    }

    public function localizedStoreConsultation(StoreProductConsultationRequest $request, string $locale, Product $product): RedirectResponse
    {
        return $this->createConsultation($request, $product);
    }

    private function createConsultation(StoreProductConsultationRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $data = $request->safe()->except('website');
        $translation = $product->translationFor(app()->getLocale())->first()
            ?: $product->translationFor('vi')->first();
        $productName = $translation?->name ?: $product->sku ?: 'Product #'.$product->getKey();

        ContactMessage::query()->create([
            ...$data,
            'subject' => __('ui.product_consultation.subject', ['product' => $productName]),
            'message' => filled($data['message'] ?? null)
                ? $data['message']
                : __('ui.product_consultation.default_message', ['product' => $productName]),
            'source' => 'product_detail',
            'related_type' => $product->getMorphClass(),
            'related_id' => $product->getKey(),
            'status' => ContactMessageStatus::New->value,
        ]);

        return back()->with('product_consultation_success', __('ui.product_consultation.success'));
    }

    private function renderListing(Request $request, string $locale, ?string $categorySlug = null): View
    {
        $data = $this->catalog->listing($request, $locale, $categorySlug);
        $translation = $this->listingPages->translation($locale, 'products');
        $activeCategory = $data['activeCategory'];
        $seoTranslation = $activeCategory?->translation ?: $translation;

        return view('frontend.pages.templates.products', [
            ...$data,
            'translation' => $translation,
            'seoTranslation' => $seoTranslation,
            'alternateUrls' => $activeCategory
                ? $this->categoryAlternateUrls($activeCategory)
                : $this->listingPages->alternateUrls('products'),
            'ogImage' => $activeCategory
                ? $this->schema->resolveOgImage($seoTranslation)
                : null,
            'schema' => $this->schema->collection(
                $seoTranslation,
                $activeCategory?->translation?->name ?: __('ui.common.products'),
                $data['products']->getCollection()->map(fn (Product $product): array => [
                    'name' => $product->translation?->name,
                    'url' => $this->urls->product($product, $product->translation, $locale),
                ])->all(),
                [
                    ['name' => __('ui.common.home'), 'url' => $this->urls->home($locale)],
                    ['name' => __('ui.common.products'), 'url' => $this->urls->listing('products', $locale)],
                    ['name' => $activeCategory?->translation?->name, 'url' => $activeCategory?->translation?->public_url],
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

        $homeUrl = $this->urls->home($locale);
        $listingUrl = $this->urls->listing('products', $locale);

        $blocks = is_array($translation->blocks) ? $translation->blocks : [];
        $specifications = is_array($translation->specifications) ? $translation->specifications : [];

        $applications = collect($blocks['applications'] ?? [])->filter()->values();
        $substrates = collect($blocks['substrates'] ?? [])->filter()->values();
        $features = collect($blocks['features'] ?? [])->filter()->values();

        $consultingInputs = collect($blocks['consulting_inputs'] ?? [])->filter()->values();
        if ($consultingInputs->isEmpty()) {
            $consultingInputs = collect(__('ui.product_detail.default_consulting_inputs'));
        }

        $storageNotes = collect($blocks['storage_notes'] ?? [])->filter()->values();

        $productFaq = collect($blocks['faq'] ?? [])
            ->filter(fn ($item): bool => is_array($item) && filled($item['question'] ?? null))
            ->values();

        $categoryName = $categoryTranslation?->name ?: __('ui.common.products');
        
        $relatedCount = $data['relatedProducts']->count();
        $data['relatedProducts']->each(function ($related) use ($locale) {
            $related->formatted_url = $this->urls->product($related, $related->translation, $locale) ?: '#';
            $related->formatted_image = $related->displayImageUrl($related->translation);
        });

        $quickFacts = collect([
            ['label' => __('ui.product_detail.category'), 'value' => $categoryName],
            ['label' => __('ui.product_detail.sku'), 'value' => $product->sku],
            ['label' => __('ui.product_detail.main_application'), 'value' => $applications->isNotEmpty() ? Str::limit($applications->first(), 80) : null],
            ['label' => __('ui.product_detail.substrate'), 'value' => $substrates->isNotEmpty() ? $substrates->take(3)->implode(', ') : null],
        ])->filter(fn (array $item): bool => filled($item['value'] ?? null))->values();

        return view('frontend.pages.templates.product-detail', [
            ...$data,
            'categoryUrl' => $categoryUrl,
            'alternateUrls' => $this->modelAlternateUrls($product),
            'ogImage' => $this->schema->resolveOgImage($translation),
            'schema' => $this->schema->product($product, $translation, [
                ['name' => __('ui.common.home'), 'url' => $homeUrl],
                ['name' => __('ui.common.products'), 'url' => $listingUrl],
                ['name' => $categoryTranslation?->name, 'url' => $categoryUrl],
                ['name' => $translation->name, 'url' => $productUrl],
            ], $productUrl, $categoryTranslation?->name),
            'homeUrl' => $homeUrl,
            'listingUrl' => $listingUrl,
            'mainImage' => $product->displayImageUrl($translation),
            'blocks' => $blocks,
            'specifications' => $specifications,
            'applications' => $applications,
            'substrates' => $substrates,
            'features' => $features,
            'consultingInputs' => $consultingInputs,
            'storageNotes' => $storageNotes,
            'productFaq' => $productFaq,
            'categoryName' => $categoryName,
            'quickFacts' => $quickFacts,
            'relatedCount' => $relatedCount,
            'locale' => $locale,
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

    private function categoryAlternateUrls(Category $category): array
    {
        $category->loadMissing('translations');

        return $category->translations
            ->filter(fn ($translation): bool => $translation->is_published)
            ->mapWithKeys(fn ($translation): array => [
                $translation->locale => $this->urls->category($category, $translation, $translation->locale),
            ])
            ->filter()
            ->all();
    }
}
