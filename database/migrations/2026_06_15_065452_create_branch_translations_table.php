<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_translations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('locale', 10);

            $table->string('name');
            $table->string('slug');

            $table->string('short_address')->nullable();
            $table->text('display_address')->nullable();

            $table->text('description')->nullable();
            $table->text('working_hours')->nullable();

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('meta_robots')->default('index,follow');
            $table->string('canonical_url')->nullable();

            $table->timestamps();

            $table->unique(['branch_id', 'locale']);
            $table->unique(['locale', 'slug']);

            $table->index(['locale']);
            $table->index(['slug', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_translations');
    }
};