<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message');

            $table->string('source')->nullable(); // contact_page, product_detail, service_detail...
            $table->nullableMorphs('related'); // related_type + related_id

            $table->string('status')->default('new'); // new, processing, done, spam
            $table->text('admin_note')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
