<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum BranchContactType: string
{
    use HasOptions;

    case Phone = 'phone';
    case Hotline = 'hotline';
    case Sales = 'sales';
    case Support = 'support';
    case Email = 'email';
    case Zalo = 'zalo';
    case Messenger = 'messenger';
    case Whatsapp = 'whatsapp';
    case Website = 'website';
    case Map = 'map';

    public function label(): string
    {
        return match ($this) {
            self::Phone => 'Số điện thoại',
            self::Hotline => 'Hotline',
            self::Sales => 'Kinh doanh',
            self::Support => 'Hỗ trợ',
            self::Email => 'Email',
            self::Zalo => 'Zalo',
            self::Messenger => 'Messenger',
            self::Whatsapp => 'WhatsApp',
            self::Website => 'Website',
            self::Map => 'Bản đồ',
        };
    }
}
