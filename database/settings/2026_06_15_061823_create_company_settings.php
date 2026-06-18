<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('company.company_name', 'Tên công ty');
        $this->migrator->add('company.company_short_name', null);
        $this->migrator->add('company.company_english_name', null);

        $this->migrator->add('company.tax_code', null);

        $this->migrator->add('company.business_license_number', null);
        $this->migrator->add('company.business_license_date', null);
        $this->migrator->add('company.business_license_place', null);

        $this->migrator->add('company.legal_representative', null);
        $this->migrator->add('company.legal_representative_position', null);

        $this->migrator->add('company.founded_year', null);

        $this->migrator->add('company.registered_address', null);
        $this->migrator->add('company.office_address', null);
        $this->migrator->add('company.factory_address', null);

        $this->migrator->add('company.business_fields', null);

        $this->migrator->add('company.bank_name', null);
        $this->migrator->add('company.bank_account_name', null);
        $this->migrator->add('company.bank_account_number', null);
    }
};