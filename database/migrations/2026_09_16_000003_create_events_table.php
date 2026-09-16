<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('organizers');
            $table->foreignId('venue_id')->constrained('venues');
            $table->string('title');
            $table->text('description');
            $table->timestamp('date_time');
            $table->string('location');
            $table->string('full_address');
            $table->string('featured_image_url');
            $table->string('external_ticket_link');
            $table->string('price_type');
            $table->string('music_category');
            $table->unsignedInteger('capacity')->nullable();
            $table->string('age_range')->nullable();
            $table->text('additional_info')->nullable();
            $table->text('accessibility_info')->nullable();
            $table->text('event_rules')->nullable();
            $table->string('status');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
