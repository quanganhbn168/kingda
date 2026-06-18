<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum MenuTarget: string
{
    use HasOptions;

    case Self = '_self';
    case Blank = '_blank';

    public function label(): string
    {
        return match ($this) {
            self::Self => 'Cùng tab',
            self::Blank => 'Tab mới',
        };
    }
}
