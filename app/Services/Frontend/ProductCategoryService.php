<?php

namespace App\Services\Frontend;

use App\Models\Category;
use App\Models\MenuItem;
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

    public function replaceProductMenuChildren(Collection $menuItems, string $locale): Collection
    {
        $categoryMenuItems = $this->categoryMenuItems(
            $this->tree($this->all($locale)),
            $locale
        );

        $menuItems->each(function (MenuItem $item) use ($categoryMenuItems): void {
            if ($this->isProductIndexItem($item)) {
                $item->setRelation('childrenRecursive', $categoryMenuItems);
            }
        });

        return $menuItems;
    }

    private function categoryMenuItems(Collection $categories, string $locale): Collection
    {
        return $categories
            ->map(function (Category $category) use ($locale): MenuItem {
                $translation = $category->translation;
                $item = new MenuItem([
                    'locale' => $locale,
                    'label' => $translation?->name ?: '',
                    'link_type' => 'custom',
                    'url' => $translation?->public_url,
                    'target' => '_self',
                    'is_active' => true,
                    'sort_order' => $category->sort_order,
                ]);

                $item->setRelation(
                    'childrenRecursive',
                    $this->categoryMenuItems($category->children, $locale)
                );

                return $item;
            })
            ->values();
    }

    private function isProductIndexItem(MenuItem $item): bool
    {
        $path = trim(parse_url($item->resolved_url ?: '', PHP_URL_PATH) ?: '', '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if (isset($segments[0]) && in_array($segments[0], ['vi', 'en', 'zh'], true)) {
            array_shift($segments);
        }

        return in_array(implode('/', $segments), ['san-pham', 'products'], true);
    }
}
