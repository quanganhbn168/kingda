<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $fields = ['default_address', 'default_working_hours'];

        foreach ($fields as $field) {
            $value = DB::table('settings')
                ->where('group', 'contact')
                ->where('name', $field)
                ->value('payload');

            if ($value !== null && $value !== 'null' && $value !== '""') {
                $decoded = json_decode($value, true);
                // If it was saved as a string in DB (e.g. "Tầng 1..."), wrap it in array
                if (is_string($decoded)) {
                    $newPayload = json_encode(['vi' => $decoded, 'en' => '']);
                    DB::table('settings')
                        ->where('group', 'contact')
                        ->where('name', $field)
                        ->update(['payload' => $newPayload]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed
    }
};
