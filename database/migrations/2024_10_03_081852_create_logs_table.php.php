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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id'); // ID изменяемой записи
            $table->string('model_type'); // Тип модели (например, User)
            $table->string('change')->nullable(); // Поле, которое изменилось
            $table->string('action'); // Действие, которое произошло
            $table->text('old_value')->nullable(); // Старое значение
            $table->text('new_value')->nullable(); // Новое значение
            $table->unsignedBigInteger('created_by'); // ID пользователя, совершившего изменение
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};

