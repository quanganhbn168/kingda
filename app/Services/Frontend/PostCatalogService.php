<?php

namespace App\Services\Frontend;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostCatalogService
{
    public function categories(string $locale)
    {
        $categories = Category::query()
            ->post()
            ->active()
            ->whereHas('translations', fn (Builder $query) => $query
                ->where('locale', $locale)
                ->where('is_published', true))
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
                'children',
            ])
            ->withCount([
                'posts' => fn (Builder $query) => $query
                    ->active()
                    ->withPublishedTranslation($locale),
            ])
            ->ordered()
            ->get();

        $categories->each(function (Category $category) use ($locale): void {
            $category->posts_count = Post::query()
                ->active()
                ->withPublishedTranslation($locale)
                ->whereIn('category_id', $category->descendantsAndSelfIds())
                ->count();
        });

        return $categories;
    }

    public function listing(Request $request, string $locale, ?string $categorySlug = null): array
    {
        $activeCategorySlug = $categorySlug ?: $request->string('category')->toString();
        $categories = $this->categories($locale);
        $activeCategory = $activeCategorySlug
            ? $categories->first(fn (Category $category): bool => $category->translation?->slug === $activeCategorySlug)
            : null;

        $posts = Post::query()
            ->active()
            ->withPublishedTranslation($locale)
            ->when($activeCategory, fn (Builder $query) => $query->whereIn('category_id', $activeCategory->descendantsAndSelfIds()))
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
                'translation.media',
                'category.translation' => fn ($query) => $query->where('locale', $locale),
            ])
            ->latest('created_at')
            ->paginate(9)
            ->withQueryString();

        return [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'posts' => $posts,
        ];
    }

    public function detail(string $locale, string $categorySlug, string $postSlug): array
    {
        $translation = PostTranslation::query()
            ->published()
            ->locale($locale)
            ->slug($postSlug)
            ->whereHas('post.category.translations', fn (Builder $query) => $query
                ->where('locale', $locale)
                ->where('slug', $categorySlug)
                ->where('is_published', true))
            ->with([
                'post.category.translations',
                'post.author',
                'post.translations',
                'media',
            ])
            ->firstOrFail();

        $post = $translation->post;

        abort_if(! $post || ! $post->is_active, 404);

        [$content, $toc] = $this->tableOfContents($translation->content);

        return [
            'post' => $post,
            'translation' => $translation,
            'categoryTranslation' => $post->category?->translations->firstWhere('locale', $locale),
            'content' => $content,
            'toc' => $toc,
            'relatedPosts' => $this->relatedPosts($post, $locale),
        ];
    }

    private function relatedPosts(Post $post, string $locale)
    {
        $relatedPosts = Post::query()
            ->active()
            ->withPublishedTranslation($locale)
            ->whereKeyNot($post->id)
            ->when($post->category_id, fn (Builder $query) => $query->where('category_id', $post->category_id))
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
                'translation.media',
                'category.translation' => fn ($query) => $query->where('locale', $locale),
            ])
            ->latest('created_at')
            ->limit(3)
            ->get();

        if ($relatedPosts->count() >= 3) {
            return $relatedPosts;
        }

        $extraPosts = Post::query()
            ->active()
            ->withPublishedTranslation($locale)
            ->whereKeyNot($post->id)
            ->whereNotIn('id', $relatedPosts->pluck('id'))
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
                'translation.media',
                'category.translation' => fn ($query) => $query->where('locale', $locale),
            ])
            ->latest('created_at')
            ->limit(3 - $relatedPosts->count())
            ->get();

        return $relatedPosts->concat($extraPosts)->values();
    }

    private function tableOfContents(?string $content): array
    {
        if (blank($content)) {
            return ['', []];
        }

        $usedIds = [];
        $items = [];

        $content = preg_replace_callback('/<h([2-3])([^>]*)>(.*?)<\/h\1>/is', function (array $matches) use (&$usedIds, &$items): string {
            $level = (int) $matches[1];
            $attributes = $matches[2] ?? '';
            $innerHtml = $matches[3] ?? '';
            $title = trim(html_entity_decode(strip_tags($innerHtml)));

            if ($title === '') {
                return $matches[0];
            }

            if (preg_match('/\sid=(["\'])(.*?)\1/i', $attributes, $idMatch)) {
                $id = $idMatch[2];
            } else {
                $baseId = Str::slug($title) ?: 'section';
                $id = $baseId;
                $suffix = 2;

                while (isset($usedIds[$id])) {
                    $id = $baseId . '-' . $suffix++;
                }

                $attributes .= ' id="' . e($id) . '"';
            }

            $usedIds[$id] = true;
            $items[] = [
                'id' => $id,
                'title' => $title,
                'level' => $level,
            ];

            return '<h' . $level . $attributes . '>' . $innerHtml . '</h' . $level . '>';
        }, $content);

        return [$content, $items];
    }
}
