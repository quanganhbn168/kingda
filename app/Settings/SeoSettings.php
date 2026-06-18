<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public ?string $default_seo_title;

    public ?string $default_seo_description;

    public ?string $default_og_image;

    public ?string $default_robots;

    public ?string $default_canonical_url;

    public static function group(): string
    {
        return 'seo';
    }
}