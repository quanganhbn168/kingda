<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slide_translations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('slide_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('locale', 10);
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('primary_button_label')->nullable();
            $table->string('primary_button_url')->nullable();
            $table->string('secondary_button_label')->nullable();
            $table->string('secondary_button_url')->nullable();
            $table->string('image_alt')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['slide_id', 'locale']);
            $table->index(['locale', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slide_translations');
    }
};
