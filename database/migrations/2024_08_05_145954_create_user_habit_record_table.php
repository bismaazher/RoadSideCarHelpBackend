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
        Schema::create('user_habit_record', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_habit_id')->constrained('user_habits')->onDelete('cascade');
            $table->text('days')->notNullable();
            $table->time('record_time')->nullable();
            $table->date('date')->nullable();
            $table->string('start_time');
            $table->string('end_time');
            $table->enum('status',['1','0'])->default('1');
            $table->timestamps();
            $table->softDeletesTz($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_habit_record');
    }
};
