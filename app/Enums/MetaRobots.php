<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum MetaRobots: string
{
    use HasOptions;

    case IndexFollow = 'index,follow';
    case NoindexFollow = 'noindex,follow';
    case IndexNofollow = 'index,nofollow';
    case NoindexNofollow = 'noindex,nofollow';

    public function label(): string
    {
        return match ($this) {
            self::IndexFollow => 'Cho phép lập chỉ mục và theo liên kết (khuyến nghị)',
            self::NoindexFollow => 'Không lập chỉ mục, vẫn theo liên kết',
            self::IndexNofollow => 'Lập chỉ mục, không theo liên kết',
            self::NoindexNofollow => 'Không lập chỉ mục và không theo liên kết',
        };
    }
}
