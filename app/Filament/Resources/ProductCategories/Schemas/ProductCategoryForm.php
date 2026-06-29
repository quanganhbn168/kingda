<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Enums\CategoryType;
use App\Enums\Locale;
use App\Models\Category;
use App\Services\Admin\ProductCategoryOptions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin danh mục')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('type')
                            ->default(CategoryType::Product->value),

                        Select::make('parent_id')
                            ->label('Danh mục cha')
                            ->options(fn (?Category $record): array => app(ProductCategoryOptions::class)->tree($record))
                            ->placeholder('Danh mục gốc (không có danh mục cha)')
                            ->default(null)
                            ->dehydrateStateUsing(fn (mixed $state): ?int => filled($state) && ((int) $state > 0) ? (int) $state : null)
                            ->helperText('Chỉ chọn được danh mục cha không chứa sản phẩm; cây không giới hạn số tầng.')
                            ->searchable()
                            ->preload(),

                        Toggle::make('is_active')
                            ->label('Kích hoạt')
                            ->default(true)
                            ->required(),
                    ]),

                Tabs::make('Nội dung đa ngôn ngữ')
                    ->tabs(collect(Locale::cases())
                        ->map(fn (Locale $locale): Tab => self::translationTab($locale))
                        ->all())
                    ->persistTab()
                    ->id('category-translation-tabs')
                    ->columnSpanFull(),
            ]);
    }

    private static function translationTab(Locale $locale): Tab
    {
        return Tab::make($locale->label())
            ->schema([
                Repeater::make('translations_'.$locale->value)
                    ->label($locale->label())
                    ->relationship(
                        'translations',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('locale', $locale->value),
                    )
                    ->schema(self::translationFields($locale))
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

    private static function translationFields(Locale $locale): array
    {
        return [
            Section::make('Nội dung')
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
                        ->label('Tên danh mục')
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

                    Toggle::make('is_published')
                        ->label('Xuất bản')
                        ->default(true),

                    Textarea::make('description')
                        ->label('Mô tả ngắn')
                        ->rows(3)
                        ->columnSpanFull(),

                    RichEditor::make('content')
                        ->label('Nội dung chi tiết')
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

                    SpatieMediaLibraryFileUpload::make('og_image')
                        ->label('OG image')
                        ->collection('og_image')
                        ->disk('public')
                        ->visibility('public')
                        ->image(),
                ]),

            Section::make('SEO')
                ->description('Canonical URL được tạo tự động theo URL công khai của danh mục.')
                ->columns(2)
                ->schema([
                    TextInput::make('seo_title')
                        ->label('SEO title')
                        ->maxLength(255),

                    Textarea::make('seo_description')
                        ->label('SEO description'),

                    Select::make('meta_robots')
                        ->label('Hiển thị trên công cụ tìm kiếm')
                        ->options([
                            'index,follow' => 'Cho phép lập chỉ mục và theo liên kết (Khuyến nghị)',
                            'noindex,follow' => 'Không lập chỉ mục, vẫn theo liên kết',
                            'index,nofollow' => 'Lập chỉ mục, không theo liên kết',
                            'noindex,nofollow' => 'Không lập chỉ mục và không theo liên kết',
                        ])
                        ->default('index,follow')
                        ->required()
                        ->native(false)
                        ->selectablePlaceholder(false)
                        ->helperText('Thông thường nên giữ lựa chọn khuyến nghị.'),

                    TextInput::make('og_title')
                        ->label('OG title')
                        ->maxLength(255),

                    Textarea::make('og_description')
                        ->label('OG description'),
                ]),
        ];
    }
}
