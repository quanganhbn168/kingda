<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('site.site_name', config('app.name', 'Company Website'));
        $this->migrator->add('site.site_slogan', null);

        $this->migrator->add('site.logo', null);
        $this->migrator->add('site.logo_footer', null);
        $this->migrator->add('site.favicon', null);
    }
};