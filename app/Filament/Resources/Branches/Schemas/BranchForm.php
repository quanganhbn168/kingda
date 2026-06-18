<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Enums\BranchType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                Select::make('type')
                    ->label('Loại chi nhánh')
                    ->options(BranchType::options())
                    ->required()
                    ->default(BranchType::Branch->value),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('google_map_url')
                    ->url(),
                Textarea::make('google_map_embed')
                    ->columnSpanFull(),
                Toggle::make('is_head_office')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
