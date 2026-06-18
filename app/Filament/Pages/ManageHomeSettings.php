<?php

namespace App\Filament\Pages;

use App\Enums\Locale;
use App\Settings\HomeSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageHomeSettings extends SettingsPage
{
    protected static string $settings = HomeSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';

    protected static ?int $navigationSort = 12;

    protected static ?string $title = 'Cài đặt trang chủ';

    protected static ?string $navigationLabel = 'Trang chủ';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('home_settings_tabs')
                    ->tabs([
                        Tab::make('Chỉ số')
                            ->schema([
                                Section::make('Chỉ số nổi bật')
                                    ->schema([
                                        Repeater::make('stats')
                                            ->label('Stats')
                                            ->schema([
                                                TextInput::make('value')
                                                    ->label('Số liệu')
                                                    ->required(),

                                                ...self::localizedFields([
                                                    fn (string $path) => TextInput::make($path . '.label')
                                                        ->label('Nhãn')
                                                        ->required(),

                                                    fn (string $path) => Textarea::make($path . '.description')
                                                        ->label('Mô tả')
                                                        ->rows(2),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm chỉ số'),
                                    ]),
                            ]),

                        Tab::make('Giới thiệu')
                            ->schema([
                                Section::make('Nội dung giới thiệu')
                                    ->schema([
                                        Tabs::make('intro_translations')
                                            ->tabs(self::localizedTabs('intro', [
                                                fn (string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn (string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn (string $path) => Textarea::make($path . '.description')
                                                    ->label('Subline')
                                                    ->rows(3),

                                                fn (string $path) => Textarea::make($path . '.content')
                                                    ->label('Nội dung')
                                                    ->rows(3),

                                                fn (string $path) => TextInput::make($path . '.button_label')
                                                    ->label('Nút'),
                                            ])),
                                    ]),

                                Section::make('Media giới thiệu')
                                    ->schema([
                                        FileUpload::make('intro.image')
                                            ->label('Ảnh')
                                            ->image()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('settings/home'),

                                        FileUpload::make('intro.video_upload')
                                            ->label('Video tải lên')
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('settings/home/videos')
                                            ->acceptedFileTypes([
                                                'video/mp4',
                                                'video/webm',
                                                'video/ogg',
                                            ]),

                                        TextInput::make('intro.video_embed_url')
                                            ->label('Video nhúng')
                                            ->placeholder('https://www.youtube.com/watch?v=...'),
                                    ])
                                    ->columns(2),

                                Section::make('Các ý chính')
                                    ->schema([
                                        Repeater::make('intro.items')
                                            ->label('Các ý chính')
                                            ->schema([
                                                TextInput::make('icon')
                                                    ->label('Icon')
                                                    ->placeholder('fa-flask-vial'),

                                                ...self::localizedFields([
                                                    fn (string $path) => TextInput::make($path . '.title')
                                                        ->label('Tiêu đề')
                                                        ->required(),

                                                    fn (string $path) => Textarea::make($path . '.description')
                                                        ->label('Mô tả')
                                                        ->rows(2),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm ý chính'),
                                    ]),
                            ]),

                        Tab::make('Tiêu đề section')
                            ->schema([
                                Section::make('Cấu hình tiêu đề các khối ngoài trang chủ')
                                    ->schema([
                                        Tabs::make('home_section_titles')
                                            ->tabs([
                                                self::sectionTab('industries', 'Lĩnh vực & ứng dụng'),
                                                self::sectionTab('products', 'Sản phẩm'),
                                                self::sectionTab('capabilities', 'Năng lực'),
                                                self::sectionTab('advantages', 'Ưu thế'),
                                                self::sectionTab('customers', 'Đối tác'),
                                                self::sectionTab('news', 'Tin tức'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Năng lực')
                            ->schema([
                                Section::make('Danh sách năng lực')
                                    ->schema([
                                        Repeater::make('capabilities.items')
                                            ->label('Năng lực')
                                            ->schema([
                                                TextInput::make('icon')
                                                    ->label('Icon')
                                                    ->placeholder('fa-industry'),

                                                FileUpload::make('image')
                                                    ->label('Ảnh')
                                                    ->image()
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('settings/home/capabilities'),

                                                ...self::localizedFields([
                                                    fn (string $path) => TextInput::make($path . '.title')
                                                        ->label('Tiêu đề')
                                                        ->required(),

                                                    fn (string $path) => Textarea::make($path . '.description')
                                                        ->label('Mô tả')
                                                        ->rows(2),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm năng lực'),
                                    ]),
                            ]),

                        Tab::make('Chứng nhận')
                            ->schema([
                                Section::make('Chứng nhận')
                                    ->schema([
                                        Repeater::make('certifications.certificates')
                                            ->label('Chứng nhận')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Tên')
                                                    ->required()
                                                    ->maxLength(255),

                                                Textarea::make('description')
                                                    ->label('Mô tả')
                                                    ->rows(2),

                                                FileUpload::make('image')
                                                    ->label('Ảnh')
                                                    ->image()
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('settings/home/certifications'),

                                                FileUpload::make('pdf')
                                                    ->label('File PDF')
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('settings/home/certifications/pdf')
                                                    ->acceptedFileTypes(['application/pdf']),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm chứng nhận'),
                                    ]),

                                Section::make('Sở hữu trí tuệ')
                                    ->schema([
                                        Repeater::make('certifications.items')
                                            ->label('Sở hữu trí tuệ')
                                            ->schema([
                                                TextInput::make('value')
                                                    ->label('Số liệu')
                                                    ->required(),

                                                ...self::localizedFields([
                                                    fn (string $path) => TextInput::make($path . '.label')
                                                        ->label('Nhãn')
                                                        ->required(),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm số liệu'),
                                    ]),
                            ]),

                        Tab::make('Ưu thế')
                            ->schema([
                                Section::make('Danh sách ưu thế')
                                    ->schema([
                                        Repeater::make('advantages.items')
                                            ->label('Ưu thế')
                                            ->schema([
                                                TextInput::make('icon')
                                                    ->label('Icon')
                                                    ->placeholder('fa-headset'),

                                                ...self::localizedFields([
                                                    fn (string $path) => TextInput::make($path . '.title')
                                                        ->label('Tiêu đề')
                                                        ->required(),

                                                    fn (string $path) => Textarea::make($path . '.description')
                                                        ->label('Mô tả')
                                                        ->rows(2),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm ưu thế'),
                                    ]),
                            ]),

                        Tab::make('Đối tác & CTA')
                            ->schema([
                                Section::make('Đối tác')
                                    ->schema([
                                        Repeater::make('customers.items')
                                            ->label('Đối tác')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Tên')
                                                    ->required(),

                                                FileUpload::make('logo')
                                                    ->label('Logo')
                                                    ->image()
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('settings/home/customers'),

                                                TextInput::make('url')
                                                    ->label('Link'),
                                            ])
                                            ->columns(3)
                                            ->addActionLabel('Thêm đối tác'),
                                    ]),

                                Section::make('CTA cuối trang')
                                    ->schema([
                                        Tabs::make('cta_translations')
                                            ->tabs(self::localizedTabs('cta', [
                                                fn (string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn (string $path) => Textarea::make($path . '.description')
                                                    ->label('Mô tả')
                                                    ->rows(3),

                                                fn (string $path) => TextInput::make($path . '.button_label')
                                                    ->label('Nút'),
                                            ])),

                                        TextInput::make('cta.button_url')
                                            ->label('Link CTA'),

                                        FileUpload::make('cta.background_image')
                                            ->label('Ảnh nền CTA')
                                            ->image()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('settings/home/cta'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function sectionTab(string $key, string $label): Tab
    {
        return Tab::make($label)
            ->schema([
                TextInput::make($key . '.limit')
                    ->label('Số lượng')
                    ->numeric(),

                Tabs::make($key . '_translations')
                    ->tabs(self::localizedTabs($key, [
                        fn (string $path) => TextInput::make($path . '.eyebrow')
                            ->label('Nhãn nhỏ'),

                        fn (string $path) => TextInput::make($path . '.title')
                            ->label('Tiêu đề'),

                        fn (string $path) => Textarea::make($path . '.description')
                            ->label('Mô tả')
                            ->rows(2),

                        fn (string $path) => TextInput::make($path . '.button_label')
                            ->label('Nút'),
                    ])),
            ]);
    }

    private static function localizedTabs(string $base, array $fields): array
    {
        return collect(Locale::cases())
            ->map(fn (Locale $locale): Tab => Tab::make($locale->label())
                ->schema(
                    collect($fields)
                        ->map(fn (callable $field) => $field($base . '.' . $locale->value))
                        ->all()
                ))
            ->all();
    }

    private static function localizedFields(array $fields): array
    {
        return collect(Locale::cases())
            ->map(fn (Locale $locale): Section => Section::make($locale->label())
                ->schema(
                    collect($fields)
                        ->map(fn (callable $field) => $field($locale->value))
                        ->all()
                ))
            ->all();
    }
}
