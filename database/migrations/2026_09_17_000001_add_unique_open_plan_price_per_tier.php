<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE UNIQUE INDEX plan_prices_open_tier_unique ON plan_prices (tier) WHERE effective_to IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX plan_prices_open_tier_unique');
    }
};
