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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('translation.title')
                    ->label('Tiêu đề')
                    ->getStateUsing(fn (Post $record): ?string => $record->resolveTranslation()?->title)
                    ->searchable(query: fn ($query, string $search) => $query->whereHas('translations', fn ($query) => $query->where('title', 'like', "%{$search}%")))
                    ->sortable()
                    ->limit(55)
                    ->weight('medium')
                    ->copyable(fn (Post $record): bool => filled($record->slug_url))
                    ->copyableState(fn (Post $record): ?string => $record->slug_url)
                    ->copyMessage('Đã sao chép liên kết bài viết')
                    ->copyMessageDuration(2000)
                    ->tooltip(fn (Post $record): string => filled($record->slug_url)
                        ? 'Bấm để sao chép liên kết bài viết'
                        : 'Chưa thể tạo liên kết: vui lòng chọn danh mục cho bài viết.'),
                TextColumn::make('category.translation.name')
                    ->label('Danh mục')
                    ->placeholder('Chưa phân loại')
                    ->searchable(),
                ToggleColumn::make('is_featured')
                    ->label('Nổi bật')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Xuất bản')
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
                SelectFilter::make('category_id')
                    ->label('Danh mục')
                    ->options(fn (): array => Category::query()
                        ->where('type', CategoryType::Post->value)
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
                TernaryFilter::make('category_status')
                    ->label('Tình trạng danh mục')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã phân loại')
                    ->falseLabel('Chưa phân loại')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('category_id'),
                        false: fn ($query) => $query->whereNull('category_id'),
                    ),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with(['translations', 'category.translations']))
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
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
