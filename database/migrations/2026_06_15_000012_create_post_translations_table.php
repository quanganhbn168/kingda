<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_translations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();

            $table->string('locale', 10);
            $table->string('slug');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('meta_robots')->default('index,follow');

            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'locale']);
            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_translations');
    }
};
