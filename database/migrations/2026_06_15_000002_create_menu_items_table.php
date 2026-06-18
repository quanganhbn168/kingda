<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('menu_id')
                ->constrained('menus')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menu_items')
                ->nullOnDelete();

            $table->string('locale', 10)->default('vi'); // vi, en, zh
            $table->string('label');

            $table->string('link_type')->default('custom');
            // custom, page, category, service, product, post

            $table->nullableMorphs('linkable');
            // linkable_type + linkable_id. Không tạo FK vì trỏ được nhiều model.

            $table->string('url')->nullable();
            $table->string('target')->default('_self'); // _self, _blank
            $table->string('rel')->nullable();
            $table->string('icon')->nullable();
            $table->string('css_class')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['menu_id', 'locale', 'is_active', 'sort_order']);
            $table->index(['link_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
