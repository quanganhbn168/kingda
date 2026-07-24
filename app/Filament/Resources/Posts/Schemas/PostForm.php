<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\CategoryType;
use App\Enums\Locale;
use App\Enums\MetaRobots;
use App\Models\Category;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 3,
                ])
                    ->columnSpanFull()
                    ->schema([
                        Group::make([
                            Tabs::make('Nội dung đa ngôn ngữ')
                                ->tabs(collect(Locale::cases())
                                    ->map(fn (Locale $locale): Tab => self::translationTab($locale))
                                    ->all())
                                ->persistTab()
                                ->id('post-translation-tabs')
                                ->columnSpanFull(),
                        ])
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 2,
                            ]),
                        Group::make([
                            Section::make('Thiết lập bài viết')
                                ->schema([
                                    Select::make('category_id')
                                        ->label('Danh mục')
                                        ->options(fn (): array => Category::query()
                                            ->where('type', CategoryType::Post->value)
                                            ->with('translation')
                                            ->ordered()
                                            ->get()
                                            ->mapWithKeys(fn (Category $category): array => [
                                                $category->id => $category->translation?->name ?: 'Danh mục #'.$category->id,
                                            ])
                                            ->all())
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Select::make('author_id')
                                        ->label('Tác giả')
                                        ->relationship('author', 'name')
                                        ->searchable()
                                        ->preload(),
                                    Toggle::make('is_featured')
                                        ->label('Nổi bật')
                                        ->default(false)
                                        ->required(),
                                    Toggle::make('is_active')
                                        ->label('Xuất bản')
                                        ->default(true)
                                        ->required(),
                                ]),
                        ])
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 1,
                            ]),
                    ]),
            ]);
    }

    private static function translationTab(Locale $locale): Tab
    {
        return Tab::make($locale->label())
            ->schema([
                Group::make(self::translationFields($locale))
                    ->relationship(self::translationRelationship($locale))
                    ->mutateRelationshipDataBeforeCreateUsing(
                        fn (array $data): array => self::translationData($data, $locale),
                    )
                    ->mutateRelationshipDataBeforeSaveUsing(
                        fn (array $data): array => self::translationData($data, $locale),
                    )
                    ->columnSpanFull(),
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

    private static function translationData(array $data, Locale $locale): array
    {
        return [
            ...$data,
            'locale' => $locale->value,
        ];
    }

    private static function translationFields(Locale $locale): array
    {
        return [
            Hidden::make('locale')
                ->default($locale->value)
                ->required(),
            TextInput::make('title')
                ->label('Tiêu đề')
                ->required(fn (): bool => $locale === Locale::Vietnamese)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state)))
                ->partiallyRenderComponentsAfterStateUpdated(['slug'])
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('slug')
                ->label('Slug')
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('description')
                ->label('Mô tả ngắn')
                ->columnSpanFull(),
            RichEditor::make('content')
                ->label('Nội dung')
                ->floatingToolbars(null)
                ->columnSpanFull(),
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
            Section::make('SEO')
                ->columns(2)
                ->schema([
                    TextInput::make('seo_title')
                        ->label('SEO title')
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label('SEO description')
                        ->rows(3),
                    Select::make('meta_robots')
                        ->label('Hiển thị trên công cụ tìm kiếm')
                        ->options(MetaRobots::options())
                        ->default(MetaRobots::IndexFollow->value)
                        ->required()
                        ->native(false)
                        ->selectablePlaceholder(false)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }
}
