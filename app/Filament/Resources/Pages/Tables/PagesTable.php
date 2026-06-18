<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Enums\PageTemplate;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Mã trang')
                    ->searchable(),
                TextColumn::make('currentTranslation.title')
                    ->label('Tiêu đề')
                    ->searchable(),
                TextColumn::make('template')
                    ->label('Giao diện')
                    ->formatStateUsing(fn (?string $state): ?string => $state ? PageTemplate::tryFrom($state)?->label() ?? $state : null)
                    ->searchable(),
                ToggleColumn::make('is_home')
                    ->label('Trang chủ')
                    ->sortable(),
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
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
