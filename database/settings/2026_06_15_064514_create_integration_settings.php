<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('integration.google_analytics_id', null);
        $this->migrator->add('integration.google_tag_manager_id', null);
        $this->migrator->add('integration.facebook_pixel_id', null);

        $this->migrator->add('integration.zalo_oa_id', null);
        $this->migrator->add('integration.messenger_page_id', null);

        $this->migrator->add('integration.recaptcha_site_key', null);
        $this->migrator->add('integration.recaptcha_secret_key', null);

        $this->migrator->add('integration.header_scripts', null);
        $this->migrator->add('integration.footer_scripts', null);
    }
};