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
            $table->string('last_name')->comment('Фамилия');
            $table->string('first_name')->comment('Имя');
            $table->string('middle_name')->comment('Отчество');
            $table->string('email')->unique()->comment('Email');
            $table->char('phone_number', 20)->unique()->comment('Номер телефона');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255)->nullable();
            $table->string('token');
            $table->boolean('active')->default(false);
            $table->string('refresh_token');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null'); // Внешний ключ на таблицу ролей
            $table->decimal('avaliable_balance', 16, 2)->nullable()->comment('Доступный баланс');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
