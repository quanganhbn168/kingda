<?php

namespace App\Filament\Resources\PostCategories\Tables;

use App\Filament\Actions\PostCategoryDeleteActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PostCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('translation.name')
                    ->label('Tên danh mục')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas('translations', fn ($query) => $query->where('name', 'like', "%{$search}%")))
                    ->sortable(),
                TextColumn::make('parent.translation.name')
                    ->label('Danh mục cha')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->sortable(),
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

            ])
            ->recordActions([
                EditAction::make(),
                PostCategoryDeleteActions::single(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->toolbarActions([
                BulkActionGroup::make([
                    PostCategoryDeleteActions::bulk(),
                ]),
            ]);
    }
}
