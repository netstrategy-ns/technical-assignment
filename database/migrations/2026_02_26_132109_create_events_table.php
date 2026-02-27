<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('category');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('venue');
            $table->string('city');
            $table->string('image_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->dateTime('sales_start_at');
            $table->boolean('queue_enabled')->default(false);
            $table->unsignedSmallInteger('queue_max_concurrent')->nullable();
            $table->timestamps();

            $table->index('starts_at');
            $table->index('category');
            $table->index('city');
            $table->index('is_featured');
            $table->index('sales_start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
