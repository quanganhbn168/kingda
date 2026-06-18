<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();

            $table->string('type')->default('page');
            $table->string('template')->default('default');

            $table->boolean('is_home')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index(['template', 'is_active']);
            $table->index(['is_home', 'is_active']);
            $table->index(['sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};