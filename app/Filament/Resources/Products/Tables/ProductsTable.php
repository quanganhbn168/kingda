<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\CategoryType;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('translation.name')
                    ->label('Tên sản phẩm')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas('translations', fn ($query) => $query->where('name', 'like', "%{$search}%")))
                    ->sortable(),
                TextColumn::make('category.translation.name')
                    ->label('Danh mục')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                IconColumn::make('is_featured')
                    ->label('Nổi bật')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
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
                ]),
            ]);
    }
}
