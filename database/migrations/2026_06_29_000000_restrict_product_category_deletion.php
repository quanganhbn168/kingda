<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });
    }
};
