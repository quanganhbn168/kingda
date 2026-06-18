<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum MenuLinkType: string
{
    use HasOptions;

    case Custom = 'custom';
    case Page = 'page';
    case Category = 'category';
    case Service = 'service';
    case Product = 'product';
    case Post = 'post';

    public function label(): string
    {
        return match ($this) {
            self::Custom => 'Tùy chỉnh',
            self::Page => 'Trang',
            self::Category => 'Danh mục',
            self::Service => 'Dịch vụ',
            self::Product => 'Sản phẩm',
            self::Post => 'Bài viết',
        };
    }
}
