<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category.id')
                    ->label('Category')
                    ->placeholder('-'),
                TextEntry::make('sku')
                    ->label('SKU')
                    ->placeholder('-'),
                TextEntry::make('price')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('sale_price')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('unit')
                    ->placeholder('-'),
                IconEntry::make('is_featured')
                    ->boolean(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('sort_order')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
