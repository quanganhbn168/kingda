<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public array $hotlines;

    public array $phones;

    public array $sales_contacts;

    public array $support_contacts;

    public array $emails;

    public array $social_links;

    public ?string $wechat_qr_image;

    public ?array $default_address;

    public ?array $default_working_hours;

    public ?string $default_google_map_url;

    public ?string $default_google_map_embed;

    public ?string $contact_form_receiver_email;

    public static function group(): string
    {
        return 'contact';
    }
}
