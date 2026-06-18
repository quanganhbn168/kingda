<?php

namespace App\Filament\Resources\Industries;

use App\Filament\Resources\Industries\Pages\CreateIndustry;
use App\Filament\Resources\Industries\Pages\EditIndustry;
use App\Filament\Resources\Industries\Pages\ListIndustries;
use App\Filament\Resources\Industries\Pages\ViewIndustry;
use App\Filament\Resources\Industries\Schemas\IndustryForm;
use App\Filament\Resources\Industries\Schemas\IndustryInfolist;
use App\Filament\Resources\Industries\Tables\IndustriesTable;
use App\Models\Industry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IndustryResource extends Resource
{
    protected static ?string $model = Industry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 23;

    protected static ?string $modelLabel = 'lĩnh vực ứng dụng';

    protected static ?string $pluralModelLabel = 'Lĩnh vực & ứng dụng';

    public static function form(Schema $schema): Schema
    {
        return IndustryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IndustryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndustriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIndustries::route('/'),
            'create' => CreateIndustry::route('/create'),
            'view' => ViewIndustry::route('/{record}'),
            'edit' => EditIndustry::route('/{record}/edit'),
        ];
    }
}
