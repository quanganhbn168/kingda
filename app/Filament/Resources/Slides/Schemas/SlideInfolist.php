<?php

namespace App\Filament\Resources\Slides\Schemas;

use App\Models\Slide;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SlideInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('key')
                    ->placeholder('-'),
                TextEntry::make('location')
                    ->label('Vị trí'),
                TextEntry::make('theme')
                    ->label('Giao diện')
                    ->placeholder('-'),
                TextEntry::make('text_position')
                    ->label('Vị trí chữ'),
                IconEntry::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean(),
                TextEntry::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric(),
                TextEntry::make('starts_at')
                    ->label('Bắt đầu')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('ends_at')
                    ->label('Kết thúc')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Slide $record): bool => $record->trashed()),
            ]);
    }
}
