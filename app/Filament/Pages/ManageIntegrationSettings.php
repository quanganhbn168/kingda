<?php

namespace App\Filament\Pages;

use App\Settings\IntegrationSettings;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageIntegrationSettings extends SettingsPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $settings = IntegrationSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracketSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'Cài đặt tích hợp';

    protected static ?string $navigationLabel = 'Tích hợp';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tracking')
                    ->columns(2)
                    ->schema([
                        TextInput::make('google_analytics_id')->label('Google Analytics ID')->maxLength(255),
                        TextInput::make('google_tag_manager_id')->label('Google Tag Manager ID')->maxLength(255),
                        TextInput::make('facebook_pixel_id')->label('Facebook Pixel ID')->maxLength(255),
                        TextInput::make('zalo_oa_id')->label('Zalo OA ID')->maxLength(255),
                        TextInput::make('messenger_page_id')->label('Messenger Page ID')->maxLength(255),
                    ]),
                Section::make('reCAPTCHA')
                    ->columns(2)
                    ->schema([
                        TextInput::make('recaptcha_site_key')->label('Site key')->maxLength(255),
                        TextInput::make('recaptcha_secret_key')->label('Secret key')->password()->revealable(),
                    ]),
                Section::make('Script')
                    ->schema([
                        Textarea::make('header_scripts')->label('Header scripts')->rows(6),
                        Textarea::make('footer_scripts')->label('Footer scripts')->rows(6),
                    ]),
            ]);
    }
}
