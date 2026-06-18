<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('page_translations', 'content_blocks')) {
            Schema::table('page_translations', function (Blueprint $table) {
                $table->dropColumn('content_blocks');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('page_translations', 'content_blocks')) {
            Schema::table('page_translations', function (Blueprint $table) {
                $table->json('content_blocks')->nullable()->after('content');
            });
        }
    }
};
