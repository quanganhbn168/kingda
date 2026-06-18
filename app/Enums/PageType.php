<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PageType: string
{
    use HasOptions;

    case Page = 'page';
    case Landing = 'landing';
    case Policy = 'policy';

    public function label(): string
    {
        return match ($this) {
            self::Page => 'Trang nội dung',
            self::Landing => 'Landing page',
            self::Policy => 'Trang chính sách',
        };
    }
}
