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
        Schema::table('about_us', function (Blueprint $table) {
            if (!Schema::hasColumn('about_us', 'version')) {
                $table->string('version')->nullable()->after('app_name');
            }
            if (!Schema::hasColumn('about_us', 'developer')) {
                $table->string('developer')->nullable()->after('version');
            }
            if (!Schema::hasColumn('about_us', 'contact')) {
                $table->string('contact')->nullable()->after('developer');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_us', function (Blueprint $table) {
            $table->dropColumn(['version', 'developer', 'contact']);
        });
    }
};
