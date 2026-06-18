<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CertificateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('Tên'),
                TextEntry::make('description')->label('Mô tả'),
                IconEntry::make('is_active')->label('Kích hoạt')->boolean(),
                TextEntry::make('sort_order')->label('Thứ tự'),
            ]);
    }
}
