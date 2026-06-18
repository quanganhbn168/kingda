<?php

namespace App\Filament\Resources\Slides\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Mã')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('translation.title')
                    ->label('Tiêu đề')
                    ->searchable(),
                TextColumn::make('location')
                    ->label('Vị trí')
                    ->badge()
                    ->searchable(),
                TextColumn::make('media_type')
                    ->label('Media')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'video_upload' => 'Video tải lên',
                        'video_embed' => 'Video nhúng',
                        default => 'Ảnh',
                    }),
                IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Bắt đầu')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label('Kết thúc')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('location')
                    ->label('Vị trí')
                    ->options([
                        'home' => 'Trang chủ',
                        'about' => 'Giới thiệu',
                        'product' => 'Sản phẩm',
                        'service' => 'Dịch vụ',
                    ]),
                SelectFilter::make('media_type')
                    ->label('Media')
                    ->options([
                        'image' => 'Ảnh',
                        'video_upload' => 'Video tải lên',
                        'video_embed' => 'Video nhúng',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
