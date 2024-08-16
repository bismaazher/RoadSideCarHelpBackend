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
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title',100)->unique();
            $table->text('description')->nullable();
            $table->enum('type',['admin','user'])->default('admin');
            $table->enum('is_super_admin',['1','0'])->default('1');
            $table->enum('status',['1','0'])->default('1');
            $table->timestamps();
            $table->softDeletesTz($column = 'deleted_at', $precision = 0);

            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_groups');
    }
};
