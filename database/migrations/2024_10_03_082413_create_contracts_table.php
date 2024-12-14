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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            // Связь с таблицей пользователей (user_id и manager_id)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->integer('contract_number')->unique()->unsigned();
            $table->date('create_date');
            $table->date('deadline');
            $table->integer('sum')->unsigned();
            $table->integer('procent')->unsigned();
            $table->string('payments')->nullable();
            $table->decimal('dividends',16,2)->nullable();
            $table->boolean('contract_status')->default(true);
            $table->boolean('agree_with_terms')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

