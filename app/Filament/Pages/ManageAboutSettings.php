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
                                            ])),
                                    ]),
                            ]),

                        Tab::make('Giới thiệu công ty')
                            ->schema([
                                Section::make('Nội dung giới thiệu')
                                    ->description('Phần mô tả chính về công ty.')
                                    ->schema([
                                        Tabs::make('Nội dung đa ngôn ngữ')
                                            ->tabs(self::localizedTabs('intro', [
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
                                            ->label('Ảnh')
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
                            ]),

                        Tab::make('Timeline')
                            ->schema([
                                Section::make('Tiêu đề timeline')
                                    ->description('Tiêu đề cho khu vực mốc phát triển.')
                                    ->schema([
                                        Tabs::make('Timeline title')
                                            ->tabs(self::localizedTabs('timeline', [
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
                                                fn(string $path) => TextInput::make($path . '.title')
                                                    ->label('Tiêu đề'),
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
