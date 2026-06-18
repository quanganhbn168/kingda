<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $site_name;

    public ?string $site_slogan;

    public ?string $logo;

    public ?string $logo_footer;

    public ?string $favicon;

    public static function group(): string
    {
        return 'site';
    }
}