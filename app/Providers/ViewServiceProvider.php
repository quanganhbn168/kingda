<?php

namespace App\Providers;

use App\Enums\CategoryType;
use App\Enums\Locale;
use App\Enums\MenuLocation;
use App\Enums\RouteSegments;
use App\Models\CategoryTranslation;
use App\Models\IndustryTranslation;
use App\Models\Menu;
use App\Models\PageTranslation;
use App\Models\PostTranslation;
use App\Models\ProductTranslation;
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
                            'linkable.translations',
                            'childrenRecursive' => fn ($query) => $query
                                ->where('locale', $locale)
                                ->where('is_active', true)
                                ->with('linkable.translations')
                                ->orderBy('sort_order'),
                        ])
                        ->orderBy('sort_order'),
                ])
                ->first();

            $headerMenuItems = $headerMenu?->items ?? collect();

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
                            ->with([
                                'linkable.translations',
                                'activeChildrenRecursive' => fn ($query) => $query
                                    ->where('locale', $locale)
                                    ->where('is_active', true)
                                    ->with('linkable.translations')
                                    ->orderBy('sort_order'),
                            ])
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
                'homeUrl' => RouteSegments::home($locale),
                'contactUrl' => RouteSegments::url('contact', $locale),

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

        return match ($routeName) {
            'home', 'localized.home' => RouteSegments::home($targetLocale),
            'about', 'localized.about' => RouteSegments::url('about', $targetLocale),
            'products.index', 'localized.products.index' => $this->localizedListingUrl($request, $currentLocale, $targetLocale, 'product', 'products'),
            'products.category', 'localized.products.category' => $this->localizedCategoryUrl($request, $currentLocale, $targetLocale, CategoryType::Product->value, 'products'),
            'products.show', 'localized.products.show' => $this->localizedProductDetailUrl($request, $currentLocale, $targetLocale),
            'news.index', 'localized.news.index' => $this->localizedListingUrl($request, $currentLocale, $targetLocale, 'post', 'news'),
            'posts.category', 'localized.posts.category' => $this->localizedCategoryUrl($request, $currentLocale, $targetLocale, CategoryType::Post->value, 'news'),
            'posts.show', 'localized.posts.show' => $this->localizedPostDetailUrl($request, $currentLocale, $targetLocale),
            'industries.index', 'localized.industries.index' => RouteSegments::url('industries', $targetLocale),
            'industries.show', 'localized.industries.show' => $this->localizedIndustryDetailUrl($request, $currentLocale, $targetLocale),
            'contact', 'localized.contact' => RouteSegments::url('contact', $targetLocale),
            'pages.show', 'localized.pages.show' => $this->localizedPageUrl($request, $currentLocale, $targetLocale),
            default => RouteSegments::home($targetLocale),
        };
    }

    private function localizedListingUrl(Request $request, string $currentLocale, string $targetLocale, string $categoryType, string $segmentKey): string
    {
        $url = RouteSegments::url($segmentKey, $targetLocale);
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

        return $this->appendQuery($url, $query);
    }

    private function localizedCategoryUrl(Request $request, string $currentLocale, string $targetLocale, string $categoryType, string $segmentKey): string
    {
        $fallbackUrl = RouteSegments::url($segmentKey, $targetLocale);
        $categorySlug = $request->route('categorySlug');

        if (! $categorySlug) {
            return $fallbackUrl;
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

        return $targetTranslation ? $targetTranslation->public_url : $fallbackUrl;
    }

    private function localizedProductDetailUrl(Request $request, string $currentLocale, string $targetLocale): string
    {
        static $translations = [];

        $fallbackUrl = RouteSegments::url('products', $targetLocale);
        $categorySlug = $request->route('categorySlug');
        $productSlug = $request->route('productSlug');

        if (! $categorySlug || ! $productSlug) {
            return $fallbackUrl;
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

        return $targetTranslation ? $targetTranslation->public_url : $fallbackUrl;
    }

    private function localizedPostDetailUrl(Request $request, string $currentLocale, string $targetLocale): string
    {
        static $translations = [];

        $fallbackUrl = RouteSegments::url('news', $targetLocale);
        $categorySlug = $request->route('categorySlug');
        $postSlug = $request->route('postSlug');

        if (! $categorySlug || ! $postSlug) {
            return $fallbackUrl;
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
            ->first(fn (PostTranslation $item): bool => $item->locale === $targetLocale && $item->isUsable());

        $fallbackTranslation = $translation?->post
            ?->translations
            ->first(fn (PostTranslation $item): bool => $item->locale === 'vi' && $item->isUsable());

        return $targetTranslation?->public_url
            ?? $fallbackTranslation?->public_url
            ?? $fallbackUrl;
    }

    private function localizedIndustryDetailUrl(Request $request, string $currentLocale, string $targetLocale): string
    {
        $fallbackUrl = RouteSegments::url('industries', $targetLocale);
        $slug = $request->route('slug');

        if (! $slug) {
            return $fallbackUrl;
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

        return $targetTranslation ? $targetTranslation->public_url : $fallbackUrl;
    }

    private function localizedPageUrl(Request $request, string $currentLocale, string $targetLocale): string
    {
        $slug = $request->route('slug');

        if (! $slug) {
            return RouteSegments::home($targetLocale);
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

        return $targetTranslation ? $targetTranslation->public_url : RouteSegments::home($targetLocale);
    }

    private function appendQuery(string $url, array $query): string
    {
        $query = array_filter($query, fn ($value): bool => filled($value));

        if ($query === []) {
            return $url;
        }

        return $url.'?'.http_build_query($query);
    }
}
