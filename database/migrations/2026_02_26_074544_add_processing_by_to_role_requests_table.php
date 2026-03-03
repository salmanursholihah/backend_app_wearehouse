<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('role_requests', function (Blueprint $table) {
        $table->unsignedBigInteger('processed_by')->nullable()->after('status');
        $table->timestamp('processed_at')->nullable()->after('processed_by');
        $table->text('reason')->nullable()->after('processed_at');

        $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
    });
}

public function down()
{
    Schema::table('role_requests', function (Blueprint $table) {
        $table->dropForeign(['processed_by']);
        $table->dropColumn(['processed_by', 'processed_at', 'reason']);
    });
}};
