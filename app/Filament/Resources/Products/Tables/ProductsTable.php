<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('translation.name')
                    ->label('Tên sản phẩm')
                    ->description(fn (Product $record): string => $record->sku ? 'SKU: ' . $record->sku : 'SKU: N/A')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas('translations', fn ($q) => $q->where('name', 'like', "%{$search}%"))->orWhere('sku', 'like', "%{$search}%"))
                    ->sortable(),
                TextColumn::make('category.translation.name')
                    ->label('Danh mục')
                    ->searchable(),
                ToggleColumn::make('is_home')
                    ->label('Trang chủ')
                    ->sortable(),
                ToggleColumn::make('is_featured')
                    ->label('Nổi bật')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Danh mục')
                    ->options(fn (): array => Category::query()
                        ->where('type', CategoryType::Product->value)
                        ->with('translations')
                        ->ordered()
                        ->get()
                        ->mapWithKeys(function (Category $category): array {
                            $translation = $category->translations->firstWhere('locale', 'vi')
                                ?: $category->translations->first();

                            return [$category->id => $translation?->name ?: 'Danh mục #'.$category->id];
                        })
                        ->all())
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('change_category')
                        ->label('Đổi danh mục')
                        ->icon('heroicon-o-folder-open')
                        ->form([
                            Select::make('category_id')
                                ->label('Chọn danh mục mới')
                                ->options(fn (): array => Category::query()
                                    ->where('type', CategoryType::Product->value)
                                    ->with('translations')
                                    ->ordered()
                                    ->get()
                                    ->mapWithKeys(function (Category $category): array {
                                        $translation = $category->translations->firstWhere('locale', 'vi')
                                            ?: $category->translations->first();
                                        return [$category->id => $translation?->name ?: 'Danh mục #'.$category->id];
                                    })
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update(['category_id' => $data['category_id']]);
                            }
                        }),
                    BulkAction::make('update_status')
                        ->label('Cập nhật trạng thái')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Select::make('is_home')
                                ->label('Trang chủ')
                                ->options([
                                    '' => 'Giữ nguyên',
                                    '1' => 'Bật',
                                    '0' => 'Tắt',
                                ])
                                ->default(''),
                            Select::make('is_featured')
                                ->label('Nổi bật')
                                ->options([
                                    '' => 'Giữ nguyên',
                                    '1' => 'Bật',
                                    '0' => 'Tắt',
                                ])
                                ->default(''),
                            Select::make('is_active')
                                ->label('Kích hoạt')
                                ->options([
                                    '' => 'Giữ nguyên',
                                    '1' => 'Bật',
                                    '0' => 'Tắt',
                                ])
                                ->default(''),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $updates = [];
                            if ($data['is_home'] !== '') $updates['is_home'] = (bool) $data['is_home'];
                            if ($data['is_featured'] !== '') $updates['is_featured'] = (bool) $data['is_featured'];
                            if ($data['is_active'] !== '') $updates['is_active'] = (bool) $data['is_active'];
                            
                            if (count($updates) > 0) {
                                foreach ($records as $record) {
                                    $record->update($updates);
                                }
                            }
                        }),
                ]),
            ]);
    }
}
