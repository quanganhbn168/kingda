<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum BranchType: string
{
    use HasOptions;

    case HeadOffice = 'head_office';
    case Branch = 'branch';
    case Showroom = 'showroom';
    case Office = 'office';
    case Factory = 'factory';
    case Warehouse = 'warehouse';

    public function label(): string
    {
        return match ($this) {
            self::HeadOffice => 'Trụ sở chính',
            self::Branch => 'Chi nhánh',
            self::Showroom => 'Showroom',
            self::Office => 'Văn phòng',
            self::Factory => 'Nhà máy',
            self::Warehouse => 'Kho hàng',
        };
    }
}
