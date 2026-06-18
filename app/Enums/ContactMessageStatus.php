<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ContactMessageStatus: string
{
    use HasOptions;

    case New = 'new';
    case Processing = 'processing';
    case Done = 'done';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Mới',
            self::Processing => 'Đang xử lý',
            self::Done => 'Hoàn tất',
            self::Spam => 'Spam',
        };
    }
}
