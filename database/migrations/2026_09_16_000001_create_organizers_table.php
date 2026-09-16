<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->string('org_name');
            $table->string('contact_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('password_hash');
            $table->string('plan_tier');
            $table->string('approval_state');
            $table->string('rejection_reason')->nullable();
            $table->timestamp('consent_given_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizers');
    }
};
