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
           
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Внешний ключ на таблицу пользователей. Клиент');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade')->comment('Внешний ключ на таблицу пользователей. Менеджер');
            $table->integer('contract_number')->unique()->unsigned()->comment('Номер договора');
            $table->date('create_date')->comment('Дата подписания договора');
            $table->date('deadline')->comment('Дата окончания договора');
            $table->integer('sum')->unsigned()->comment('Сумма договора');
            $table->integer('procent')->unsigned()->comment('Ставка в процентах');
            $table->string('payments')->comment('Периодичность платежей');
            $table->decimal('avaliable_dividends',16,2)->unsigned()->nullable()->comment('Доступный остаток от дивидендов');
            $table->date('last_payment_date')->nullable()->after('deadline');
            $table->boolean('contract_status')->default(true)->comment('Статус договора');
            $table->boolean('agree_with_terms')->default(false)->comment('Выплаты в конце срока');
            $table->boolean('is_aplication')->default(false)->comment('Признак наличия заявки');
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

