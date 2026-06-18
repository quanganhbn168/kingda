<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\ContactMessageStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('company')
                    ->searchable(),
                TextColumn::make('subject')
                    ->searchable(),
                TextColumn::make('source')
                    ->searchable(),
                TextColumn::make('related_type')
                    ->searchable(),
                TextColumn::make('related_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(fn (?string $state): ?string => $state ? ContactMessageStatus::tryFrom($state)?->label() ?? $state : null)
                    ->searchable(),
                TextColumn::make('read_at')
                    ->dateTime()
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
                //
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
