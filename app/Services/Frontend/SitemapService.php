<?php

namespace App\Services\Frontend;

use App\Enums\CategoryType;
use App\Enums\Locale;
use App\Enums\RouteSegments;
use App\Models\Category;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public const CACHE_KEY = 'frontend:sitemap:xml:v1';

    /** @var array<string, true> */
    private array $seenUrls = [];

    public function __construct(
        private readonly FrontendUrlBuilder $urls,
    ) {}

    public function render(): string
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addHour(),
            fn (): string => $this->build()->render(),
        );
    }

    public static function forgetCached(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function build(): Sitemap
    {
        $this->seenUrls = [];
        $sitemap = Sitemap::create();

        $this->addStaticPages($sitemap);
        $this->addCustomPages($sitemap);
        $this->addCategories($sitemap);
        $this->addProducts($sitemap);
        $this->addPosts($sitemap);
        $this->addIndustries($sitemap);

        return $sitemap;
    }

    private function addStaticPages(Sitemap $sitemap): void
    {
        foreach (['home', 'products', 'news', 'industries', 'about', 'contact'] as $type) {
            $entries = [];

            foreach (Locale::cases() as $locale) {
                $entries[$locale->value] = [
                    'url' => $type === 'home'
                        ? RouteSegments::home($locale->value)
                        : RouteSegments::url($type, $locale->value),
                    'lastModified' => null,
                ];
            }

            $this->addLocalizedGroup($sitemap, $entries);
        }
    }

    private function addCustomPages(Sitemap $sitemap): void
    {
        Page::query()
            ->active()
            ->with(['translations' => fn ($query) => $query
                ->where('is_published', true)
                ->whereNotNull('slug')])
            ->get()
            ->each(function (Page $page) use ($sitemap): void {
                $entries = $this->translationEntries(
                    $page->translations,
                    fn (Model $translation): ?string => $this->isReservedPageSlug(
                        (string) $translation->locale,
                        (string) $translation->slug,
                    ) ? null : $translation->public_url,
                );

                $this->addLocalizedGroup($sitemap, $entries);
            });
    }

    private function addCategories(Sitemap $sitemap): void
    {
        Category::query()
            ->active()
            ->whereIn('type', [CategoryType::Product->value, CategoryType::Post->value])
            ->with(['translations' => fn ($query) => $query
                ->where('is_published', true)
                ->whereNotNull('slug')])
            ->get()
            ->each(function (Category $category) use ($sitemap): void {
                $entries = $this->translationEntries(
                    $category->translations,
                    fn (Model $translation): ?string => $this->urls->category(
                        $category,
                        $translation,
                        (string) $translation->locale,
                    ),
                );

                $this->addLocalizedGroup($sitemap, $entries);
            });
    }

    private function addProducts(Sitemap $sitemap): void
    {
        Product::query()
            ->active()
            ->whereHas('category', fn (Builder $query): Builder => $query
                ->active()
                ->product())
            ->with([
                'translations' => fn ($query) => $query
                    ->where('is_published', true)
                    ->whereNotNull('slug'),
                'category.translations' => fn ($query) => $query
                    ->where('is_published', true)
                    ->whereNotNull('slug'),
            ])
            ->get()
            ->each(function (Product $product) use ($sitemap): void {
                $entries = $this->translationEntries(
                    $product->translations,
                    function (Model $translation) use ($product): ?string {
                        $locale = (string) $translation->locale;
                        $categoryTranslation = $product->category?->translations
                            ->firstWhere('locale', $locale);

                        if (! $categoryTranslation) {
                            return null;
                        }

                        return $this->urls->product($product, $translation, $locale);
                    },
                );

                $this->addLocalizedGroup($sitemap, $entries);
            });
    }

    private function addPosts(Sitemap $sitemap): void
    {
        Post::query()
            ->active()
            ->whereHas('category', fn (Builder $query): Builder => $query
                ->active()
                ->post())
            ->with([
                'translations' => fn ($query) => $query
                    ->where('is_published', true)
                    ->whereNotNull('slug'),
                'category.translations' => fn ($query) => $query
                    ->where('is_published', true)
                    ->whereNotNull('slug'),
            ])
            ->get()
            ->each(function (Post $post) use ($sitemap): void {
                $entries = $this->translationEntries(
                    $post->translations,
                    function (Model $translation) use ($post): ?string {
                        $locale = (string) $translation->locale;
                        $categoryTranslation = $post->category?->translations
                            ->firstWhere('locale', $locale);

                        if (! $categoryTranslation) {
                            return null;
                        }

                        return $this->urls->post($post, $translation, $locale);
                    },
                );

                $this->addLocalizedGroup($sitemap, $entries);
            });
    }

    private function addIndustries(Sitemap $sitemap): void
    {
        Industry::query()
            ->active()
            ->with(['translations' => fn ($query) => $query
                ->where('is_published', true)
                ->whereNotNull('slug')])
            ->get()
            ->each(function (Industry $industry) use ($sitemap): void {
                $entries = $this->translationEntries(
                    $industry->translations,
                    fn (Model $translation): string => $translation->public_url,
                );

                $this->addLocalizedGroup($sitemap, $entries);
            });
    }

    /**
     * @param  iterable<int, Model>  $translations
     * @param  callable(Model): (?string)  $urlResolver
     * @return array<string, array{url: string, lastModified: DateTimeInterface|null}>
     */
    private function translationEntries(iterable $translations, callable $urlResolver): array
    {
        $entries = [];

        foreach ($translations as $translation) {
            $locale = (string) $translation->getAttribute('locale');
            $url = $urlResolver($translation);

            if (! Locale::tryFrom($locale) || blank($url) || ! $this->isIndexable($translation)) {
                continue;
            }

            $entries[$locale] = [
                'url' => $url,
                'lastModified' => $translation->getAttribute('updated_at'),
            ];
        }

        return $entries;
    }

    /**
     * @param  array<string, array{url: string, lastModified: DateTimeInterface|null}>  $entries
     */
    private function addLocalizedGroup(Sitemap $sitemap, array $entries): void
    {
        $entries = array_filter(
            $entries,
            fn (array $entry): bool => ! isset($this->seenUrls[$entry['url']]),
        );

        if ($entries === []) {
            return;
        }

        $defaultUrl = $entries[Locale::Vietnamese->value]['url']
            ?? reset($entries)['url'];

        foreach ($entries as $entry) {
            $tag = Url::create($entry['url']);

            if ($entry['lastModified']) {
                $tag->setLastModificationDate($entry['lastModified']);
            }

            foreach ($entries as $alternateLocale => $alternateEntry) {
                $tag->addAlternate($alternateEntry['url'], $alternateLocale);
            }

            $tag->addAlternate($defaultUrl, 'x-default');
            $sitemap->add($tag);
            $this->seenUrls[$entry['url']] = true;
        }
    }

    private function isIndexable(Model $translation): bool
    {
        $robots = strtolower(trim((string) $translation->getAttribute('meta_robots')));

        if ($robots === '') {
            return true;
        }

        $directives = preg_split('/[\s,]+/', $robots, flags: PREG_SPLIT_NO_EMPTY) ?: [];

        return ! in_array('noindex', $directives, true)
            && ! in_array('none', $directives, true);
    }

    private function isReservedPageSlug(string $locale, string $slug): bool
    {
        $slug = trim($slug, '/');

        if ($slug === '' || str_contains($slug, '/')) {
            return true;
        }

        $reserved = [
            'admin',
            'api',
            'en',
            'filament',
            'robots.txt',
            'sitemap.xml',
            'storage',
            'up',
            've-chung-toi',
            'zh',
        ];

        foreach (['products', 'news', 'industries', 'about', 'contact'] as $type) {
            $reserved[] = RouteSegments::for($type, $locale);
        }

        return in_array($slug, $reserved, true)
            || str_starts_with($slug, 'livewire');
    }
}
