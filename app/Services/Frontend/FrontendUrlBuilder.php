<?php

namespace App\Services\Frontend;

use App\Enums\CategoryType;
use App\Enums\RouteSegments;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Post;
use App\Models\PostTranslation;

class FrontendUrlBuilder
{
    public function home(string $locale): string
    {
        return RouteSegments::home($locale);
    }

    public function listing(string $type, string $locale): string
    {
        return RouteSegments::url($type, $locale);
    }

    public function category(Category $category, ?CategoryTranslation $translation, string $locale): ?string
    {
        if (! $translation?->slug) {
            return null;
        }

        $sectionKey = match ($category->type) {
            CategoryType::Product->value => 'products',
            CategoryType::Post->value => 'news',
            CategoryType::Service->value => 'services',
            default => 'categories',
        };

        return RouteSegments::url($sectionKey, $locale, $translation->slug);
    }

    public function product(Product $product, ?ProductTranslation $translation, string $locale): ?string
    {
        if (! $translation?->slug) {
            return null;
        }

        $category = $product->relationLoaded('category') ? $product->category : null;
        $categoryTranslation = null;

        if ($category?->relationLoaded('translation')) {
            $categoryTranslation = $category->translation;
        } elseif ($category?->relationLoaded('translations')) {
            $categoryTranslation = $category->translations->firstWhere('locale', $locale);
        }

        $segments = collect([$categoryTranslation?->slug, $translation->slug])
            ->filter()
            ->all();

        return RouteSegments::url('products', $locale, ...$segments);
    }

    public function post(Post $post, ?PostTranslation $translation, string $locale): ?string
    {
        if (! $translation?->slug) {
            return null;
        }

        $category = $post->relationLoaded('category') ? $post->category : null;
        $categoryTranslation = null;

        if ($category?->relationLoaded('translation')) {
            $categoryTranslation = $category->translation;
        } elseif ($category?->relationLoaded('translations')) {
            $categoryTranslation = $category->translations->firstWhere('locale', $locale);
        }

        $segments = collect([$categoryTranslation?->slug, $translation->slug])
            ->filter()
            ->all();

        return RouteSegments::url('news', $locale, ...$segments);
    }
}
