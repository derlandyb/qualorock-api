<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_info_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events');
            // No FK constraint: web-app owns the consumer User model, not yet executed
            // (per project CLAUDE.md's data-owner-first order) - spec gap, plain ID reference.
            $table->unsignedBigInteger('consumer_user_id');
            $table->text('message');
            $table->text('organizer_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_info_requests');
    }
};
