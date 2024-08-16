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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('module',100);
            $table->foreignId('module_id')->constrained('user_post')->onDelete('cascade');
            $table->text('file_url',5000);
            $table->text('thumbnail_url',5000)->nullable();
            $table->string('filename',200)->nullable();
            $table->string('file_type',50)->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
