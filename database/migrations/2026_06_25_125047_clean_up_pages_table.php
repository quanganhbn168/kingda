<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->dropIndex(['template', 'is_active']);
            $table->dropIndex(['is_home', 'is_active']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['key', 'is_home', 'is_system', 'template']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('key')->nullable();
            $table->boolean('is_home')->default(false);
            $table->boolean('is_system')->default(false);
            $table->string('template')->default('default');

            $table->unique('key');
            $table->index(['template', 'is_active']);
            $table->index(['is_home', 'is_active']);
        });
    }
};
