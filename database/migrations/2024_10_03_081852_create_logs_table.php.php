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
            $table->unsignedBigInteger('model_id'); 
            $table->string('model_type'); 
            $table->string('change')->nullable(); 
            $table->string('action'); 
            $table->text('old_value')->nullable(); 
            $table->text('new_value')->nullable();
            $table->unsignedBigInteger('created_by'); 
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

