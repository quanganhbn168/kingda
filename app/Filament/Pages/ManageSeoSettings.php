<?php

namespace App\Filament\Pages;

use App\Settings\SeoSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSeoSettings extends SettingsPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $settings = SeoSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';

    protected static ?int $navigationSort = 40;

    protected static ?string $title = 'Cài đặt SEO';

    protected static ?string $navigationLabel = 'SEO';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('SEO mặc định')
                    ->columns(2)
                    ->schema([
                        TextInput::make('default_seo_title')->label('SEO title')->maxLength(255),
                        TextInput::make('default_canonical_url')->label('Canonical URL')->url(),
                        Textarea::make('default_seo_description')->label('SEO description'),
                        TextInput::make('default_robots')->label('Robots')->default('index,follow')->maxLength(255),
                        FileUpload::make('default_og_image')
                            ->label('OG image mặc định')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('settings/seo'),
                    ]),
            ]);
    }
}
