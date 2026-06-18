<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CompanySettings extends Settings
{
    public string $company_name;

    public ?string $company_short_name;

    public ?string $company_english_name;

    public ?string $tax_code;

    public ?string $business_license_number;

    public ?string $business_license_date;

    public ?string $business_license_place;

    public ?string $legal_representative;

    public ?string $legal_representative_position;

    public ?string $founded_year;

    public ?string $registered_address;

    public ?string $office_address;

    public ?string $factory_address;

    public ?string $business_fields;

    public ?array $stats;

    public ?string $bank_name;

    public ?string $bank_account_name;

    public ?string $bank_account_number;

    public static function group(): string
    {
        return 'company';
    }
}
