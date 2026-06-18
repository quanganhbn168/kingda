<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Frontend\FrontendUrlBuilder;
use App\Services\Frontend\ListingPageService;
use App\Services\Frontend\PostCatalogService;
use App\Services\Frontend\SeoSchemaBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private readonly PostCatalogService $catalog,
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

    public function show(Request $request, string $categorySlug, string $postSlug): View
    {
        return $this->renderDetail(app()->getLocale(), $categorySlug, $postSlug);
    }

    public function localizedShow(Request $request, string $locale, string $categorySlug, string $postSlug): View
    {
        return $this->renderDetail(app()->getLocale(), $categorySlug, $postSlug);
    }

    private function renderListing(Request $request, string $locale, ?string $categorySlug = null): View
    {
        $data = $this->catalog->listing($request, $locale, $categorySlug);
        $translation = $this->listingPages->translation($locale, 'news');

        return view('frontend.pages.templates.news', [
            ...$data,
            'translation' => $translation,
            'alternateUrls' => $this->listingPages->alternateUrls('news'),
            'ogImage' => null,
            'schema' => $this->schema->collection(
                $translation,
                $data['activeCategory']?->translation?->name ?: __('ui.common.news'),
                $data['posts']->getCollection()->map(fn (Post $post): array => [
                    'name' => $post->translation?->title,
                    'url' => $this->urls->post($post, $post->translation, $locale),
                ])->all(),
                [
                    ['name' => __('ui.common.home'), 'url' => $this->urls->home($locale)],
                    ['name' => __('ui.common.news'), 'url' => $this->urls->listing('news', $locale)],
                ]
            ),
        ]);
    }

    private function renderDetail(string $locale, string $categorySlug, string $postSlug): View
    {
        $data = $this->catalog->detail($locale, $categorySlug, $postSlug);
        $post = $data['post'];
        $translation = $data['translation'];
        $categoryTranslation = $data['categoryTranslation'];
        $categoryUrl = $post->category
            ? $this->urls->category($post->category, $categoryTranslation, $locale)
            : null;
        $postUrl = $this->urls->post($post, $translation, $locale);

        return view('frontend.pages.templates.post-detail', [
            ...$data,
            'categoryUrl' => $categoryUrl,
            'alternateUrls' => $this->modelAlternateUrls($post),
            'ogImage' => $this->schema->resolveOgImage($translation),
            'schema' => $this->schema->newsArticle($post, $translation, [
                ['name' => __('ui.common.home'), 'url' => $this->urls->home($locale)],
                ['name' => __('ui.common.news'), 'url' => $this->urls->listing('news', $locale)],
                ['name' => $categoryTranslation?->name, 'url' => $categoryUrl],
                ['name' => $translation->title, 'url' => $postUrl],
            ]),
        ]);
    }

    private function modelAlternateUrls(Post $post): array
    {
        $post->loadMissing('translations', 'category.translations');

        return $post->translations
            ->filter(fn ($translation): bool => $translation->is_published)
            ->mapWithKeys(fn ($translation): array => [
                $translation->locale => $this->urls->post($post, $translation, $translation->locale),
            ])
            ->filter()
            ->all();
    }
}
