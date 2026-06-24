<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('site.footer_menu_1_id', null);
        $this->migrator->add('site.footer_menu_1_title', [
            'vi' => 'Liên kết nhanh',
            'en' => 'Quick links',
            'zh' => '快速链接',
        ]);
        $this->migrator->add('site.footer_menu_2_id', null);
        $this->migrator->add('site.footer_menu_2_title', [
            'vi' => 'Sản phẩm',
            'en' => 'Products',
            'zh' => '产品',
        ]);
    }
};
