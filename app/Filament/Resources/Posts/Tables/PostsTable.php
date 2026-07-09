<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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
                    ->getStateUsing(fn(Post $record): ?string => $record->resolveTranslation()?->title)
                    ->searchable(query: fn($query, string $search) => $query->whereHas('translations', fn($query) => $query->where('title', 'like', "%{$search}%")))
                    ->sortable()
                    ->limit(55)
                    ->weight('medium')
                    ->copyable()
                    ->copyableState(fn(Post $record): string => $record->slug_url)
                    ->copyMessage('Đã sao chép liên kết bài viết')
                    ->copyMessageDuration(2000)
                    ->tooltip('Bấm để sao chép liên kết bài viết'),
                TextColumn::make('category.translation.name')
                    ->label('Danh mục')
                    ->searchable(),
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
                    ->options(fn(): array => Category::query()
                        ->where('type', CategoryType::Post->value)
                        ->with('translations')
                        ->ordered()
                        ->get()
                        ->mapWithKeys(function (Category $category): array {
                            $translation = $category->translations->firstWhere('locale', 'vi')
                                ?: $category->translations->first();

                            return [$category->id => $translation?->name ?: 'Danh mục #' . $category->id];
                        })
                        ->all())
                    ->searchable()
                    ->preload(),
            ])
            ->modifyQueryUsing(fn($query) => $query->with(['translations', 'category.translations']))
            ->defaultSort('created_at', 'desc')
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
