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
        Schema::table('users', function (Blueprint $table) {
            $table->string('handle')->unique()->nullable()->after('username');
            $table->string('brand_name')->nullable()->after('handle');
            $table->string('primary_color', 7)->default('#D6FF00')->after('brand_name');
            $table->string('hero_image_url')->nullable()->after('primary_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['handle', 'brand_name', 'primary_color', 'hero_image_url']);
        });
    }
};
