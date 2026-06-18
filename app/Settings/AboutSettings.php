<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AboutSettings extends Settings
{
    public ?array $hero;

    public ?array $intro;

    public ?array $timeline;

    public ?array $culture;

    public ?array $development;

    public ?array $capabilities;

    public ?array $production;

    public ?array $certificates;

    public ?array $intellectual_property;

    public ?array $research;

    public ?array $advantages;

    public ?array $applications;

    public ?array $customers;

    public ?array $organization;

    public ?array $commitment;

    public ?array $contact;

    public static function group(): string
    {
        return 'about';
    }
}
