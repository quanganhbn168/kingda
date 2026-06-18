<?php

namespace App\Services\Frontend;

use App\Enums\CategoryType;
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
        return url($locale === 'vi' ? '/' : '/' . $locale);
    }

    public function listing(string $type, string $locale): string
    {
        $segment = match ($type) {
            'about' => $locale === 'vi' ? 'gioi-thieu' : 'about',
            'products' => $locale === 'vi' ? 'san-pham' : 'products',
            'news' => $locale === 'vi' ? 'tin-tuc' : 'news',
            'contact' => $locale === 'vi' ? 'lien-he' : 'contact',
            'industries' => $locale === 'vi' ? 'linh-vuc' : 'industries',
        };

        return url($locale === 'vi' ? '/' . $segment : '/' . $locale . '/' . $segment);
    }

    public function category(Category $category, ?CategoryTranslation $translation, string $locale): ?string
    {
        if (! $translation?->slug) {
            return null;
        }

        $base = match ($category->type) {
            CategoryType::Product->value => $locale === 'vi' ? 'san-pham' : 'products',
            CategoryType::Post->value => $locale === 'vi' ? 'tin-tuc' : 'news',
            CategoryType::Service->value => $locale === 'vi' ? 'dich-vu' : 'services',
            default => 'categories',
        };

        return url($locale === 'vi'
            ? '/' . $base . '/' . $translation->slug
            : '/' . $locale . '/' . $base . '/' . $translation->slug);
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

        $path = collect([
            $locale === 'vi' ? 'san-pham' : 'products',
            $categoryTranslation?->slug,
            $translation->slug,
        ])->filter()->join('/');

        return url($locale === 'vi' ? '/' . $path : '/' . $locale . '/' . $path);
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

        $path = collect([
            $locale === 'vi' ? 'tin-tuc' : 'news',
            $categoryTranslation?->slug,
            $translation->slug,
        ])->filter()->join('/');

        return url($locale === 'vi' ? '/' . $path : '/' . $locale . '/' . $path);
    }
}
