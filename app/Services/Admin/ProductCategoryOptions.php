<?php

namespace App\Services\Admin;

use App\Models\Category;
use Illuminate\Support\Collection;

class ProductCategoryOptions
{
    public function tree(?Category $excludedCategory = null): array
    {
        $excludedIds = $excludedCategory?->exists
            ? [$excludedCategory->getKey(), ...$excludedCategory->descendantIds()]
            : [];

        return $this->build($this->categories($excludedIds, withoutProducts: true), leavesOnly: false);
    }

    public function leaves(): array
    {
        return $this->build($this->categories(), leavesOnly: true);
    }

    private function categories(array $excludedIds = [], bool $withoutProducts = false): Collection
    {
        return Category::query()
            ->product()
            ->when($excludedIds !== [], fn ($query) => $query->whereNotIn('id', $excludedIds))
            ->when($withoutProducts, fn ($query) => $query->whereDoesntHave('products'))
            ->with('translation')
            ->ordered()
            ->get();
    }

    private function build(Collection $categories, bool $leavesOnly): array
    {
        $categoryIds = array_fill_keys($categories->pluck('id')->all(), true);
        $categoriesByParent = $categories->groupBy(function (Category $category) use ($categoryIds): string {
            if ($category->parent_id && isset($categoryIds[$category->parent_id])) {
                return (string) $category->parent_id;
            }

            return 'root';
        });

        $options = [];
        $visited = [];

        $appendCategory = function (Category $category, int $depth, array $ancestors = []) use (&$appendCategory, &$options, &$visited, $categoriesByParent, $leavesOnly): void {
            if (isset($visited[$category->id])) {
                return;
            }

            $visited[$category->id] = true;
            $name = $category->translation?->name ?: 'Danh mục #'.$category->id;
            $path = [...$ancestors, $name];
            $children = $categoriesByParent
                ->get((string) $category->id, collect())
                ->reject(fn (Category $child): bool => isset($visited[$child->id]));

            if (! $leavesOnly) {
                $options[$category->id] = $this->indentedLabel($name, $depth);
            } elseif ($children->isEmpty()) {
                $options[$category->id] = $this->indentedLabel(implode(' / ', $path), $depth);
            }

            foreach ($children as $child) {
                $appendCategory($child, $depth + 1, $path);
            }
        };

        foreach ($categoriesByParent->get('root', collect()) as $category) {
            $appendCategory($category, 0);
        }

        // Keep malformed legacy rows visible instead of silently dropping them.
        foreach ($categories as $category) {
            $appendCategory($category, 0);
        }

        return $options;
    }

    private function indentedLabel(string $label, int $depth): string
    {
        if ($depth === 0) {
            return $label;
        }

        return str_repeat('　', $depth).'— '.$label;
    }
}
