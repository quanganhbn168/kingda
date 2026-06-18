<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('translation.title')
                    ->label('Tiêu đề')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas('translations', fn ($query) => $query->where('title', 'like', "%{$search}%")))
                    ->sortable(),
                TextColumn::make('category.translation.name')
                    ->label('Danh mục')
                    ->searchable(),
                TextColumn::make('author.name')
                    ->label('Tác giả')
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
                    ->relationship('category', 'id'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
