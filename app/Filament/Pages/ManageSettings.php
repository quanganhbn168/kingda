<?php

namespace App\Filament\Pages;

use App\Enums\Locale;
use App\Models\Menu;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\IntegrationSettings;
use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Cài đặt';

    protected static ?string $navigationLabel = 'Cài đặt';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            ...app(SiteSettings::class)->toArray(),
            ...app(CompanySettings::class)->toArray(),
            ...app(ContactSettings::class)->toArray(),
            ...app(SeoSettings::class)->toArray(),
            ...app(IntegrationSettings::class)->toArray(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(1)
            ->components([
                Tabs::make('Cài đặt')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Website')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->schema($this->siteSchema()),
                        Tab::make('Công ty')
                            ->icon(Heroicon::OutlinedBuildingOffice2)
                            ->schema($this->companySchema()),
                        Tab::make('Liên hệ')
                            ->icon(Heroicon::OutlinedPhone)
                            ->schema($this->contactSchema()),
                        Tab::make('SEO')
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->schema($this->seoSchema()),
                        Tab::make('Tích hợp')
                            ->icon(Heroicon::OutlinedCodeBracketSquare)
                            ->schema($this->integrationSchema()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    EmbeddedSchema::make('form'),
                ])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Lưu cài đặt')
                                ->icon(Heroicon::OutlinedCheck)
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->saveSettings(SiteSettings::class, $data, [
            'site_name',
            'site_slogan',
            'logo',
            'logo_footer',
            'footer_menu_id',
            'footer_description',
            'footer_menu_1_id',
            'footer_menu_1_title',
            'footer_menu_2_id',
            'footer_menu_2_title',
            'favicon',
        ]);

        $this->saveSettings(CompanySettings::class, $data, [
            'company_name',
            'company_short_name',
            'company_english_name',
            'tax_code',
            'business_license_number',
            'business_license_date',
            'business_license_place',
            'legal_representative',
            'legal_representative_position',
            'founded_year',
            'registered_address',
            'office_address',
            'factory_address',
            'business_fields',
            'stats',
            'bank_name',
            'bank_account_name',
            'bank_account_number',
        ]);

        $this->saveSettings(ContactSettings::class, $data, [
            'hotlines',
            'phones',
            'emails',
            'social_links',
            'zalo_qr_image',
            'wechat_qr_image',
            'default_address',
            'default_working_hours',
            'default_google_map_url',
            'default_google_map_embed',
            'contact_form_receiver_email',
            'sales_contacts',
            'support_contacts',
        ]);

        $this->saveSettings(SeoSettings::class, $data, [
            'default_seo_title',
            'default_seo_description',
            'default_og_image',
            'default_robots',
            'default_canonical_url',
        ]);

        $this->saveSettings(IntegrationSettings::class, $data, [
            'google_analytics_id',
            'google_tag_manager_id',
            'facebook_pixel_id',
            'zalo_oa_id',
            'messenger_page_id',
            'recaptcha_site_key',
            'recaptcha_secret_key',
            'header_scripts',
            'footer_scripts',
        ]);

        Notification::make()
            ->success()
            ->title('Đã lưu cài đặt')
            ->send();
    }

    /**
     * @param  class-string  $settingsClass
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     */
    private function saveSettings(string $settingsClass, array $data, array $keys): void
    {
        $settings = app($settingsClass);

        $settings->fill(collect($data)->only($keys)->all());
        $settings->save();
    }

    private function siteSchema(): array
    {
        return [
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
            Section::make('Footer')
                ->columns(1)
                ->schema([
                    Tabs::make('Nội dung footer theo ngôn ngữ')
                        ->tabs(collect(Locale::cases())
                            ->map(fn (Locale $locale): Tab => Tab::make($locale->label())
                                ->schema([
                                    Textarea::make('footer_description.'.$locale->value)
                                        ->label('Mô tả dưới logo')
                                        ->rows(3)
                                        ->maxLength(1000),
                                    TextInput::make('footer_menu_1_title.'.$locale->value)
                                        ->label('Tiêu đề menu Footer 1')
                                        ->maxLength(255),
                                    TextInput::make('footer_menu_2_title.'.$locale->value)
                                        ->label('Tiêu đề menu Footer 2')
                                        ->maxLength(255),
                                ]))
                            ->all()),
                    Select::make('footer_menu_1_id')
                        ->label('Chọn menu Footer 1')
                        ->options(fn (): array => Menu::query()
                            ->where('location', 'footer')
                            ->ordered()
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->placeholder('Chọn menu Footer 1'),
                    Select::make('footer_menu_2_id')
                        ->label('Chọn menu Footer 2')
                        ->options(fn (): array => Menu::query()
                            ->where('location', 'footer')
                            ->ordered()
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->placeholder('Chọn menu Footer 2'),
                ]),        ];
    }

    private function companySchema(): array
    {
        return [
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
                            TextInput::make('value')->label('Số liệu')->required()->maxLength(255),
                            TextInput::make('label')->label('Nhãn')->required()->maxLength(255),
                            Textarea::make('description')->label('Mô tả')->rows(2),
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
        ];
    }

    private function contactSchema(): array
    {
        return [
            Section::make('Kênh liên hệ')
                ->columns(2)
                ->schema([
                    Repeater::make('hotlines')
                        ->label('Hotline')
                        ->schema([
                            TextInput::make('phone')->label('Hotline')->required(),
                        ])
                        ->default([]),
                    Repeater::make('phones')
                        ->label('Điện thoại')
                        ->schema([
                            TextInput::make('phone')->label('Điện thoại')->required(),
                        ])
                        ->default([]),
                    Repeater::make('emails')
                        ->label('Email')
                        ->schema([
                            TextInput::make('email')->label('Email')->email()->required(),
                        ])
                        ->default([]),
                    Repeater::make('social_links')
                        ->label('Mạng xã hội')
                        ->schema([
                            TextInput::make('label')->label('Tên')->required(),
                            TextInput::make('url')->label('URL')->url()->required(),
                        ])
                        ->columns(2)
                        ->default([]),
                    FileUpload::make('zalo_qr_image')
                        ->label('Mã QR Zalo')
                        ->helperText('Ảnh QR hiển thị khi khách bấm nút Zalo nổi.')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('settings/contact')
                        ->imagePreviewHeight('180'),
                    FileUpload::make('wechat_qr_image')
                        ->label('Mã QR WeChat')
                        ->helperText('Ảnh QR hiển thị khi khách bấm nút WeChat nổi.')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('settings/contact')
                        ->imagePreviewHeight('180'),
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
        ];
    }

    private function seoSchema(): array
    {
        return [
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
        ];
    }

    private function integrationSchema(): array
    {
        return [
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
        ];
    }
}
