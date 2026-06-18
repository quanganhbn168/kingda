<?php

namespace App\Filament\Pages;

use App\Settings\SiteSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSiteSettings extends SettingsPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $settings = SiteSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Cài đặt website';

    protected static ?string $navigationLabel = 'Website';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nhận diện')
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Tên website')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('site_slogan')
                            ->label('Slogan')
                            ->maxLength(255),
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('settings/site'),
                        FileUpload::make('logo_footer')
                            ->label('Logo footer')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('settings/site'),
                        FileUpload::make('favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('settings/site'),
                    ]),
            ]);
    }
}
