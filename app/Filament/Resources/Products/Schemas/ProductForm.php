<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\Locale;
use App\Models\Product;
use App\Services\Admin\ProductCategoryOptions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('product_form_tabs')
                    ->tabs([
                        Tab::make('Thông tin chung')
                            ->schema(self::generalFields()),
                        ...collect(Locale::cases())
                            ->map(fn (Locale $locale): Tab => self::translationTab($locale))
                            ->all(),
                    ])
                    ->persistTab()
                    ->id('product-form-tabs')
                    ->columnSpanFull(),
            ]);
    }

    private static function generalFields(): array
    {
        return [
            Section::make('Thông tin sản phẩm')
                ->columns(2)
                ->schema([
                    Select::make('category_id')
                        ->label('Danh mục')
                        ->options(fn (): array => app(ProductCategoryOptions::class)->leaves())
                        ->helperText('Chỉ hiển thị danh mục cấp lá, không còn danh mục con.')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('sku')
                        ->label('SKU')
                        ->maxLength(255),
                    TextInput::make('sort_order')
                        ->label('Thứ tự')
                        ->required()
                        ->numeric()
                        ->default(0),
                    Toggle::make('is_home')
                        ->label('Trang chủ')
                        ->required(),
                    Toggle::make('is_featured')
                        ->label('Nổi bật')
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Kích hoạt')
                        ->required(),
                ]),
        ];
    }

    private static function translationTab(Locale $locale): Tab
    {
        return Tab::make($locale->label())
            ->schema([
                Group::make(self::translationFields($locale))
                    ->relationship(self::translationRelationship($locale))
                    ->mutateRelationshipDataBeforeCreateUsing(
                        fn (array $data, Get $get): array => self::translationData($data, $get, $locale),
                    )
                    ->mutateRelationshipDataBeforeSaveUsing(
                        fn (array $data, Get $get): array => self::translationData($data, $get, $locale),
                    )
                    ->columnSpanFull(),

                Section::make('Nội dung chi tiết')
                    ->schema([
                        RichEditor::make(self::translationContentField($locale))
                            ->label('Nội dung')
                            ->afterStateHydrated(
                                fn (RichEditor $component, ?Product $record): mixed => $component->state(
                                    $record?->translationFor($locale->value)->value('content'),
                                ),
                            )
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function translationRelationship(Locale $locale): string
    {
        return match ($locale) {
            Locale::Vietnamese => 'translationVi',
            Locale::English => 'translationEn',
            Locale::Chinese => 'translationZh',
        };
    }

    private static function translationData(array $data, Get $get, Locale $locale): array
    {
        return [
            ...$data,
            'locale' => $locale->value,
            'content' => $get(self::translationContentField($locale)),
        ];
    }

    private static function translationContentField(Locale $locale): string
    {
        return 'content_' . $locale->value;
    }

    private static function translationFields(Locale $locale): array
    {
        return [
            Section::make('Thông tin bản dịch')
                ->columns(2)
                ->schema([
                    Hidden::make('locale')
                        ->default($locale->value)
                        ->required(),
                    TextInput::make('locale_label')
                        ->label('Ngôn ngữ')
                        ->disabled()
                        ->dehydrated(false)
                        ->default($locale->label())
                        ->afterStateHydrated(fn (TextInput $component): mixed => $component->state($locale->label())),
                    TextInput::make('name')
                        ->label('Tên sản phẩm')
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
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Mô tả ngắn')
                        ->columnSpanFull(),
                    KeyValue::make('specifications')
                        ->label('Thông số')
                        ->columnSpanFull(),
                    Toggle::make('is_published')
                        ->label('Xuất bản')
                        ->default(true),
                ]),
            Section::make('Nội dung trang chi tiết')
                ->description('Các trường này được hiển thị trực tiếp trên trang chi tiết sản phẩm.')
                ->columns(2)
                ->schema([
                    TagsInput::make('blocks.applications')
                        ->label('Ứng dụng')
                        ->placeholder('Nhập một ứng dụng rồi Enter')
                        ->columnSpanFull(),
                    TagsInput::make('blocks.substrates')
                        ->label('Nền vật liệu')
                        ->placeholder('PET, PP, PE...')
                        ->columnSpanFull(),
                    TagsInput::make('blocks.features')
                        ->label('Đặc tính kỹ thuật')
                        ->placeholder('Nhập một đặc tính rồi Enter')
                        ->columnSpanFull(),
                    TagsInput::make('blocks.consulting_inputs')
                        ->label('Thông tin khách cần chuẩn bị')
                        ->placeholder('Nền vật liệu, công nghệ, yêu cầu thành phẩm...')
                        ->columnSpanFull(),
                    TagsInput::make('blocks.storage_notes')
                        ->label('Lưu ý bảo quản')
                        ->placeholder('Nhập một lưu ý rồi Enter')
                        ->columnSpanFull(),
                    Repeater::make('blocks.faq')
                        ->label('FAQ sản phẩm')
                        ->schema([
                            TextInput::make('question')
                                ->label('Câu hỏi')
                                ->required(),
                            Textarea::make('answer')
                                ->label('Trả lời')
                                ->rows(3)
                                ->required(),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->reorderable()
                        ->columnSpanFull(),
                ]),
            Section::make('Đa phương tiện')
                ->columns(2)
                ->schema([
                    SpatieMediaLibraryFileUpload::make('thumbnail')
                        ->label('Ảnh đại diện')
                        ->collection('thumbnail')
                        ->disk('public')
                        ->visibility('public')
                        ->image(),
                    SpatieMediaLibraryFileUpload::make('hero')
                        ->label('Ảnh hero')
                        ->collection('hero')
                        ->disk('public')
                        ->visibility('public')
                        ->image(),
                    SpatieMediaLibraryFileUpload::make('gallery')
                        ->label('Ảnh chi tiết')
                        ->helperText('Có thể tải nhiều ảnh và kéo thả để sắp xếp thứ tự hiển thị.')
                        ->collection('gallery')
                        ->disk('public')
                        ->visibility('public')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->columnSpanFull(),
                ]),
            Section::make('SEO')
                ->columns(2)
                ->schema([
                    TextInput::make('seo_title')
                        ->label('SEO title')
                        ->maxLength(255),
                    TextInput::make('canonical_url')
                        ->label('Canonical URL')
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label('SEO description'),
                    TextInput::make('meta_robots')
                        ->label('Meta robots')
                        ->default('index,follow')
                        ->maxLength(255),
                    TextInput::make('og_title')
                        ->label('OG title')
                        ->maxLength(255),
                    Textarea::make('og_description')
                        ->label('OG description'),
                    SpatieMediaLibraryFileUpload::make('og_image')
                        ->label('OG image')
                        ->collection('og_image')
                        ->disk('public')
                        ->visibility('public')
                        ->image(),
                ]),
        ];
    }
}
