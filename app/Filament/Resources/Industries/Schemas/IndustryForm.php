<?php

namespace App\Filament\Resources\Industries\Schemas;

use App\Enums\Locale;
use App\Models\Industry;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class IndustryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin lĩnh vực')
                    ->columns(2)
                    ->schema([
                        TextInput::make('icon')
                            ->label('Icon Font Awesome')
                            ->placeholder('fa-mobile-screen-button')
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label('URL tùy chọn')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->label('Thứ tự')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label('Hiện trang chủ')
                                    ->default(true)
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label('Kích hoạt')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Hình ảnh')
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
                    ]),

                Tabs::make('Nội dung đa ngôn ngữ')
                    ->tabs(collect(Locale::cases())
                        ->map(fn (Locale $locale): Tab => self::translationTab($locale))
                        ->all())
                    ->persistTab()
                    ->id('industry-translation-tabs')
                    ->columnSpanFull(),
            ]);
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
                                fn (RichEditor $component, ?Industry $record): mixed => $component->state(
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
                        ->default($locale->label()),
                    TextInput::make('title')
                        ->label('Tên lĩnh vực')
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
                        ->required()
                        ->maxLength(255),
                    Toggle::make('is_published')
                        ->label('Xuất bản')
                        ->default(true),
                    Textarea::make('description')
                        ->label('Mô tả')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
