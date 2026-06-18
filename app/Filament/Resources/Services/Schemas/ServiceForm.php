<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\CategoryType;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Danh mục')
                    ->options(fn (): array => Category::query()
                        ->where('type', CategoryType::Service->value)
                        ->with('translation')
                        ->ordered()
                        ->get()
                        ->mapWithKeys(fn (Category $category): array => [
                            $category->id => $category->translation?->name ?: 'Danh mục #' . $category->id,
                        ])
                        ->all())
                    ->searchable()
                    ->preload(),
                TextInput::make('code'),
                Toggle::make('is_featured')
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
