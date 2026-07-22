<?php

namespace App\Services\Frontend;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductCatalogService
{
    public function __construct(
        private readonly ProductCategoryService $productCategories,
    ) {}

    public function categories(string $locale)
    {
        return $this->productCategories->all($locale)->values();
    }

    public function listing(Request $request, string $locale, ?string $categorySlug = null): array
    {
        $activeCategorySlug = $categorySlug ?: $request->string('category')->toString();
        $categories = $this->categories($locale);
        $activeCategory = $activeCategorySlug
            ? $categories->first(fn (Category $category): bool => $category->translation?->slug === $activeCategorySlug)
            : null;

        abort_if(filled($activeCategorySlug) && ! $activeCategory, 404);

        $activeCategoryAncestorIds = [];
        $parentId = $activeCategory?->parent_id;
        while ($parentId) {
            $activeCategoryAncestorIds[] = (int) $parentId;
            $parentId = $categories->firstWhere('id', $parentId)?->parent_id;
        }

        $products = Product::query()
            ->active()
            ->withRoutableCategory()
            ->withPublishedTranslation($locale)
            ->when($activeCategory, fn (Builder $query) => $query->whereIn('category_id', $activeCategory->descendantsAndSelfIds()))
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
                'translation.media',
                'translations' => fn ($query) => $query->where('locale', 'vi'),
                'translations.media',
                'category.translation' => fn ($query) => $query->where('locale', $locale),
            ])
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return [
            'categories' => $categories,
            'categoryTree' => $this->productCategories->tree($categories),
            'activeCategory' => $activeCategory,
            'activeCategoryAncestorIds' => $activeCategoryAncestorIds,
            'products' => $products,
        ];
    }

    public function detail(string $locale, string $categorySlug, string $productSlug): array
    {
        $translation = ProductTranslation::query()
            ->published()
            ->locale($locale)
            ->slug($productSlug)
            ->whereHas('product.category.translations', fn (Builder $query) => $query
                ->where('locale', $locale)
                ->where('slug', $categorySlug)
                ->where('is_published', true))
            ->with([
                'product.category.translations',
                'media',
            ])
            ->firstOrFail();

        $product = $translation->product;

        abort_if(! $product || ! $product->is_active, 404);

        $categoryTranslation = $product->category?->translations->firstWhere('locale', $locale);

        return [
            'product' => $product,
            'translation' => $translation,
            'categoryTranslation' => $categoryTranslation,
            'relatedProducts' => $this->relatedProducts($product, $locale),
        ];
    }

    private function relatedProducts(Product $product, string $locale)
    {
        return Product::query()
            ->active()
            ->withRoutableCategory()
            ->withPublishedTranslation($locale)
            ->whereKeyNot($product->id)
            ->when($product->category_id, fn (Builder $query) => $query->where('category_id', $product->category_id))
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
                'translation.media',
                'category.translation' => fn ($query) => $query->where('locale', $locale),
            ] + ($locale !== 'vi' ? [
                'translations' => fn ($query) => $query->where('locale', 'vi'),
                'translations.media',
            ] : []))
            ->ordered()
            ->limit(3)
            ->get();
    }
}
