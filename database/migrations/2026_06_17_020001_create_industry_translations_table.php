<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industry_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('slug');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['industry_id', 'locale']);
            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industry_translations');
    }
};
