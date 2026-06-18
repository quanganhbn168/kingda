<?php

namespace App\Filament\Resources\Industries\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class IndustryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('translation.title')->label('Tên'),
                TextEntry::make('icon')->label('Icon'),
                IconEntry::make('is_featured')->label('Hiện trang chủ')->boolean(),
                IconEntry::make('is_active')->label('Kích hoạt')->boolean(),
            ]);
    }
}
