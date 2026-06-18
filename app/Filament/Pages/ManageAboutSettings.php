<?php

namespace App\Filament\Pages;

use App\Enums\Locale;
use App\Settings\AboutSettings;
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

class ManageAboutSettings extends SettingsPage
{
    protected static string $settings = AboutSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';

    protected static ?int $navigationSort = 13;

    protected static ?string $title = 'Cài đặt giới thiệu';

    protected static ?string $navigationLabel = 'Giới thiệu';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Cài đặt trang giới thiệu')
                    ->tabs([
                        Tab::make('Hero')
                            ->schema([
                                Section::make('Nội dung hero đầu trang')
                                    ->description('Phần tiêu đề lớn ở đầu trang giới thiệu.')
                                    ->schema([
                                        Tabs::make('Nội dung đa ngôn ngữ')
                                            ->tabs(self::localizedTabs('hero', [
                                                fn(string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn(string $path) => TextInput::make($path . '.subtitle')
                                                    ->label('Subline'),

                                                fn(string $path) => Textarea::make($path . '.description')
                                                    ->label('Mô tả')
                                                    ->rows(3),

                                                fn(string $path) => TextInput::make($path . '.primary_button_label')
                                                    ->label('Nút chính'),

                                                fn(string $path) => TextInput::make($path . '.secondary_button_label')
                                                    ->label('Nút phụ'),

                                                fn(string $path) => TextInput::make($path . '.floating_one_value')
                                                    ->label('Số nổi 1'),

                                                fn(string $path) => TextInput::make($path . '.floating_one_label')
                                                    ->label('Nhãn nổi 1'),

                                                fn(string $path) => TextInput::make($path . '.floating_two_value')
                                                    ->label('Số nổi 2'),

                                                fn(string $path) => TextInput::make($path . '.floating_two_label')
                                                    ->label('Nhãn nổi 2'),
                                            ])),
                                    ]),

                                Section::make('Media hero')
                                    ->schema([
                                        FileUpload::make('hero.image')
                                            ->label('Ảnh hero')
                                            ->image()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('settings/about/hero'),
                                    ]),
                            ]),

                        Tab::make('Giới thiệu công ty')
                            ->schema([
                                Section::make('Nội dung giới thiệu')
                                    ->description('Phần mô tả chính về công ty.')
                                    ->schema([
                                        Tabs::make('Nội dung đa ngôn ngữ')
                                            ->tabs(self::localizedTabs('intro', [
                                                fn(string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn(string $path) => Textarea::make($path . '.content')
                                                    ->label('Nội dung')
                                                    ->rows(8),
                                            ])),
                                    ]),

                                Section::make('Media giới thiệu')
                                    ->schema([
                                        FileUpload::make('intro.image')
                                            ->label('Ảnh chính')
                                            ->image()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('settings/about'),

                                        FileUpload::make('intro.small_image_one')
                                            ->label('Ảnh phụ 1')
                                            ->image()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('settings/about'),

                                        FileUpload::make('intro.small_image_two')
                                            ->label('Ảnh phụ 2')
                                            ->image()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('settings/about'),

                                        FileUpload::make('intro.video_upload')
                                            ->label('Video tải lên')
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('settings/about/videos')
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

                                Section::make('Chỉ số giới thiệu')
                                    ->schema([
                                        Repeater::make('intro.stats')
                                            ->label('Chỉ số')
                                            ->schema([
                                                TextInput::make('value')
                                                    ->label('Số liệu')
                                                    ->required(),

                                                ...self::localizedFields([
                                                    fn(string $path) => TextInput::make($path . '.label')
                                                        ->label('Nhãn')
                                                        ->required(),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm chỉ số'),
                                    ]),
                            ]),

                        Tab::make('Quan điểm phát triển')
                            ->schema([
                                Section::make('Tiêu đề khu vực')
                                    ->schema([
                                        Tabs::make('Development title')
                                            ->tabs(self::localizedTabs('development', [
                                                fn(string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn(string $path) => Textarea::make($path . '.description')
                                                    ->label('Mô tả')
                                                    ->rows(2),
                                            ])),
                                    ]),

                                Section::make('Các quan điểm')
                                    ->schema([
                                        Repeater::make('development.items')
                                            ->label('Quan điểm')
                                            ->schema([
                                                TextInput::make('number')
                                                    ->label('Số thứ tự')
                                                    ->placeholder('01')
                                                    ->required(),

                                                ...self::localizedFields([
                                                    fn(string $path) => TextInput::make($path . '.title')
                                                        ->label('Tiêu đề')
                                                        ->required(),

                                                    fn(string $path) => Textarea::make($path . '.description')
                                                        ->label('Mô tả')
                                                        ->rows(2),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm quan điểm'),
                                    ]),
                            ]),

                        Tab::make('Timeline')
                            ->schema([
                                Section::make('Tiêu đề timeline')
                                    ->description('Tiêu đề cho khu vực mốc phát triển.')
                                    ->schema([
                                        Tabs::make('Timeline title')
                                            ->tabs(self::localizedTabs('timeline', [
                                                fn(string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),
                                            ])),
                                    ]),

                                Section::make('Mốc phát triển')
                                    ->schema([
                                        Repeater::make('timeline.items')
                                            ->label('Mốc phát triển')
                                            ->schema([
                                                TextInput::make('year')
                                                    ->label('Năm')
                                                    ->required(),

                                                TextInput::make('icon')
                                                    ->label('Icon')
                                                    ->maxLength(20)
                                                    ->placeholder('fa-flag'),

                                                ...self::localizedFields([
                                                    fn(string $path) => TextInput::make($path . '.title')
                                                        ->label('Tiêu đề'),

                                                    fn(string $path) => Textarea::make($path . '.description')
                                                        ->label('Mô tả')
                                                        ->rows(2),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm mốc phát triển'),
                                    ]),
                            ]),

                        Tab::make('Văn hóa doanh nghiệp')
                            ->schema([
                                Section::make('Tiêu đề văn hóa')
                                    ->description('Tiêu đề cho khu vực văn hóa doanh nghiệp.')
                                    ->schema([
                                        Tabs::make('Culture title')
                                            ->tabs(self::localizedTabs('culture', [
                                                fn(string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn(string $path) => Textarea::make($path . '.description')
                                                    ->label('Mô tả')
                                                    ->rows(2),

                                                fn(string $path) => TextInput::make($path . '.highlight_title')
                                                    ->label('Tiêu đề nổi bật'),

                                                fn(string $path) => Textarea::make($path . '.highlight_description')
                                                    ->label('Mô tả nổi bật')
                                                    ->rows(2),
                                            ])),
                                    ]),

                                Section::make('Giá trị văn hóa')
                                    ->schema([
                                        Repeater::make('culture.items')
                                            ->label('Giá trị')
                                            ->schema([
                                                TextInput::make('icon')
                                                    ->label('Icon')
                                                    ->maxLength(20)
                                                    ->placeholder('fa-heart'),

                                                ...self::localizedFields([
                                                    fn(string $path) => TextInput::make($path . '.title')
                                                        ->label('Tiêu đề')
                                                        ->required(),

                                                    fn(string $path) => Textarea::make($path . '.description')
                                                        ->label('Mô tả')
                                                        ->rows(2),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm giá trị'),
                                    ]),
                            ]),

                        Tab::make('Năng lực')
                            ->schema([
                                Section::make('Tiêu đề năng lực')
                                    ->schema([
                                        Tabs::make('Capability title')
                                            ->tabs(self::localizedTabs('capabilities', [
                                                fn(string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn(string $path) => Textarea::make($path . '.description')
                                                    ->label('Mô tả')
                                                    ->rows(2),
                                            ])),
                                    ]),

                                Section::make('Danh sách năng lực')
                                    ->schema([
                                        Repeater::make('capabilities.items')
                                            ->label('Năng lực')
                                            ->schema([
                                                FileUpload::make('image')
                                                    ->label('Ảnh')
                                                    ->image()
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('settings/about/capabilities'),

                                                ...self::localizedFields([
                                                    fn(string $path) => TextInput::make($path . '.title')
                                                        ->label('Tiêu đề')
                                                        ->required(),

                                                    fn(string $path) => Textarea::make($path . '.description')
                                                        ->label('Mô tả')
                                                        ->rows(2),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm năng lực'),
                                    ]),
                            ]),

                        Tab::make('Chứng nhận & đối tác')
                            ->schema([
                                Section::make('Chứng nhận & sở hữu trí tuệ')
                                    ->schema([
                                        Tabs::make('Certificates title')
                                            ->tabs(self::localizedTabs('certificates', [
                                                fn(string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn(string $path) => Textarea::make($path . '.description')
                                                    ->label('Mô tả')
                                                    ->rows(2),
                                            ])),

                                        Repeater::make('certificates.items')
                                            ->label('Chứng nhận')
                                            ->schema([
                                                TextInput::make('badge')
                                                    ->label('Nhãn')
                                                    ->placeholder('ISO'),

                                                ...self::localizedFields([
                                                    fn(string $path) => TextInput::make($path . '.title')
                                                        ->label('Tiêu đề')
                                                        ->required(),

                                                    fn(string $path) => Textarea::make($path . '.description')
                                                        ->label('Mô tả')
                                                        ->rows(2),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm chứng nhận'),

                                        Repeater::make('intellectual_property.items')
                                            ->label('Sở hữu trí tuệ')
                                            ->schema([
                                                TextInput::make('value')
                                                    ->label('Số liệu')
                                                    ->required(),

                                                ...self::localizedFields([
                                                    fn(string $path) => TextInput::make($path . '.label')
                                                        ->label('Nhãn')
                                                        ->required(),
                                                ]),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm số liệu'),
                                    ]),

                                Section::make('Khách hàng & đối tác')
                                    ->schema([
                                        Tabs::make('Customers title')
                                            ->tabs(self::localizedTabs('customers', [
                                                fn(string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn(string $path) => Textarea::make($path . '.description')
                                                    ->label('Mô tả')
                                                    ->rows(2),
                                            ])),

                                        Repeater::make('customers.items')
                                            ->label('Đối tác')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Tên')
                                                    ->required(),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm đối tác'),
                                    ]),
                            ]),

                        Tab::make('CTA')
                            ->schema([
                                Section::make('CTA cuối trang')
                                    ->schema([
                                        Tabs::make('Contact title')
                                            ->tabs(self::localizedTabs('contact', [
                                                fn(string $path) => TextInput::make($path . '.eyebrow')
                                                    ->label('Nhãn nhỏ'),

                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),

                                                fn(string $path) => Textarea::make($path . '.description')
                                                    ->label('Mô tả')
                                                    ->rows(2),

                                                fn(string $path) => TextInput::make($path . '.button_label')
                                                    ->label('Nút'),
                                            ])),

                                        TextInput::make('contact.button_url')
                                            ->label('Link nút')
                                            ->placeholder('/lien-he'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function localizedTabs(string $base, array $fields): array
    {
        return collect(Locale::cases())
            ->map(fn(Locale $locale): Tab => Tab::make($locale->label())
                ->schema(
                    collect($fields)
                        ->map(fn(callable $field) => $field($base . '.' . $locale->value, $locale))
                        ->all()
                ))
            ->all();
    }

    private static function localizedFields(array $fields): array
    {
        return collect(Locale::cases())
            ->map(fn(Locale $locale): Section => Section::make($locale->label())
                ->schema(
                    collect($fields)
                        ->map(fn(callable $field) => $field($locale->value, $locale))
                        ->all()
                ))
            ->all();
    }
}
