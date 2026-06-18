<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contact.hotlines', []);
        $this->migrator->add('contact.phones', []);
        $this->migrator->add('contact.sales_contacts', []);
        $this->migrator->add('contact.support_contacts', []);
        $this->migrator->add('contact.emails', []);
        $this->migrator->add('contact.social_links', []);

        $this->migrator->add('contact.default_address', null);
        $this->migrator->add('contact.default_working_hours', null);
        $this->migrator->add('contact.default_google_map_url', null);
        $this->migrator->add('contact.default_google_map_embed', null);

        $this->migrator->add('contact.contact_form_receiver_email', null);
    }
};