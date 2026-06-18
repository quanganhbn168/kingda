<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Industry;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Post;
use App\Models\Product;
use App\Models\Slide;
use App\Services\Frontend\ListingPageService;
use App\Services\Frontend\LocalizedContent;
use App\Services\Frontend\SeoSchemaBuilder;
use App\Settings\AboutSettings;
use App\Settings\ContactSettings;
use App\Settings\HomeSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        private readonly ListingPageService $listingPages,
        private readonly SeoSchemaBuilder $schema,
    ) {}

    public function home(Request $request): View
    {
        return $this->renderHome(app()->getLocale());
    }

    public function about(Request $request): View
    {
        return $this->renderAbout(app()->getLocale());
    }

    public function contact(Request $request): View
    {
        return $this->renderContact(app()->getLocale());
    }

    public function industries(Request $request): View
    {
        return $this->renderIndustries(app()->getLocale());
    }

    public function industryDetail(Request $request, string $slug): View
    {
        return $this->renderIndustryDetail(app()->getLocale(), $slug);
    }

    public function localizedIndustryDetail(Request $request, string $locale, string $slug): View
    {
        return $this->renderIndustryDetail(app()->getLocale(), $slug);
    }

    public function show(Request $request, string $slug): View
    {
        return $this->renderPageBySlug(app()->getLocale(), $slug);
    }

    public function localizedShow(Request $request, string $locale, string $slug): View
    {
        return $this->renderPageBySlug(app()->getLocale(), $slug);
    }

    private function renderHome(string $locale): View
    {
        $page = Page::query()
            ->active()
            ->home()
            ->with([
                'translations',
                'translations.media',
            ])
            ->firstOrFail();

        $translation = $page->publishedTranslation($locale);

        abort_if(! $translation, 404);

        return $this->render($page, $translation, [
            'homeSlides' => $this->homeSlides($locale),
            'homeSettings' => $this->homeSettings($locale),
            'homeIndustries' => $this->homeIndustries($locale),
            'homeProductCategories' => $this->homeProductCategories($locale),
            'homePosts' => $this->homePosts($locale),
        ]);
    }

    private function renderAbout(string $locale): View
    {
        $homeSettings = $this->homeSettings($locale);
        $translation = $this->listingPages->translation($locale, 'about');

        return view('frontend.pages.templates.about', [
            'translation' => $translation,
            'aboutSettings' => $this->aboutSettings($locale),
            'sharedHomeSettings' => $homeSettings,
            'sharedIndustries' => $this->homeIndustries($locale),
            'companyStats' => $homeSettings['stats'] ?? [],
            'alternateUrls' => $this->listingPages->alternateUrls('about'),
            'ogImage' => null,
            'schema' => $this->schema->page($translation, 'AboutPage', [
                ['name' => __('ui.common.home'), 'url' => url($locale === 'vi' ? '/' : '/' . $locale)],
                ['name' => $translation->title, 'url' => url($locale === 'vi' ? '/gioi-thieu' : '/' . $locale . '/about')],
            ]),
        ]);
    }

    private function renderContact(string $locale): View
    {
        $translation = $this->listingPages->translation($locale, 'contact');

        return view('frontend.pages.templates.contact', [
            'translation' => $translation,
            'branches' => Branch::query()
                ->active()
                ->with([
                    'translations' => fn ($query) => $query->where('locale', $locale),
                    'publicContacts',
                ])
                ->ordered()
                ->get(),
            'contactSettings' => app(ContactSettings::class),
            'alternateUrls' => $this->listingPages->alternateUrls('contact'),
            'ogImage' => null,
            'schema' => $this->schema->page($translation, 'ContactPage', [
                ['name' => __('ui.common.home'), 'url' => url($locale === 'vi' ? '/' : '/' . $locale)],
                ['name' => __('ui.common.contact'), 'url' => url($locale === 'vi' ? '/lien-he' : '/' . $locale . '/contact')],
            ]),
        ]);
    }

    private function renderIndustries(string $locale): View
    {
        $translation = $this->listingPages->translation($locale, 'industries');
        $industries = Industry::query()
            ->active()
            ->withPublishedTranslation($locale)
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
                'media',
            ])
            ->ordered()
            ->get();

        return view('frontend.pages.templates.industries', [
            'translation' => $translation,
            'industries' => $industries,
            'stats' => $this->homeSettings($locale)['stats'] ?? [],
            'alternateUrls' => $this->listingPages->alternateUrls('industries'),
            'ogImage' => null,
            'schema' => $this->schema->collection(
                $translation,
                __('ui.common.industries'),
                $industries->map(fn (Industry $industry): array => [
                    'name' => $industry->translation?->title,
                    'url' => $industry->translation?->public_url,
                ])->all(),
                [
                    ['name' => __('ui.common.home'), 'url' => url($locale === 'vi' ? '/' : '/' . $locale)],
                    ['name' => __('ui.common.industries'), 'url' => url($locale === 'vi' ? '/linh-vuc' : '/' . $locale . '/industries')],
                ]
            ),
        ]);
    }

    private function renderIndustryDetail(string $locale, string $slug): View
    {
        $translation = \App\Models\IndustryTranslation::query()
            ->published()
            ->locale($locale)
            ->where('slug', $slug)
            ->with('industry.media')
            ->firstOrFail();

        $industry = $translation->industry;

        abort_if(! $industry || ! $industry->is_active, 404);

        return view('frontend.pages.templates.industry-detail', [
            'industry' => $industry,
            'translation' => $translation,
            'alternateUrls' => [],
            'ogImage' => $industry->getFirstMediaUrl('hero') ?: $industry->getFirstMediaUrl('thumbnail'),
            'schema' => $this->schema->page($translation, 'WebPage', [
                ['name' => __('ui.common.home'), 'url' => url($locale === 'vi' ? '/' : '/' . $locale)],
                ['name' => __('ui.common.industries'), 'url' => url($locale === 'vi' ? '/linh-vuc' : '/' . $locale . '/industries')],
                ['name' => $translation->title, 'url' => $translation->public_url],
            ]),
        ]);
    }

    private function renderPageBySlug(string $locale, string $slug): View
    {
        $slug = trim($slug, '/');

        $translation = PageTranslation::query()
            ->published()
            ->locale($locale)
            ->slug($slug)
            ->with([
                'page.translations',
                'media',
            ])
            ->firstOrFail();

        $page = $translation->page;

        abort_if(! $page || ! $page->is_active, 404);

        return $this->render($page, $translation);
    }

    private function render(Page $page, PageTranslation $translation, array $data = []): View
    {
        return view($page->template_view, [
            'page' => $page,
            'translation' => $translation,
            'alternateUrls' => $this->buildAlternateUrls($page),
            'ogImage' => $this->schema->resolveOgImage($translation),
            'schema' => $this->schema->page($translation, 'WebPage', [
                ['name' => __('ui.common.home'), 'url' => url(app()->getLocale() === 'vi' ? '/' : '/' . app()->getLocale())],
            ]),
        ] + $data);
    }

    private function homeSlides(string $locale)
    {
        return Slide::query()
            ->active()
            ->location('home')
            ->whereHas('translations', fn ($query) => $query
                ->where('locale', $locale)
                ->where('is_published', true))
            ->with([
                'translations' => fn ($query) => $query
                    ->where('locale', $locale)
                    ->where('is_published', true),
                'media',
            ])
            ->ordered()
            ->get();
    }

    private function homeProductCategories(string $locale)
    {
        return Category::query()
            ->product()
            ->active()
            ->whereHas('translations', fn ($query) => $query
                ->where('locale', $locale)
                ->where('is_published', true))
            ->whereHas('products', fn ($query) => $query
                ->active()
                ->withPublishedTranslation($locale))
            ->with([
                'translation.media',
                'products' => fn ($query) => $query
                    ->active()
                    ->withPublishedTranslation($locale)
                    ->with(['translation.media'])
                    ->ordered(),
            ])
            ->ordered()
            ->get()
            ->map(fn (Category $category): array => [
                'title' => $category->translation?->name,
                'description' => $category->translation?->description,
                'url' => $category->translation?->public_url,
                'image' => $category->translation?->getFirstMediaUrl('thumbnail') ?: $category->translation?->getFirstMediaUrl('hero'),
                'products' => $category->products->map(fn ($product): array => [
                    'title' => $product->translation?->name,
                    'description' => $product->translation?->description,
                    'url' => $product->translation?->public_url,
                    'image' => $product->translation?->getFirstMediaUrl('thumbnail') ?: $product->translation?->getFirstMediaUrl('hero'),
                    'sku' => $product->sku,
                ])->values()->all(),
            ])
            ->filter(fn (array $category): bool => filled($category['title']) && ! empty($category['products']))
            ->values();
    }

    private function homeIndustries(string $locale)
    {
        return Industry::query()
            ->active()
            ->featured()
            ->withPublishedTranslation($locale)
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
                'media',
            ])
            ->ordered()
            ->limit((int) data_get(app(HomeSettings::class)->industries, 'limit', 8))
            ->get()
            ->map(fn (Industry $industry): array => [
                'title' => $industry->translation?->title,
                'description' => $industry->translation?->description,
                'url' => $industry->url ?: $industry->translation?->public_url,
                'icon' => $industry->icon,
                'image' => $industry->getFirstMediaUrl('thumbnail') ?: $industry->getFirstMediaUrl('hero'),
            ])
            ->filter(fn (array $industry): bool => filled($industry['title']))
            ->values();
    }

    private function certificates()
    {
        return Certificate::query()
            ->active()
            ->with('media')
            ->ordered()
            ->get()
            ->map(fn (Certificate $certificate): array => [
                'name' => $certificate->name,
                'description' => $certificate->description,
                'image' => $certificate->getFirstMediaUrl('image'),
                'pdf' => $certificate->getFirstMediaUrl('pdf'),
            ])
            ->values();
    }

    private function homeSettings(string $locale): array
    {
        $settings = app(HomeSettings::class);

        return [
            'stats' => LocalizedContent::items($settings->stats, $locale),
            'intro' => [
                ...LocalizedContent::block($settings->intro, $locale),
                'items' => LocalizedContent::items($settings->intro['items'] ?? [], $locale),
                'image' => LocalizedContent::mediaUrl($settings->intro['image'] ?? null),
                'video_upload' => LocalizedContent::mediaUrl($settings->intro['video_upload'] ?? null),
                'video_embed_url' => $settings->intro['video_embed_url'] ?? null,
            ],
            'industries' => LocalizedContent::block($settings->industries, $locale) + ['limit' => $settings->industries['limit'] ?? 8],
            'products' => LocalizedContent::block($settings->products, $locale) + ['limit' => $settings->products['limit'] ?? 8],
            'capabilities' => [
                ...LocalizedContent::block($settings->capabilities, $locale),
                'items' => collect(LocalizedContent::items($settings->capabilities['items'] ?? [], $locale))
                    ->map(fn (array $item): array => [
                        ...$item,
                        'image' => LocalizedContent::mediaUrl($item['image'] ?? null),
                    ])
                    ->all(),
            ],
            'certifications' => [
                ...LocalizedContent::block($settings->certifications, $locale),
                'certificates' => collect($settings->certifications['certificates'] ?? [])
                    ->map(function (mixed $certificate): mixed {
                        if (! is_array($certificate)) {
                            return $certificate;
                        }

                        return [
                            ...$certificate,
                            'image' => LocalizedContent::mediaUrl($certificate['image'] ?? null),
                            'pdf' => LocalizedContent::mediaUrl($certificate['pdf'] ?? null),
                        ];
                    })
                    ->concat($this->certificates())
                    ->all(),
                'items' => LocalizedContent::items($settings->certifications['items'] ?? [], $locale),
            ],
            'advantages' => [
                ...LocalizedContent::block($settings->advantages, $locale),
                'items' => LocalizedContent::items($settings->advantages['items'] ?? [], $locale),
            ],
            'customers' => [
                ...LocalizedContent::block($settings->customers, $locale),
                'items' => collect($settings->customers['items'] ?? [])
                    ->map(fn (array $item): array => [
                        ...$item,
                        'logo' => LocalizedContent::mediaUrl($item['logo'] ?? null),
                    ])
                    ->all(),
            ],
            'news' => LocalizedContent::block($settings->news, $locale) + ['limit' => $settings->news['limit'] ?? 3],
            'cta' => [
                ...LocalizedContent::block($settings->cta, $locale),
                'button_url' => $settings->cta['button_url'] ?? null,
                'background_image' => LocalizedContent::mediaUrl($settings->cta['background_image'] ?? null),
            ],
        ];
    }

    private function aboutSettings(string $locale): array
    {
        $settings = app(AboutSettings::class);

        return [
            'hero' => [
                ...LocalizedContent::block($settings->hero, $locale),
                'image' => LocalizedContent::mediaUrl($settings->hero['image'] ?? null),
            ],
            'intro' => [
                ...LocalizedContent::block($settings->intro, $locale),
                'image' => LocalizedContent::mediaUrl($settings->intro['image'] ?? null),
                'small_image_one' => LocalizedContent::mediaUrl($settings->intro['small_image_one'] ?? null),
                'small_image_two' => LocalizedContent::mediaUrl($settings->intro['small_image_two'] ?? null),
                'video_upload' => LocalizedContent::mediaUrl($settings->intro['video_upload'] ?? null),
                'video_embed_url' => $settings->intro['video_embed_url'] ?? null,
                'stats' => LocalizedContent::items($settings->intro['stats'] ?? [], $locale),
            ],
            'development' => [
                ...LocalizedContent::block($settings->development, $locale),
                'items' => LocalizedContent::items($settings->development['items'] ?? [], $locale),
            ],
            'timeline' => [
                ...LocalizedContent::block($settings->timeline, $locale),
                'items' => LocalizedContent::items($settings->timeline['items'] ?? [], $locale),
            ],
            'culture' => [
                ...LocalizedContent::block($settings->culture, $locale),
                'items' => LocalizedContent::items($settings->culture['items'] ?? [], $locale),
            ],
            'capabilities' => [
                ...LocalizedContent::block($settings->capabilities, $locale),
                'items' => collect($settings->capabilities['items'] ?? [])
                    ->map(fn (array $item): array => [
                        ...LocalizedContent::items([$item], $locale)[0],
                        'image' => LocalizedContent::mediaUrl($item['image'] ?? null),
                    ])
                    ->all(),
            ],
            'certificates' => [
                ...LocalizedContent::block($settings->certificates, $locale),
                'items' => LocalizedContent::items($settings->certificates['items'] ?? [], $locale),
            ],
            'intellectual_property' => [
                'items' => LocalizedContent::items($settings->intellectual_property['items'] ?? [], $locale),
            ],
            'customers' => [
                ...LocalizedContent::block($settings->customers, $locale),
                'items' => $settings->customers['items'] ?? [],
            ],
            'contact' => [
                ...LocalizedContent::block($settings->contact, $locale),
                'button_url' => $settings->contact['button_url'] ?? null,
            ],
        ];
    }

    private function homePosts(string $locale)
    {
        $limit = (int) data_get(app(HomeSettings::class)->news, 'limit', 3);

        return Post::query()
            ->active()
            ->withPublishedTranslation($locale)
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
                'translation.media',
                'category.translation' => fn ($query) => $query->where('locale', $locale),
            ])
            ->latest('created_at')
            ->limit($limit > 0 ? $limit : 3)
            ->get();
    }

    private function buildAlternateUrls(Page $page): array
    {
        $urls = [];

        $page->loadMissing('translations');

        foreach ($page->translations as $translation) {
            if (! $translation->is_published) {
                continue;
            }

            $urls[$translation->locale] = $translation->public_url;
        }

        return $urls;
    }

}
