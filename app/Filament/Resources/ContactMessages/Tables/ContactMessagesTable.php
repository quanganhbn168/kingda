<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Khách hàng')
                    ->description(fn (ContactMessage $record): ?string => $record->company ?: null)
                    ->icon(fn (ContactMessage $record): ?string => $record->read_at ? null : 'heroicon-s-envelope')
                    ->iconColor('primary')
                    ->weight(fn (ContactMessage $record): string => $record->read_at ? 'medium' : 'bold')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(function (Builder $query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('company', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })),
                TextColumn::make('phone')
                    ->label('Liên hệ')
                    ->description(fn (ContactMessage $record): ?string => $record->email ?: null)
                    ->copyable()
                    ->placeholder('Chưa có thông tin'),
                TextColumn::make('subject')
                    ->label('Nhu cầu')
                    ->description(fn (ContactMessage $record): ?string => $record->related_label)
                    ->limit(45)
                    ->wrap()
                    ->placeholder('Yêu cầu liên hệ')
                    ->searchable(),
                TextColumn::make('source_label')
                    ->label('Nguồn')
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
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
                TextColumn::make('created_at')
                    ->label('Thời gian gửi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(ContactMessageStatus::options()),
                SelectFilter::make('source')
                    ->label('Nguồn gửi')
                    ->options([
                        'contact_page' => 'Trang liên hệ',
                        'product_detail' => 'Chi tiết sản phẩm',
                        'service_detail' => 'Chi tiết dịch vụ',
                    ]),
                Filter::make('unread')
                    ->label('Chưa xem')
                    ->query(fn (Builder $query): Builder => $query->whereNull('read_at')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Xem')
                    ->slideOver()
                    ->modalWidth('3xl')
                    ->before(fn (ContactMessage $record): bool => $record->markAsRead()),
                EditAction::make()
                    ->label('Xử lý')
                    ->slideOver()
                    ->modalWidth('lg')
                    ->modalHeading('Cập nhật xử lý liên hệ'),
            ])
            ->recordAction('view')
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
