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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->integer('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->string('condition');
            $table->string('status');
            $table->string('type_of_processing');
            $table->date('create_date');
            $table->date('date_of_payments');
            $table->decimal('sum', 16, 2)->nullable();
            $table->decimal('dividends', 16, 2)->nullable();
            $table->decimal('dividendsAfterExpiration', 16, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
