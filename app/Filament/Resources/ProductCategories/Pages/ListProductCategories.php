<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Actions\QuickCatalogImportActions;
use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            QuickCatalogImportActions::productCategories(),
            Action::make('organizeTree')
                ->label('Sắp xếp cây')
                ->icon('heroicon-o-bars-3-bottom-left')
                ->color('gray')
                ->slideOver()
                ->modalHeading('Sắp xếp cây danh mục')
                ->modalDescription('Kéo để đổi thứ tự hoặc thả vào vùng con của danh mục khác để đổi cấp. Mọi thay đổi được lưu ngay.')
                ->modalWidth('5xl')
                ->stickyModalHeader()
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Đóng')
                ->modalContent(fn () => view('filament.resources.product-categories.components.tree-manager', [
                    'tree' => $this->categoryTree(),
                ]))
                ->visible(fn (): bool => ProductCategoryResource::canReorder()),
            CreateAction::make(),
        ];
    }

    /**
     * @param  array<int, int|string>  $targetOrder
     * @param  array<int, int|string>  $sourceOrder
     */
    public function moveCategory(int $categoryId, ?int $parentId, array $targetOrder = [], array $sourceOrder = []): void
    {
        abort_unless(ProductCategoryResource::canReorder(), 403);

        $category = Category::query()->product()->findOrFail($categoryId);
        $parent = $parentId ? Category::query()->product()->findOrFail($parentId) : null;

        if ($parent && ! $parent->canAcceptProductChildren(fresh: true)) {
            Notification::make()
                ->title('Không thể thêm danh mục con')
                ->body($parent->productChildCreationBlockReason(fresh: true))
                ->danger()
                ->send();

            return;
        }

        if ($parent && $this->wouldCreateCycle($category, $parent)) {
            Notification::make()
                ->title('Không thể chuyển vào chính nó hoặc danh mục con của nó')
                ->danger()
                ->send();

            return;
        }

        $oldParentId = $category->parent_id;

        DB::transaction(function () use ($category, $parent, $oldParentId, $targetOrder, $sourceOrder): void {
            $newParentId = $parent?->getKey();

            if ($oldParentId !== $newParentId) {
                $category->update(['parent_id' => $newParentId]);
            }

            $this->persistSiblingOrder($newParentId, $targetOrder);

            if ($oldParentId !== $newParentId) {
                $this->persistSiblingOrder($oldParentId, $sourceOrder);
            }
        });

        $this->flushCachedTableRecords();

        Notification::make()
            ->title('Đã cập nhật cây danh mục')
            ->success()
            ->send();
    }

    public function shiftCategory(int $categoryId, string $direction): void
    {
        abort_unless(ProductCategoryResource::canReorder(), 403);
        abort_unless(in_array($direction, ['up', 'down', 'indent', 'outdent'], true), 404);

        $category = Category::query()->product()->findOrFail($categoryId);
        $siblings = $this->siblings($category->parent_id);
        $index = $siblings->search(fn (Category $sibling): bool => $sibling->is($category));

        if ($index === false) {
            return;
        }

        if (in_array($direction, ['up', 'down'], true)) {
            $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

            if (! $siblings->has($swapIndex)) {
                return;
            }

            $orderedIds = $siblings->pluck('id')->all();
            [$orderedIds[$index], $orderedIds[$swapIndex]] = [$orderedIds[$swapIndex], $orderedIds[$index]];

            $this->moveCategory($category->id, $category->parent_id, $orderedIds, $orderedIds);

            return;
        }

        if ($direction === 'indent') {
            if ($index === 0) {
                return;
            }

            /** @var Category $newParent */
            $newParent = $siblings->get($index - 1);
            $targetOrder = [
                ...$this->siblings($newParent->id)->pluck('id')->all(),
                $category->id,
            ];
            $sourceOrder = $siblings->reject(fn (Category $sibling): bool => $sibling->is($category))->pluck('id')->all();

            $this->moveCategory($category->id, $newParent->id, $targetOrder, $sourceOrder);

            return;
        }

        if (! $category->parent_id) {
            return;
        }

        $parent = Category::query()->product()->find($category->parent_id);

        if (! $parent) {
            return;
        }

        $grandparentSiblings = $this->siblings($parent->parent_id);
        $parentIndex = $grandparentSiblings->search(fn (Category $sibling): bool => $sibling->is($parent));
        $targetOrder = $grandparentSiblings->pluck('id')->all();
        array_splice($targetOrder, ($parentIndex === false ? count($targetOrder) - 1 : $parentIndex) + 1, 0, [$category->id]);
        $sourceOrder = $siblings->reject(fn (Category $sibling): bool => $sibling->is($category))->pluck('id')->all();

        $this->moveCategory($category->id, $parent->parent_id, $targetOrder, $sourceOrder);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function categoryTree(): array
    {
        $categories = Category::query()
            ->product()
            ->with('translation')
            ->withCount('products')
            ->ordered()
            ->get();
        $categoryIds = array_fill_keys($categories->pluck('id')->all(), true);
        $categoriesByParent = $categories->groupBy(fn (Category $category): string => $category->parent_id && isset($categoryIds[$category->parent_id])
            ? (string) $category->parent_id
            : 'root');
        $visited = [];

        $build = function (Category $category, int $siblingIndex, int $siblingCount, bool $canOutdent) use (&$build, &$visited, $categoriesByParent): array {
            $visited[$category->id] = true;
            $children = $categoriesByParent
                ->get((string) $category->id, collect())
                ->reject(fn (Category $child): bool => isset($visited[$child->id]))
                ->values();

            return [
                'id' => $category->id,
                'name' => $category->translation?->name ?: 'Danh mục #'.$category->id,
                'is_active' => $category->is_active,
                'products_count' => $category->products_count,
                'can_accept_children' => $category->canAcceptProductChildren(),
                'can_edit' => ProductCategoryResource::canEdit($category),
                'edit_url' => ProductCategoryResource::getUrl('edit', ['record' => $category]),
                'can_move_up' => $siblingIndex > 0,
                'can_move_down' => $siblingIndex < ($siblingCount - 1),
                'can_indent' => $siblingIndex > 0,
                'can_outdent' => $canOutdent,
                'children' => $children
                    ->map(fn (Category $child, int $index): array => $build($child, $index, $children->count(), true))
                    ->all(),
            ];
        };

        $roots = $categoriesByParent->get('root', collect())->values();
        $tree = $roots
            ->map(fn (Category $category, int $index): array => $build($category, $index, $roots->count(), false))
            ->all();

        // Malformed legacy rows remain visible and can be dragged back into a valid tree.
        foreach ($categories as $category) {
            if (! isset($visited[$category->id])) {
                $tree[] = $build($category, count($tree), count($tree) + 1, false);
            }
        }

        return $tree;
    }

    /**
     * @param  array<int, int|string>  $orderedIds
     */
    protected function persistSiblingOrder(?int $parentId, array $orderedIds): void
    {
        $siblings = $this->siblings($parentId);
        $validIds = array_fill_keys($siblings->pluck('id')->all(), true);
        $orderedIds = collect($orderedIds)
            ->map(fn (int|string $id): int => (int) $id)
            ->filter(fn (int $id): bool => isset($validIds[$id]))
            ->unique()
            ->values();
        $finalOrder = $orderedIds
            ->concat($siblings->pluck('id')->reject(fn (int $id): bool => $orderedIds->contains($id)))
            ->values();

        foreach ($finalOrder as $index => $id) {
            Category::query()->whereKey($id)->update(['sort_order' => ($index + 1) * 10]);
        }
    }

    /**
     * @return Collection<int, Category>
     */
    protected function siblings(?int $parentId): Collection
    {
        return Category::query()
            ->product()
            ->where('parent_id', $parentId)
            ->ordered()
            ->get();
    }

    protected function wouldCreateCycle(Category $category, Category $parent): bool
    {
        $visited = [];
        $cursor = $parent;

        while ($cursor) {
            if ($cursor->is($category) || isset($visited[$cursor->id])) {
                return true;
            }

            $visited[$cursor->id] = true;
            $cursor = $cursor->parent_id
                ? Category::query()->product()->find($cursor->parent_id)
                : null;
        }

        return false;
    }
}
