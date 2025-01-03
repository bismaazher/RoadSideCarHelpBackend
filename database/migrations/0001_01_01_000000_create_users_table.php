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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('mobile_no', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->longText('meta_data')->nullable();
            $table->string('fcm_token', 255)->nullable();
            $table->string('device_id', 255)->nullable();
            $table->string('device_type', 255)->nullable();
            $table->boolean('is_verify')->default(0);
            $table->boolean('is_active')->default(0);
            $table->string('latitude',100)->nullable();
            $table->string('longitude',100)->nullable();
            $table->boolean('is_super_admin')->default(0);
            $table->boolean('is_notification')->default(1);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_group_id','email','mobile_no'],'index1');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
