<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_export_requests', function (Blueprint $table) {
            $table->string('download_path')->nullable()->after('status');
            $table->dropColumn('download_url');
        });
    }

    public function down(): void
    {
        Schema::table('data_export_requests', function (Blueprint $table) {
            $table->string('download_url')->nullable()->after('status');
            $table->dropColumn('download_path');
        });
    }
};
