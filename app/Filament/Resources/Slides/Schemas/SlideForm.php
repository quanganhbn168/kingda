<?php

namespace App\Filament\Resources\Slides\Schemas;

use App\Enums\Locale;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
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
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])
                    ->schema([
                        Section::make('Cấu hình slide')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 4,
                            ])
                            ->schema([
                                TextInput::make('key')
                                    ->label('Mã slide')
                                    ->placeholder('home-hero-01')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Select::make('location')
                                    ->label('Vị trí')
                                    ->options([
                                        'home' => 'Trang chủ',
                                        'about' => 'Giới thiệu',
                                        'product' => 'Sản phẩm',
                                        'service' => 'Dịch vụ',
                                    ])
                                    ->default('home')
                                    ->required()
                                    ->native(false),

                                Select::make('theme')
                                    ->label('Giao diện')
                                    ->options([
                                        'light' => 'Sáng',
                                        'dark' => 'Tối',
                                        'brand' => 'Thương hiệu',
                                    ])
                                    ->native(false),

                                Select::make('text_position')
                                    ->label('Vị trí chữ')
                                    ->options([
                                        'left' => 'Trái',
                                        'center' => 'Giữa',
                                        'right' => 'Phải',
                                    ])
                                    ->default('left')
                                    ->required()
                                    ->native(false),

                                Select::make('media_type')
                                    ->label('Loại media')
                                    ->options([
                                        'image' => 'Ảnh',
                                        'video_upload' => 'Video tải lên',
                                        'video_embed' => 'Video YouTube/Vimeo',
                                    ])
                                    ->default('image')
                                    ->required()
                                    ->live()
                                    ->native(false),

                                TextInput::make('video_embed_url')
                                    ->label('Link video nhúng')
                                    ->placeholder('https://www.youtube.com/watch?v=...')
                                    ->url()
                                    ->maxLength(255)
                                    ->visible(fn (Get $get): bool => $get('media_type') === 'video_embed'),

                                DateTimePicker::make('starts_at')
                                    ->label('Bắt đầu hiển thị')
                                    ->seconds(false),

                                DateTimePicker::make('ends_at')
                                    ->label('Kết thúc hiển thị')
                                    ->seconds(false),

                                TextInput::make('sort_order')
                                    ->label('Thứ tự')
                                    ->required()
                                    ->numeric()
                                    ->default(0),

                                Toggle::make('is_active')
                                    ->label('Kích hoạt')
                                    ->default(true)
                                    ->required(),
                            ])
                            ->columnSpanFull(),

                        Section::make('Nội dung & media')
                            ->columns([
                                'default' => 1,
                                'lg' => 2,
                            ])
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('desktop')
                                    ->label('Ảnh desktop / poster')
                                    ->collection('desktop')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->helperText('Khuyến nghị 1920x760 cho hero desktop.')
                                    ->image(),

                                SpatieMediaLibraryFileUpload::make('mobile')
                                    ->label('Ảnh mobile / poster')
                                    ->collection('mobile')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->image(),

                                SpatieMediaLibraryFileUpload::make('video')
                                    ->label('Video tải lên')
                                    ->collection('video')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                                    ->visible(fn (Get $get): bool => $get('media_type') === 'video_upload')
                                    ->helperText('Video nền sẽ autoplay muted loop; nên dùng file ngắn, nhẹ.'),

                                Tabs::make('Nội dung theo ngôn ngữ')
                                    ->tabs(collect(Locale::cases())
                                        ->map(fn (Locale $locale): Tab => self::translationTab($locale))
                                        ->all())
                                    ->persistTab()
                                    ->id('slide-translation-tabs')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function translationTab(Locale $locale): Tab
    {
        return Tab::make($locale->label())
            ->schema([
                Repeater::make('translations_' . $locale->value)
                    ->label($locale->label())
                    ->relationship(
                        'translations',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('locale', $locale->value),
                    )
                    ->schema([
                        Hidden::make('locale')
                            ->default($locale->value)
                            ->required(),
                        TextInput::make('locale_label')
                            ->label('Ngôn ngữ')
                            ->disabled()
                            ->dehydrated(false)
                            ->default($locale->label()),
                        TextInput::make('eyebrow')
                            ->label('Nhãn nhỏ')
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label('Tiêu đề')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('primary_button_label')
                            ->label('Nút chính')
                            ->maxLength(255),
                        TextInput::make('primary_button_url')
                            ->label('Link nút chính')
                            ->maxLength(255),
                        TextInput::make('secondary_button_label')
                            ->label('Nút phụ')
                            ->maxLength(255),
                        TextInput::make('secondary_button_url')
                            ->label('Link nút phụ')
                            ->maxLength(255),
                        TextInput::make('image_alt')
                            ->label('Alt ảnh')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->label('Xuất bản')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->maxItems(1)
                    ->minItems(1)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->cloneable(false)
                    ->itemHeaders(false)
                    ->hiddenLabel()
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => [
                        ...$data,
                        'locale' => $locale->value,
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => [
                        ...$data,
                        'locale' => $locale->value,
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
