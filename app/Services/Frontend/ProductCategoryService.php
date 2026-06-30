<?php

namespace App\Services\Frontend;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductCategoryService
{
    public function all(string $locale): Collection
    {
        return Category::query()
            ->product()
            ->active()
            ->whereHas('translations', fn (Builder $query) => $query
                ->where('locale', $locale)
                ->where('is_published', true))
            ->with([
                'translation' => fn ($query) => $query->where('locale', $locale),
            ])
            ->withCount([
                'products' => fn (Builder $query) => $query
                    ->active()
                    ->withPublishedTranslation($locale),
            ])
            ->ordered()
            ->get();
    }

    public function tree(Collection $categories): Collection
    {
        $categoriesByParent = $categories->groupBy(
            fn (Category $category): string => (string) ($category->parent_id ?: 'root')
        );

        $buildTree = function (int|string $parentId = 'root') use (&$buildTree, $categoriesByParent): Collection {
            return $categoriesByParent
                ->get((string) $parentId, collect())
                ->values()
                ->map(function (Category $category) use (&$buildTree): Category {
                    $category->setRelation('children', $buildTree($category->id));

                    return $category;
                });
        };

        return $buildTree();
    }
}
