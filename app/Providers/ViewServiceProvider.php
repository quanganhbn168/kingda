<?php

namespace App\Providers;

use App\Enums\CategoryType;
use App\Enums\Locale;
use App\Enums\MenuLocation;
use App\Models\CategoryTranslation;
use App\Models\IndustryTranslation;
use App\Models\Menu;
use App\Models\PageTranslation;
use App\Models\PostTranslation;
use App\Models\ProductTranslation;
use App\Services\Frontend\ProductCategoryService;
use App\Settings\ContactSettings;
use App\Settings\IntegrationSettings;
use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $locale = app()->getLocale();

            $view->with([
                'settings' => app(SiteSettings::class),
                'siteSettings' => app(SiteSettings::class),
                'contactSettings' => app(ContactSettings::class),
                'integrationSettings' => app(IntegrationSettings::class),
                'seoSettings' => app(SeoSettings::class),
                'currentLocale' => $locale,
            ]);
        });

        View::composer([
            'layouts.master',
            'frontend.partials.header',
            'frontend.partials.footer',
        ], function ($view) {
            static $payloads = [];

            $locale = app()->getLocale();
            $request = request();
            $cacheKey = implode('|', [
                $locale,
                $request->route()?->getName(),
                $request->path(),
                $request->getQueryString() ?: '',
            ]);

            if (isset($payloads[$cacheKey])) {
                $view->with($payloads[$cacheKey]);

                return;
            }

            $headerMenu = Menu::query()
                ->where('location', MenuLocation::Header->value)
                ->where('is_active', true)
                ->with([
                    'items' => fn ($query) => $query
                        ->whereNull('parent_id')
                        ->where('locale', $locale)
                        ->where('is_active', true)
                        ->with([
                            'childrenRecursive' => fn ($query) => $query
                                ->where('locale', $locale)
                                ->where('is_active', true)
                                ->orderBy('sort_order'),
                        ])
                        ->orderBy('sort_order'),
                ])
                ->first();

            $headerMenuItems = $headerMenu?->items ?? collect();
            app(ProductCategoryService::class)->replaceProductMenuChildren($headerMenuItems, $locale);

            $siteSettings = app(SiteSettings::class);
            $loadFooterMenu = function (?int $menuId, bool $fallbackToFooter = false) use ($locale): ?Menu {
                if (blank($menuId) && ! $fallbackToFooter) {
                    return null;
                }

                $query = Menu::query()->where('is_active', true);

                if (filled($menuId)) {
                    $query->whereKey($menuId);
                } else {
                    $query->where('location', MenuLocation::Footer->value);
                }

                return $query
                    ->with([
                        'items' => fn ($query) => $query
                            ->whereNull('parent_id')
                            ->where('locale', $locale)
                            ->where('is_active', true)
                            ->with('activeChildrenRecursive')
                            ->orderBy('sort_order'),
                    ])
                    ->first();
            };

            $footerMenu1 = $loadFooterMenu(
                $siteSettings->footer_menu_1_id ?: $siteSettings->footer_menu_id,
                true,
            );
            $footerMenu2 = $loadFooterMenu($siteSettings->footer_menu_2_id);
            $languageItems = collect(Locale::publicOptions())
                ->map(fn (array $item): array => [
                    ...$item,
                    'url' => $this->localizedUrlForRequest($request, $locale, $item['locale']),
                    'active' => $locale === $item['locale'],
                    'is_active' => $locale === $item['locale'],
                ])
                ->all();

            $payloads[$cacheKey] = [
                'homeUrl' => $locale === 'vi' ? url('/') : url('/'.$locale),
                'contactUrl' => $locale === 'vi' ? url('/lien-he') : url('/'.$locale.'/contact'),

                'headerMenuItems' => $headerMenuItems,
                'footerMenuItems' => $footerMenu1?->items ?? collect(),
                'footerMenu1Items' => $footerMenu1?->items ?? collect(),
                'footerMenu2Items' => $footerMenu2?->items ?? collect(),

                'localeSwitcher' => $languageItems,
                'languageItems' => $languageItems,
            ];

            $view->with($payloads[$cacheKey]);
        });
    }

    private function localizedUrlForRequest(Request $request, string $currentLocale, string $targetLocale): string
    {
        $routeName = $request->route()?->getName();

        $path = match ($routeName) {
            'home', 'localized.home' => $this->localizedPath($targetLocale, ''),
            'about', 'localized.about' => $this->localizedPath($targetLocale, $targetLocale === Locale::Vietnamese->value ? 'gioi-thieu' : 'about'),
            'products.index', 'localized.products.index' => $this->localizedListingPath($request, $currentLocale, $targetLocale, 'product', 'san-pham', 'products'),
            'products.category', 'localized.products.category' => $this->localizedCategoryPath($request, $currentLocale, $targetLocale, CategoryType::Product->value, 'san-pham', 'products'),
            'products.show', 'localized.products.show' => $this->localizedProductDetailPath($request, $currentLocale, $targetLocale),
            'news.index', 'localized.news.index' => $this->localizedListingPath($request, $currentLocale, $targetLocale, 'post', 'tin-tuc', 'news'),
            'posts.category', 'localized.posts.category' => $this->localizedCategoryPath($request, $currentLocale, $targetLocale, CategoryType::Post->value, 'tin-tuc', 'news'),
            'posts.show', 'localized.posts.show' => $this->localizedPostDetailPath($request, $currentLocale, $targetLocale),
            'industries.index', 'localized.industries.index' => $this->localizedPath($targetLocale, $targetLocale === Locale::Vietnamese->value ? 'linh-vuc' : 'industries'),
            'industries.show', 'localized.industries.show' => $this->localizedIndustryDetailPath($request, $currentLocale, $targetLocale),
            'contact', 'localized.contact' => $this->localizedPath($targetLocale, $targetLocale === Locale::Vietnamese->value ? 'lien-he' : 'contact'),
            'pages.show', 'localized.pages.show' => $this->localizedPagePath($request, $currentLocale, $targetLocale),
            default => $this->localizedPath($targetLocale, ''),
        };

        return url($path);
    }

    private function localizedListingPath(Request $request, string $currentLocale, string $targetLocale, string $categoryType, string $viSegment, string $localizedSegment): string
    {
        $path = $this->localizedPath($targetLocale, $targetLocale === Locale::Vietnamese->value ? $viSegment : $localizedSegment);
        $query = $request->query();

        if (filled($query['category'] ?? null)) {
            $categoryTranslation = CategoryTranslation::query()
                ->where('locale', $currentLocale)
                ->where('slug', $query['category'])
                ->whereHas('category', fn ($query) => $query->where('type', $categoryType))
                ->first();

            $targetCategoryTranslation = $categoryTranslation?->category
                ?->translationFor($targetLocale)
                ->where('is_published', true)
                ->first();

            if ($targetCategoryTranslation) {
                $query['category'] = $targetCategoryTranslation->slug;
            } else {
                unset($query['category']);
            }
        }

        return $this->appendQuery($path, $query);
    }

    private function localizedCategoryPath(Request $request, string $currentLocale, string $targetLocale, string $categoryType, string $viSegment, string $localizedSegment): string
    {
        $fallbackPath = $this->localizedPath($targetLocale, $targetLocale === Locale::Vietnamese->value ? $viSegment : $localizedSegment);
        $categorySlug = $request->route('categorySlug');

        if (! $categorySlug) {
            return $fallbackPath;
        }

        $translation = CategoryTranslation::query()
            ->where('locale', $currentLocale)
            ->where('slug', $categorySlug)
            ->whereHas('category', fn ($query) => $query
                ->where('type', $categoryType)
                ->where('is_active', true))
            ->first();

        $targetTranslation = $translation?->category
            ?->translationFor($targetLocale)
            ->where('is_published', true)
            ->first();

        return $targetTranslation
            ? $this->pathFromUrl($targetTranslation->public_url)
            : $fallbackPath;
    }

    private function localizedProductDetailPath(Request $request, string $currentLocale, string $targetLocale): string
    {
        static $translations = [];

        $fallbackPath = $this->localizedPath($targetLocale, $targetLocale === Locale::Vietnamese->value ? 'san-pham' : 'products');
        $categorySlug = $request->route('categorySlug');
        $productSlug = $request->route('productSlug');

        if (! $categorySlug || ! $productSlug) {
            return $fallbackPath;
        }

        $cacheKey = $currentLocale.':'.$categorySlug.':'.$productSlug;

        $translation = $translations[$cacheKey] ??= ProductTranslation::query()
            ->where('locale', $currentLocale)
            ->where('slug', $productSlug)
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->whereHas('product.category.translations', fn ($query) => $query
                ->where('locale', $currentLocale)
                ->where('slug', $categorySlug)
                ->where('is_published', true))
            ->with('product.translations', 'product.category.translations')
            ->first();

        $targetTranslation = $translation?->product
            ?->translations
            ->first(fn (ProductTranslation $item): bool => $item->locale === $targetLocale && $item->is_published);

        $targetCategoryTranslation = $translation?->product
            ?->category
            ?->translations
            ->firstWhere('locale', $targetLocale);

        $targetPath = $targetTranslation && $targetCategoryTranslation
            ? ($targetLocale === Locale::Vietnamese->value ? 'san-pham' : 'products').'/'.$targetCategoryTranslation->slug.'/'.$targetTranslation->slug
            : null;

        return $targetPath
            ? $this->localizedPath($targetLocale, $targetPath)
            : $fallbackPath;
    }

    private function localizedPostDetailPath(Request $request, string $currentLocale, string $targetLocale): string
    {
        static $translations = [];

        $fallbackPath = $this->localizedPath($targetLocale, $targetLocale === Locale::Vietnamese->value ? 'tin-tuc' : 'news');
        $categorySlug = $request->route('categorySlug');
        $postSlug = $request->route('postSlug');

        if (! $categorySlug || ! $postSlug) {
            return $fallbackPath;
        }

        $cacheKey = $currentLocale.':'.$categorySlug.':'.$postSlug;

        $translation = $translations[$cacheKey] ??= PostTranslation::query()
            ->where('locale', $currentLocale)
            ->where('slug', $postSlug)
            ->whereHas('post', fn ($query) => $query->where('is_active', true))
            ->whereHas('post.category.translations', fn ($query) => $query
                ->where('locale', $currentLocale)
                ->where('slug', $categorySlug)
                ->where('is_published', true))
            ->with('post.translations', 'post.category.translations')
            ->first();

        $targetTranslation = $translation?->post
            ?->translations
            ->first(fn (PostTranslation $item): bool => $item->locale === $targetLocale && $item->is_published);

        $targetCategoryTranslation = $translation?->post
            ?->category
            ?->translations
            ->firstWhere('locale', $targetLocale);

        $targetPath = $targetTranslation && $targetCategoryTranslation
            ? ($targetLocale === Locale::Vietnamese->value ? 'tin-tuc' : 'news').'/'.$targetCategoryTranslation->slug.'/'.$targetTranslation->slug
            : null;

        return $targetPath
            ? $this->localizedPath($targetLocale, $targetPath)
            : $fallbackPath;
    }

    private function localizedIndustryDetailPath(Request $request, string $currentLocale, string $targetLocale): string
    {
        $fallbackPath = $this->localizedPath($targetLocale, $targetLocale === Locale::Vietnamese->value ? 'linh-vuc' : 'industries');
        $slug = $request->route('slug');

        if (! $slug) {
            return $fallbackPath;
        }

        $translation = IndustryTranslation::query()
            ->where('locale', $currentLocale)
            ->where('slug', $slug)
            ->whereHas('industry', fn ($query) => $query->where('is_active', true))
            ->with('industry.translations')
            ->first();

        $targetTranslation = $translation?->industry
            ?->translations()
            ->where('is_published', true)
            ->where('locale', $targetLocale)
            ->first();

        return $targetTranslation
            ? $this->pathFromUrl($targetTranslation->public_url)
            : $fallbackPath;
    }

    private function localizedPagePath(Request $request, string $currentLocale, string $targetLocale): string
    {
        $slug = $request->route('slug');

        if (! $slug) {
            return $this->localizedPath($targetLocale, '');
        }

        $translation = PageTranslation::query()
            ->where('locale', $currentLocale)
            ->where('slug', $slug)
            ->whereHas('page', fn ($query) => $query->where('is_active', true))
            ->first();

        $targetTranslation = $translation?->page
            ?->translations()
            ->where('locale', $targetLocale)
            ->where('is_published', true)
            ->first();

        if (! $targetTranslation) {
            return $this->localizedPath($targetLocale, '');
        }

        if ($targetTranslation->page?->is_home) {
            return $this->localizedPath($targetLocale, '');
        }

        return $this->localizedPath($targetLocale, $targetTranslation->slug);
    }

    private function localizedPath(string $locale, string $path): string
    {
        $path = trim($path, '/');

        if ($locale === Locale::Vietnamese->value) {
            return $path === '' ? '/' : '/'.$path;
        }

        return $path === '' ? '/'.$locale : '/'.$locale.'/'.$path;
    }

    private function appendQuery(string $path, array $query): string
    {
        $query = array_filter($query, fn ($value): bool => filled($value));

        if ($query === []) {
            return $path;
        }

        return $path.'?'.http_build_query($query);
    }

    private function pathFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $query = parse_url($url, PHP_URL_QUERY);

        return $query ? $path.'?'.$query : $path;
    }
}
