<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\CategoryType;
use App\Enums\Locale;
use App\Models\Category;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
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

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin bài viết')
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Danh mục')
                            ->options(fn (): array => Category::query()
                                ->where('type', CategoryType::Post->value)
                                ->with('translation')
                                ->ordered()
                                ->get()
                                ->mapWithKeys(fn (Category $category): array => [
                                    $category->id => $category->translation?->name ?: 'Danh mục #' . $category->id,
                                ])
                                ->all())
                            ->searchable()
                            ->preload(),
                        Select::make('author_id')
                            ->label('Tác giả')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('sort_order')
                            ->label('Thứ tự')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_featured')
                            ->label('Nổi bật')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Kích hoạt')
                            ->required(),
                    ]),
                Tabs::make('Nội dung đa ngôn ngữ')
                    ->tabs(collect(Locale::cases())
                        ->map(fn (Locale $locale): Tab => self::translationTab($locale))
                        ->all())
                    ->persistTab()
                    ->id('post-translation-tabs')
                    ->columnSpanFull(),
            ]);
    }

    private static function translationTab(Locale $locale): Tab
    {
        return Tab::make($locale->label())
            ->schema([
                \Filament\Forms\Components\Repeater::make('translations_' . $locale->value)
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
                    TextInput::make('title')
                        ->label('Tiêu đề')
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
                        ->helperText('Có thể sửa tay cho ngắn gọn, không cần bê nguyên tiêu đề.')
                        ->maxLength(255),
                    DateTimePicker::make('published_at')
                        ->label('Ngày xuất bản'),
                    Textarea::make('description')
                        ->label('Mô tả ngắn')
                        ->columnSpanFull(),
                    RichEditor::make('content')
                        ->label('Nội dung')
                        ->floatingToolbars(null)
                        ->columnSpanFull(),
                    Toggle::make('is_published')
                        ->label('Xuất bản')
                        ->default(true),
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
                        ->label('Thư viện ảnh')
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
