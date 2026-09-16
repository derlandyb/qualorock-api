<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->string('tier');
            $table->unsignedInteger('amount');
            $table->timestamp('effective_from');
            $table->timestamp('effective_to')->nullable();
            // No FK constraint: no super_admins table exists yet in any admin-panel task
            // (super_admin provisioning is out-of-band per AD-003) - spec gap, plain ID reference.
            $table->unsignedBigInteger('set_by_super_admin_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};
