<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $site_name;

    public ?string $site_slogan;

    public ?string $logo;

    public ?string $logo_footer;

    public ?int $footer_menu_id;

    public ?array $footer_description;

    public ?int $footer_menu_1_id;

    public ?array $footer_menu_1_title;

    public ?int $footer_menu_2_id;

    public ?array $footer_menu_2_title;

    public ?string $favicon;

    public static function group(): string
    {
        return 'site';
    }
}
