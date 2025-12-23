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
        Schema::table('availability_schedules', function (Blueprint $table) {
            $table->json('date_overrides')->nullable()->after('schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availability_schedules', function (Blueprint $table) {
            $table->dropColumn('date_overrides');
        });
    }
};
