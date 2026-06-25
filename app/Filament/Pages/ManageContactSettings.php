<?php

namespace App\Filament\Pages;

use App\Settings\ContactSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageContactSettings extends SettingsPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $settings = ContactSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';

    protected static ?int $navigationSort = 30;

    protected static ?string $title = 'Cài đặt liên hệ';

    protected static ?string $navigationLabel = 'Liên hệ';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kênh liên hệ')
                    ->columns(2)
                    ->schema([
                        Repeater::make('hotlines')
                            ->label('Hotline')
                            ->simple(TextInput::make('value')->label('Hotline'))
                            ->default([]),
                        Repeater::make('phones')
                            ->label('Điện thoại')
                            ->simple(TextInput::make('value')->label('Điện thoại'))
                            ->default([]),
                        Repeater::make('emails')
                            ->label('Email')
                            ->simple(TextInput::make('value')->label('Email')->email())
                            ->default([]),
                        Repeater::make('social_links')
                            ->label('Mạng xã hội')
                            ->schema([
                                TextInput::make('label')->label('Tên')->required(),
                                TextInput::make('url')->label('URL')->url()->required(),
                            ])
                            ->columns(2)
                            ->default([]),
                        FileUpload::make('wechat_qr_image')
                            ->label('Mã QR WeChat')
                            ->helperText('Ảnh QR hiển thị khi khách bấm nút WeChat nổi.')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('settings/contact')
                            ->imagePreviewHeight('180')
                            ->columnSpanFull(),
                    ]),
                Section::make('Thông tin mặc định')
                    ->columns(2)
                    ->schema([
                        Tabs::make('Thông tin địa chỉ và giờ làm việc theo ngôn ngữ')
                            ->tabs(collect(Locale::cases())
                                ->map(fn (Locale $locale): Tab => Tab::make($locale->label())
                                    ->schema([
                                        Textarea::make('default_address.'.$locale->value)
                                            ->label('Địa chỉ'),
                                        TextInput::make('default_working_hours.'.$locale->value)
                                            ->label('Giờ làm việc')
                                            ->maxLength(255),
                                    ]))
                                ->all())->columnSpanFull(),
                        TextInput::make('default_google_map_url')->label('Google Map URL')->url(),
                        Textarea::make('default_google_map_embed')->label('Google Map embed')->columnSpanFull(),
                        TextInput::make('contact_form_receiver_email')->label('Email nhận form')->email(),
                    ]),
                Section::make('Đội ngũ')
                    ->columns(2)
                    ->schema([
                        Repeater::make('sales_contacts')
                            ->label('Liên hệ kinh doanh')
                            ->schema([
                                TextInput::make('name')->label('Tên'),
                                TextInput::make('phone')->label('Điện thoại'),
                                TextInput::make('email')->label('Email')->email(),
                            ])
                            ->columns(3)
                            ->default([]),
                        Repeater::make('support_contacts')
                            ->label('Liên hệ hỗ trợ')
                            ->schema([
                                TextInput::make('name')->label('Tên'),
                                TextInput::make('phone')->label('Điện thoại'),
                                TextInput::make('email')->label('Email')->email(),
                            ])
                            ->columns(3)
                            ->default([]),
                    ]),
            ]);
    }
}
