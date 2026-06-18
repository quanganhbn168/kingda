<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('page_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('locale', 10);

            $table->string('title');
            $table->string('slug')->nullable();

            $table->string('headline')->nullable();
            $table->text('subheadline')->nullable();

            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('meta_robots')->default('index,follow');
            $table->string('canonical_url')->nullable();

            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique(['page_id', 'locale']);
            $table->unique(['locale', 'slug']);

            $table->index(['locale', 'is_published']);
            $table->index(['slug', 'locale']);
            $table->index(['published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_translations');
    }
};
