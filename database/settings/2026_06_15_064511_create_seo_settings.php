<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.default_seo_title', null);
        $this->migrator->add('seo.default_seo_description', null);
        $this->migrator->add('seo.default_og_image', null);
        $this->migrator->add('seo.default_robots', 'index,follow');
        $this->migrator->add('seo.default_canonical_url', null);
    }
};