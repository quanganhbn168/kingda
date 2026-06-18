<?php

namespace App\Filament\Pages;

use App\Settings\CompanySettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageCompanySettings extends SettingsPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $settings = CompanySettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Cài đặt công ty';

    protected static ?string $navigationLabel = 'Công ty';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pháp lý')
                    ->columns(2)
                    ->schema([
                        TextInput::make('company_name')->label('Tên công ty')->required()->maxLength(255),
                        TextInput::make('company_short_name')->label('Tên viết tắt')->maxLength(255),
                        TextInput::make('company_english_name')->label('Tên tiếng Anh')->maxLength(255),
                        TextInput::make('tax_code')->label('Mã số thuế')->maxLength(255),
                        TextInput::make('business_license_number')->label('Số ĐKKD')->maxLength(255),
                        TextInput::make('business_license_date')->label('Ngày cấp')->maxLength(255),
                        TextInput::make('business_license_place')->label('Nơi cấp')->maxLength(255),
                        TextInput::make('founded_year')->label('Năm thành lập')->maxLength(255),
                        TextInput::make('legal_representative')->label('Người đại diện')->maxLength(255),
                        TextInput::make('legal_representative_position')->label('Chức vụ')->maxLength(255),
                    ]),
                Section::make('Địa chỉ và ngành nghề')
                    ->columns(2)
                    ->schema([
                        Textarea::make('registered_address')->label('Địa chỉ đăng ký'),
                        Textarea::make('office_address')->label('Địa chỉ văn phòng'),
                        Textarea::make('factory_address')->label('Địa chỉ nhà máy'),
                        Textarea::make('business_fields')->label('Lĩnh vực hoạt động'),
                    ]),
                Section::make('Chỉ số năng lực')
                    ->schema([
                        Repeater::make('stats')
                            ->label('Chỉ số')
                            ->schema([
                                TextInput::make('value')
                                    ->label('Số liệu')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('label')
                                    ->label('Nhãn')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label('Mô tả')
                                    ->rows(2),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 3,
                            ])
                            ->addActionLabel('Thêm chỉ số'),
                    ]),
                Section::make('Ngân hàng')
                    ->columns(2)
                    ->schema([
                        TextInput::make('bank_name')->label('Ngân hàng')->maxLength(255),
                        TextInput::make('bank_account_name')->label('Chủ tài khoản')->maxLength(255),
                        TextInput::make('bank_account_number')->label('Số tài khoản')->maxLength(255),
                    ]),
            ]);
    }
}
