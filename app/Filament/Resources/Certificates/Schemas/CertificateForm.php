<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chứng nhận')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->label('Thứ tự')
                            ->numeric()
                            ->required()
                            ->default(0),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Kích hoạt')
                            ->default(true)
                            ->required(),
                    ]),

                Section::make('Tệp đính kèm')
                    ->columns(2)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('Ảnh')
                            ->collection('image')
                            ->disk('public')
                            ->visibility('public')
                            ->image(),
                        SpatieMediaLibraryFileUpload::make('pdf')
                            ->label('File PDF')
                            ->collection('pdf')
                            ->disk('public')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf']),
                    ]),
            ]);
    }
}
