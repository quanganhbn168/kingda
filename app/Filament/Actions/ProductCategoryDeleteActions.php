<?php

namespace App\Filament\Actions;

use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class ProductCategoryDeleteActions
{
    public static function single(): DeleteAction
    {
        return DeleteAction::make()
            ->disabled(fn (Category $record): bool => ! $record->canDeleteProductCategory())
            ->tooltip(fn (Category $record): ?string => $record->productCategoryDeletionBlockReason())
            ->modalDescription('Chỉ danh mục lá không chứa sản phẩm mới có thể xóa.')
            ->databaseTransaction()
            ->using(function (Category $record): bool {
                $lockedCategory = Category::query()
                    ->whereKey($record->getKey())
                    ->lockForUpdate()
                    ->first();

                return (bool) $lockedCategory?->delete();
            })
            ->failureNotificationTitle('Không thể xóa danh mục')
            ->failureNotificationBody(fn (Category $record): string => $record->productCategoryDeletionBlockReason(fresh: true)
                ?? 'Dữ liệu danh mục vừa thay đổi. Vui lòng tải lại trang và thử lại.');
    }

    public static function bulk(): DeleteBulkAction
    {
        return DeleteBulkAction::make()
            ->modalDescription('Chỉ danh mục lá không chứa sản phẩm mới có thể xóa. Nếu một danh mục không hợp lệ, toàn bộ lượt xóa sẽ dừng lại.')
            ->databaseTransaction()
            ->using(function (DeleteBulkAction $action, Collection $records): void {
                $lockedCategories = Category::query()
                    ->whereKey($records->modelKeys())
                    ->with(['translation'])
                    ->withCount(['products', 'children'])
                    ->lockForUpdate()
                    ->get();

                if ($lockedCategories->count() !== $records->count()) {
                    self::notifyBulkDeletionBlocked('Một hoặc nhiều danh mục không còn tồn tại. Vui lòng tải lại trang và thử lại.');
                    $action->halt(shouldRollBackDatabaseTransaction: true);
                }

                $blockedCategories = $lockedCategories
                    ->filter(fn (Category $category): bool => ! $category->canDeleteProductCategory());

                if ($blockedCategories->isNotEmpty()) {
                    self::notifyBulkDeletionBlocked(self::blockedCategoryList($blockedCategories));
                    $action->halt(shouldRollBackDatabaseTransaction: true);
                }

                foreach ($lockedCategories as $category) {
                    if ($category->delete()) {
                        continue;
                    }

                    self::notifyBulkDeletionBlocked(
                        $category->productCategoryDeletionBlockReason(fresh: true)
                            ?? 'Dữ liệu danh mục vừa thay đổi. Vui lòng tải lại trang và thử lại.'
                    );
                    $action->halt(shouldRollBackDatabaseTransaction: true);
                }
            });
    }

    private static function blockedCategoryList(Collection $categories): string
    {
        $lines = $categories
            ->take(5)
            ->map(function (Category $category): string {
                $name = $category->translation?->name ?? "Danh mục #{$category->getKey()}";

                return "• {$name}: {$category->productCategoryDeletionBlockReason()}";
            });

        if ($categories->count() > 5) {
            $lines->push('• Và '.($categories->count() - 5).' danh mục khác.');
        }

        return $lines->implode("\n");
    }

    private static function notifyBulkDeletionBlocked(string $body): void
    {
        Notification::make()
            ->title('Không thể xóa các danh mục đã chọn')
            ->body($body)
            ->danger()
            ->persistent()
            ->send();
    }
}
