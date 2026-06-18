<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum MenuLocation: string
{
    use HasOptions;

    case Header = 'header';
    case Footer = 'footer';
    case Mobile = 'mobile';
    case Sidebar = 'sidebar';

    public function label(): string
    {
        return match ($this) {
            self::Header => 'Header',
            self::Footer => 'Footer',
            self::Mobile => 'Mobile',
            self::Sidebar => 'Sidebar',
        };
    }
}
