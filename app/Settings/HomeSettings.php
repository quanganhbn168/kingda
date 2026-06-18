<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HomeSettings extends Settings
{
    public ?array $stats;

    public ?array $intro;

    public ?array $industries;

    public ?array $products;

    public ?array $capabilities;

    public ?array $certifications;

    public ?array $advantages;

    public ?array $customers;

    public ?array $news;

    public ?array $cta;

    public static function group(): string
    {
        return 'home';
    }
}
