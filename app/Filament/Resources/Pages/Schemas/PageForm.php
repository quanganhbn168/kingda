<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\Locale;
use App\Enums\PageTemplate;
use App\Enums\PageType;
use App\Models\Page;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Thông tin trang')
                            ->description('Trang đã có route và Blade trong code. Khu vực này chỉ hiển thị cấu hình kỹ thuật để đối chiếu.')
                            ->schema([
                                TextInput::make('key')
                                    ->label('Mã trang')
                                    ->placeholder('home, about, contact, privacy-policy')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->maxLength(100)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Mã này được code dùng để map route/template, không cần sửa trong admin.'),

                                Select::make('type')
                                    ->label('Loại trang')
                                    ->options(PageType::options())
                                    ->default(PageType::Page->value)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->native(false),

                                Select::make('template')
                                    ->label('Giao diện Blade')
                                    ->options(PageTemplate::options())
                                    ->default(PageTemplate::Default->value)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->native(false)
                                    ->helperText('Map tới resources/views/frontend/pages/templates/{template}.blade.php'),

                                Grid::make([
                                    'default' => 1,
                                    'md' => 3,
                                ])
                                    ->schema([
                                        Toggle::make('is_home')
                                            ->label('Trang chủ')
                                            ->helperText('Chỉ nên có 1 trang chủ.'),

                                        Toggle::make('is_system')
                                            ->label('Trang hệ thống')
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Trang lõi, hạn chế xóa nhầm.'),

                                        Toggle::make('is_active')
                                            ->label('Đang hoạt động')
                                            ->default(true),
                                    ]),

                                TextInput::make('sort_order')
                                    ->label('Thứ tự')
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Nội dung theo ngôn ngữ')
                            ->description('Quản lý title, headline, SEO, banner/OG và trạng thái xuất bản theo từng ngôn ngữ.')
                            ->schema([
                                Repeater::make('translations')
                                    ->label('Bản dịch')
                                    ->relationship()
                                    ->schema([
                                        Hidden::make('locale')
                                            ->required(),

                                        TextInput::make('locale_label')
                                            ->label('Ngôn ngữ')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(fn (Get $get): ?string => Locale::labelFor($get('locale')))
                                            ->afterStateHydrated(fn (TextInput $component, Get $get): mixed => $component->state(Locale::labelFor($get('locale')))),

                                        Tabs::make('translation_tabs')
                                            ->tabs([
                                                Tab::make('Nội dung chính')
                                                    ->schema([
                                                        Grid::make([
                                                            'default' => 1,
                                                            'md' => 2,
                                                        ])
                                                            ->schema([
                                                                TextInput::make('title')
                                                                    ->label('Tiêu đề trang')
                                                                    ->required()
                                                                    ->live(onBlur: true)
                                                                    ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                                                        if (filled($get('slug')) || blank($state)) {
                                                                            return;
                                                                        }

                                                                        $set('slug', Str::slug($state));
                                                                    })
                                                                    ->maxLength(255),

                                                                TextInput::make('slug')
                                                                    ->label('Slug')
                                                                    ->placeholder('Tự sinh khi lưu nếu để trống')
                                                                    ->maxLength(255)
                                                                    ->disabled(fn (?Page $record): bool => (bool) $record?->is_system)
                                                                    ->dehydrated(fn (?Page $record): bool => ! $record?->is_system)
                                                                    ->helperText(fn (?Page $record): string => $record?->is_system
                                                                        ? 'Trang hệ thống dùng route cố định theo mã trang, không cần sửa slug.'
                                                                        : 'Có thể để trống, hệ thống tự sinh từ tiêu đề khi lưu. Trang chủ luôn để trống.'),
                                                            ]),

                                                        TextInput::make('headline')
                                                            ->label('Headline')
                                                            ->maxLength(255)
                                                            ->helperText('Tiêu đề lớn ngoài giao diện. Nếu trống thì dùng title.'),

                                                        Textarea::make('subheadline')
                                                            ->label('Subheadline')
                                                            ->rows(3),

                                                        Textarea::make('excerpt')
                                                            ->label('Mô tả ngắn')
                                                            ->rows(3),

                                                        RichEditor::make('content')
                                                            ->label('Nội dung dài')
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->columns(1),

                                                Tab::make('SEO')
                                                    ->schema([
                                                        TextInput::make('seo_title')
                                                            ->label('SEO title')
                                                            ->maxLength(255),

                                                        Textarea::make('seo_description')
                                                            ->label('SEO description')
                                                            ->rows(3),

                                                        Select::make('meta_robots')
                                                            ->label('Meta robots')
                                                            ->options([
                                                                'index,follow' => 'index,follow',
                                                                'noindex,follow' => 'noindex,follow',
                                                                'index,nofollow' => 'index,nofollow',
                                                                'noindex,nofollow' => 'noindex,nofollow',
                                                            ])
                                                            ->default('index,follow')
                                                            ->native(false),

                                                        TextInput::make('canonical_url')
                                                            ->label('Canonical URL')
                                                            ->url()
                                                            ->maxLength(255),

                                                        TextInput::make('og_title')
                                                            ->label('OG title')
                                                            ->maxLength(255),

                                                        Textarea::make('og_description')
                                                            ->label('OG description')
                                                            ->rows(3),
                                                    ])
                                                    ->columns([
                                                        'default' => 1,
                                                        'md' => 2,
                                                    ]),

                                                Tab::make('Ảnh')
                                                    ->schema([
                                                        SpatieMediaLibraryFileUpload::make('hero')
                                                            ->label('Ảnh hero')
                                                            ->collection('hero')
                                                            ->disk('public')
                                                            ->visibility('public')
                                                            ->image(),

                                                        SpatieMediaLibraryFileUpload::make('thumbnail')
                                                            ->label('Ảnh thumbnail')
                                                            ->collection('thumbnail')
                                                            ->disk('public')
                                                            ->visibility('public')
                                                            ->image(),

                                                        SpatieMediaLibraryFileUpload::make('og_image')
                                                            ->label('Ảnh chia sẻ mạng xã hội')
                                                            ->collection('og_image')
                                                            ->disk('public')
                                                            ->visibility('public')
                                                            ->image(),
                                                    ])
                                                    ->columns([
                                                        'default' => 1,
                                                        'md' => 3,
                                                    ]),

                                                Tab::make('Xuất bản')
                                                    ->schema([
                                                        Toggle::make('is_published')
                                                            ->label('Xuất bản')
                                                            ->default(false),

                                                        DateTimePicker::make('published_at')
                                                            ->label('Ngày xuất bản')
                                                            ->seconds(false),
                                                    ])
                                                    ->columns([
                                                        'default' => 1,
                                                        'md' => 2,
                                                    ]),
                                            ])
                                            ->persistTab()
                                            ->id('page-translation-tabs')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->itemLabel(fn (array $state): ?string => collect([
                                        Locale::labelFor($state['locale'] ?? null),
                                        $state['title'] ?? null,
                                    ])->filter()->join(' - ') ?: null)
                                    ->collapsed()
                                    ->cloneable(false)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->default(Locale::translationRepeaterDefaults())
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
