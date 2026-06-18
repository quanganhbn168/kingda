<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum CategoryType: string
{
    use HasOptions;

    case Product = 'product';
    case Service = 'service';
    case Post = 'post';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Sản phẩm',
            self::Service => 'Dịch vụ',
            self::Post => 'Bài viết',
        };
    }
}
