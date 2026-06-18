<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PageTemplate: string
{
    use HasOptions;

    case Default = 'default';
    case Home = 'home';
    case About = 'about';
    case Contact = 'contact';
    case Products = 'products';
    case News = 'news';
    case Industries = 'industries';
    case Landing = 'landing';

    public function label(): string
    {
        return match ($this) {
            self::Default => 'Mặc định',
            self::Home => 'Trang chủ',
            self::About => 'Giới thiệu',
            self::Contact => 'Liên hệ',
            self::Products => 'Sản phẩm',
            self::News => 'Tin tức',
            self::Industries => 'Lĩnh vực',
            self::Landing => 'Landing page',
        };
    }
}
