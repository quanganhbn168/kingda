<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class IntegrationSettings extends Settings
{
    public ?string $google_analytics_id;

    public ?string $google_tag_manager_id;

    public ?string $facebook_pixel_id;

    public ?string $zalo_oa_id;

    public ?string $messenger_page_id;

    public ?string $recaptcha_site_key;

    public ?string $recaptcha_secret_key;

    public ?string $header_scripts;

    public ?string $footer_scripts;

    public static function group(): string
    {
        return 'integration';
    }
}