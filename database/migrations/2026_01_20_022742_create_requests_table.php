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
Schema::create('requests', function (Blueprint $table) {
    $table->id();

    // user yang mengajukan
    $table->foreignId('user_id')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->enum('status', [
        'pending',
        'approved',
        'rejected',
        'taken'
    ])->default('pending');

    // admin yang memproses
    $table->foreignId('processed_by')
        ->nullable()
        ->constrained('users');

    $table->text('note')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
