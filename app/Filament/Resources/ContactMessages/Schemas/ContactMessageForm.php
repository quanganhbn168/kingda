<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Enums\ContactMessageStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('company'),
                TextInput::make('subject'),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('source'),
                TextInput::make('related_type'),
                TextInput::make('related_id')
                    ->numeric(),
                Select::make('status')
                    ->label('Trạng thái')
                    ->options(ContactMessageStatus::options())
                    ->required()
                    ->default(ContactMessageStatus::New->value),
                Textarea::make('admin_note')
                    ->columnSpanFull(),
                DateTimePicker::make('read_at'),
            ]);
    }
}
