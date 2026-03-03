<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up()
{
    Schema::table('role_requests', function (Blueprint $table) {
        $table->text('note')->nullable()->after('requested_role');
    });
}

public function down()
{
    Schema::table('role_requests', function (Blueprint $table) {
        $table->dropColumn('note');
    });
}
};
