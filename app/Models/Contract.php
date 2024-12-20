<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;
use Carbon\Carbon;

class Contract extends Model
{
    use HasFactory;

    // Разрешённые для массового заполнения поля
    protected $fillable = [
        'title',
        'user_id',
        'manager_id',
        'contract_number',
        'create_date',
        'deadline',
        'sum',
        'procent',
        'payments',
        'contract_status',
        'agree_with_terms',
        'dividends',
        'number_of_payments',
        'last_payment_date',
    ];
    /**
     * Связь с транзакциями (один ко многим)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'contract_id');
    }

    public function application()
    {
        return $this->hasMany(Application::class, 'contract_id');
    }

    /**
     * Связь с пользователем (многие к одному)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Связь с менеджером (многие к одному)
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }




    protected $intervalMapping = [
        'ежеквартально' => 3, // 3 месяца
        'ежегодно' => 12,     // 12 месяцев
        'в конце срока' => 'end', // Обозначает конец договора
    ];

    public function calculatePaymentDates()
    {
        $startDate = Carbon::parse($this->start_date); // Начало договора
        $endDate = Carbon::parse($this->end_date);     // Конец договора
        $interval = $this->payment_interval;          // Строковый интервал

        // Получаем значение интервала в месяцах или специальный флаг
        $months = $this->intervalMapping[$interval] ?? null;

        // if (!$months) {
        //     throw new Exception('Неизвестный интервал выплат: ' . $interval);
        // }

        $dates = [];

        if ($months === 'end') {
            // Выплата только в конце срока
            $dates[] = $endDate;
        } else {
            // Добавляем даты с указанным интервалом
            while ($startDate < $endDate) {
                $dates[] = $startDate->copy();
                $startDate->addMonths($months);
            }
        }

        return $dates;
    }

}
