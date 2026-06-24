<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Enums\ContactMessageStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Xử lý liên hệ')
                    ->description('Cập nhật tiến độ và ghi chú nội bộ. Thông tin khách gửi được giữ nguyên.')
                    ->schema([
                        Select::make('status')
                            ->label('Trạng thái xử lý')
                            ->options(ContactMessageStatus::options())
                            ->required()
                            ->default(ContactMessageStatus::New->value),
                        Textarea::make('admin_note')
                            ->label('Ghi chú nội bộ')
                            ->placeholder('Kết quả trao đổi, người phụ trách, lịch hẹn...')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
