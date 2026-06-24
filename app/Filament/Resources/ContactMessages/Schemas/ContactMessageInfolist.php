<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Enums\ContactMessageStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Khách hàng')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Họ và tên')
                            ->weight('bold'),
                        TextEntry::make('company')
                            ->label('Công ty')
                            ->placeholder('Không cung cấp'),
                        TextEntry::make('phone')
                            ->label('Số điện thoại')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->url(fn (?string $state): ?string => filled($state) ? 'tel:'.preg_replace('/[^0-9+]/', '', $state) : null)
                            ->placeholder('Không cung cấp'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->url(fn (?string $state): ?string => filled($state) ? 'mailto:'.$state : null)
                            ->placeholder('Không cung cấp'),
                    ]),
                Section::make('Phân loại')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status')
                            ->label('Trạng thái')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => ContactMessageStatus::tryFrom((string) $state)?->label() ?? 'Chưa xác định')
                            ->color(fn (?string $state): string => match ($state) {
                                ContactMessageStatus::New->value => 'danger',
                                ContactMessageStatus::Processing->value => 'warning',
                                ContactMessageStatus::Done->value => 'success',
                                ContactMessageStatus::Spam->value => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('source_label')
                            ->label('Nguồn gửi')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('related_label')
                            ->label('Sản phẩm / nội dung quan tâm')
                            ->placeholder('Không gắn nội dung cụ thể')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Thời gian gửi')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('read_at')
                            ->label('Đã xem lúc')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Chưa xem'),
                    ]),
                Section::make('Nội dung yêu cầu')
                    ->schema([
                        TextEntry::make('subject')
                            ->label('Tiêu đề')
                            ->placeholder('Không có tiêu đề')
                            ->weight('bold'),
                        TextEntry::make('message')
                            ->label('Nội dung')
                            ->prose()
                            ->placeholder('Không có nội dung'),
                    ])
                    ->columnSpanFull(),
                Section::make('Ghi chú nội bộ')
                    ->schema([
                        TextEntry::make('admin_note')
                            ->label('Ghi chú xử lý')
                            ->placeholder('Chưa có ghi chú'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
