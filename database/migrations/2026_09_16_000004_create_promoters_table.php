<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promoters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('organizers');
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('instagram_url');
            $table->string('tiktok_url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoters');
    }
};
