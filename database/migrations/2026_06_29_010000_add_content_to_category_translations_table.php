<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('category_translations', 'content')) {
            return;
        }

        Schema::table('category_translations', function (Blueprint $table): void {
            $table->longText('content')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('category_translations', 'content')) {
            return;
        }

        Schema::table('category_translations', function (Blueprint $table): void {
            $table->dropColumn('content');
        });
    }
};
