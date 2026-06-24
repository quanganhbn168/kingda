<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('site.footer_menu_id', null);
        $this->migrator->add('site.footer_description', [
            'vi' => 'Công ty TNHH Thương Mại Công Nghệ Kingda',
            'en' => 'Kingda Technology Trading Company Limited',
            'zh' => '金达科技贸易有限公司',
        ]);
    }
};
