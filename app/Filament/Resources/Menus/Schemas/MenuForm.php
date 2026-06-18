<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Enums\MenuLocation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin menu')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên menu')
                            ->required()
                            ->maxLength(255),
                        Select::make('location')
                            ->label('Vị trí')
                            ->options(MenuLocation::options())
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Thứ tự')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Kích hoạt')
                            ->required(),
                    ]),
            ]);
    }
}
