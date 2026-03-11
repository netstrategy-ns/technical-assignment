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
            $table->foreignId('event_category_id')->constrained('event_categories')->cascadeOnDelete();
            $table->foreignId('venue_type_id')->constrained('venue_types')->cascadeOnDelete();
            $table->text('description');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->dateTime('sale_starts_at')->nullable();
            $table->string('location');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
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
