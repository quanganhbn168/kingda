<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$address = DB::table('settings')->where('group', 'contact')->where('name', 'default_address')->value('payload');
$working_hours = DB::table('settings')->where('group', 'contact')->where('name', 'default_working_hours')->value('payload');

echo "Address was: $address\n";
echo "Working hours was: $working_hours\n";

if ($address !== 'null' && $address !== null) {
    // If it's a string like "Tầng 1...", convert it to an array {"vi": "Tầng 1...", "en": ""}
    $addressDecoded = json_decode($address, true);
    if (is_string($addressDecoded)) {
        $newPayload = json_encode(['vi' => $addressDecoded, 'en' => '']);
        DB::table('settings')->where('group', 'contact')->where('name', 'default_address')->update(['payload' => $newPayload]);
        echo "Address updated to: $newPayload\n";
    }
} else {
    DB::table('settings')->where('group', 'contact')->where('name', 'default_address')->update(['payload' => '{"vi":"","en":""}']);
}

if ($working_hours !== 'null' && $working_hours !== null) {
    $whDecoded = json_decode($working_hours, true);
    if (is_string($whDecoded)) {
        $newPayload = json_encode(['vi' => $whDecoded, 'en' => '']);
        DB::table('settings')->where('group', 'contact')->where('name', 'default_working_hours')->update(['payload' => $newPayload]);
        echo "Working hours updated to: $newPayload\n";
    }
} else {
    DB::table('settings')->where('group', 'contact')->where('name', 'default_working_hours')->update(['payload' => '{"vi":"","en":""}']);
}
