<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_contacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type');

            $table->json('label')->nullable();

            $table->string('value');
            $table->string('display_value')->nullable();
            $table->string('url')->nullable();

            $table->string('contact_person')->nullable();
            $table->string('position')->nullable();

            $table->boolean('is_primary')->default(false);
            $table->boolean('is_public')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'type']);
            $table->index(['type', 'is_public']);
            $table->index(['is_primary', 'is_public']);
            $table->index(['sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_contacts');
    }
};