<?php

namespace App\Http\Controllers;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use App\Models\Transaction;
use App\Models\User;

use DateTime;

abstract class Controller
{
    //
    /**
     * Нормализация значений для сравнения
     *
     * @param mixed $value
     * @return string
     */
    protected function normalizeValue($value): string
    {
        return trim(strtolower((string) $value));
    }
    protected function termOfTheContract($start, $end)
    {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);

        // Разница в годах
        return $startDate->diff($endDate)->y;
    }


protected function calculateAccumulatedDividends($contractStartDate, $contractDeadline, $currentDate, $paymentAmount, $paymentFrequency = 'Ежеквартально', $lastPaymentDate = null)
{
  

    return match ($paymentFrequency) {
        'По истечению срока' => $this->calculateEndOfTermDividends($contractStartDate, $contractDeadline, $currentDate, $paymentAmount),
        'Ежеквартально'   => $this->calculateQuarterlyDividends($contractStartDate, $currentDate, $paymentAmount, $lastPaymentDate ),
        'Ежегодно' => $this->calculateAnnualDividends($contractStartDate, $currentDate, $paymentAmount, $lastPaymentDate),
        default => 0,
    };
}


protected function calculateEndOfTermDividends($contractStartDate, $contractEndDate, $currentDate, $paymentAmount)
{
    $startDate = new DateTime($contractStartDate);
    $endDate = new DateTime($contractEndDate);
    $currentDate = new DateTime($currentDate);

    // Дни с момента начала договора до текущей даты
    $daysSinceStart = $startDate->diff($currentDate)->days;

    // Полный срок договора в днях
    $totalContractDays = $startDate->diff($endDate)->days;

    // Дивиденды за один день
    $dailyDividend = $paymentAmount / $totalContractDays;

    // Если договор истёк, начисляем 100% суммы
    if ($currentDate >= $endDate) {
        return round($paymentAmount);
    }

    // Пропорционально за прошедшие дни
    $accruedDividends = $daysSinceStart * $dailyDividend;

    return round($accruedDividends);
}

protected function calculateAnnualDividends($contractStartDate, $currentDate, $paymentAmount,$lastPaymentDate)
{
    $startDate = $lastPaymentDate ? new DateTime($lastPaymentDate) : new DateTime($contractStartDate);
    $endDate = (clone $startDate)->modify('+1 year');
    $currentDate = new DateTime($currentDate);

    // Считаем количество дней в году от даты начала договора
    $daysInYear = $startDate->diff($endDate)->days;

    // Дни с момента начала договора до текущей даты
    $daysSinceStart = $startDate->diff($currentDate)->days;

    // Дивиденды за 1 день
    $dailyDividend = $paymentAmount / $daysInYear;

    // Пропорциональные дивиденды за прошедшие дни
    $accruedDividends = $daysSinceStart * $dailyDividend;

    return round($accruedDividends);
}


protected function calculateQuarterlyDividends($contractStartDate, $currentDate, $paymentAmount, $lastPaymentDate )
{
    $startDate = $lastPaymentDate ? new DateTime($lastPaymentDate) : new DateTime($contractStartDate);
    $currentDate = new DateTime($currentDate);

    // Рассчитываем квартальные выплаты
    $quarterAmount = $paymentAmount / 4;

    // Определяем, сколько дней прошло с момента начала квартала
    $daysSinceStart = $startDate->diff($currentDate)->days;

    // Определяем дату начала текущего квартала
    $quarterStart = clone $startDate;
    while ($quarterStart < $currentDate) {
        $quarterStart->modify('+3 months');
    }
    $quarterStart->modify('-3 months');

    // Определяем конец квартала
    $quarterEnd = clone $quarterStart;
    $quarterEnd->modify('+3 months');

    // Считаем фактическое количество дней в квартале
    $quarterDays = $quarterStart->diff($quarterEnd)->days;

    // Дивиденды за 1 день
    $dailyDividend = $quarterAmount / $quarterDays;

    // Считаем, сколько дней прошло с начала квартала
    $daysSinceQuarterStart = $quarterStart->diff($currentDate)->days;

    // Рассчитываем накопленные дивиденды
    $accruedDividends = $daysSinceQuarterStart * $dailyDividend;

    return round($accruedDividends);
}






    
    

    protected function createTransaction($application, $sum, $source)
    {
        //dd($application, $sum, $source);
        $application->user->userTransactions()->create([
            'contract_id' => $application->contract_id,
            'manager_id' => $application->manager_id,
            'user_id' => $application->user_id,
            'date_transition' => $application->date_of_payments,
            'sum_transition' => $sum,
            'sourse' => $source,
        ]);
    }
    
    protected function handleInProgressApplication($application) {
        $application->update([
            'status' => 'В обработке',
        ]);
        $message = 'Статус заявки успешно изменен!';
        return  $message;
    }


    protected function handleAgreedApplication($application) {
         // Маппинг действий в зависимости от состояния заявки
    $actions = [
        'Раньше срока' => fn() => $this->beforeTheDeadline($application),
        'В срок' => fn() => $this->onTimePayout($application),  // Добавляем "В срок"
    ];

    // Выполняем действие в зависимости от состояния (по умолчанию — "В срок")
        $action = $actions[$application->condition]; 
        $message = $action();

        return $message;
    }



    protected function handleExecutedApplication($application , $user) {
        $actions = [
            'Раньше срока' => fn() => $this->beforeTheDeadline($application),
            'В срок' => fn() => $this->onTimePayout($application),  // Добавляем "В срок"
        ];
        $action = $actions[$application->condition]; 
        $message = $action();

        return $message;
    }

    protected function handleCancelledApplication($application) {
        $actions = [
            'Раньше срока' => fn() => $this->beforeTheDeadline($application, true),
            'В срок' => fn() => $this->onTimePayout($application, true),  // Добавляем "В срок"
        ];
        $action = $actions[$application->condition]; 
        $message = $action();

        return $message;
    }



    protected function beforeTheDeadline($application, $isCancelled = false) {
       // Определяем новый статус заявки
        if ($isCancelled) {
            $application->update(['status' => 'Отменена']);
            return 'Заявка отменена.';
        }

        $newStatus = match ($application->status) {
            'В обработке' => 'Согласована',
            'Согласована' => 'Исполнена',
            default => $application->status,
        };

        // Обновляем статус заявки
        $application->update(['status' => $newStatus]);

        // Если заявка переходит в "Исполнена", обрабатываем транзакцию и договор
        if ($newStatus === 'Исполнена') {
            $contract = Contract::find($application->contract_id);

            if (!$contract) {
                $message = 'Ошибка: Договор не найден.';
            }

            // Завершаем договор и создаём транзакцию
            $contract->update(['contract_status' => false]);
            $this->createTransaction($application, $application->sum, 'Заявка');

            $user=User::find($contract->user_id);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Transaction::class,
                'change' => 'Новая транзакция',
                'action' => 'Создание транзакции',
                'old_value' => '',
                'new_value' => 'Сумама: ' . $application->sum,
                'created_by' => Auth::id(),
            ]);
            $user->userNotifications()->create([
                'title' => 'Создание транзакции',
                'content'=> 'Создана транзакция на сумму: ' . $application->sum,
            ]);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Contract::class,
                'change' => 'Смена статуса договора',
                'action' => 'Закрытие договора No ' . $contract->contract_number,
                'old_value' => 'Активный',
                'new_value' => 'Неактивный',
                'created_by' => Auth::id(),
            ]);
            $user->userNotifications()->create([
                'title' => 'Закрытие договора',
                'content'=> 'Договор No ' . $contract->contract_number . 'был закрыт.',
            ]);
        }

       
        $message = 'Статус заявки успешно изменен!';
        return $message;
    }

   
    protected function onTimePayout($application, $isCancelled = false)
    {
        $actions = [
            'Забрать дивиденды частично' => fn() => $this->partialPayoutDividends($application, $isCancelled),
            'Забрать дивиденды целиком' => fn() => $this->fullPayoutDividends($application, $isCancelled),
            'Забрать дивиденды и сумму' => fn() => $this->fullPayout($application, $isCancelled),
        ];

        // Обрабатываем заявку в зависимости от типа
        $action = $actions[$application->type_of_processing] ?? fn() => 'Тип обработки не предусмотрен.';
        $message = $action();

        return $message;
    }

    protected function partialPayoutDividends($application, $isCancelled = false)
    {
        $contract = Contract::find($application->contract_id);

        if (!$contract) {
            return 'Ошибка: Договор не найден.';
        }

        // Отмена заявки
        if ($isCancelled) {
            $application->update(['status' => 'Отменена']);
            $contract->update(['avaliable_dividends' => null]);
            return 'Заявка отменена.';
        }

        // Определяем новый статус заявки
        $newStatus = match ($application->status) {
            'В обработке' => 'Согласована',
            'Согласована' => 'Исполнена',
            default => $application->status,
        };

        // Обновляем статус заявки
        $application->update(['status' => $newStatus]);

        $mainSum = $contract->sum;
        $avalible_dividends = round($contract->avaliable_dividends ?? 0);
        // dd($newStatus);
        // Обновляем сумму по договору и сбрасываем доступные дивиденды
        // Если заявка исполнена, создаём транзакцию
        if ($newStatus === 'Исполнена') {
            $contract->update([
                'sum' => $mainSum + $avalible_dividends,
                'last_payment_date' => now(),
                'avaliable_dividends' =>  null,
            ]);
            $this->createTransaction($application, $avalible_dividends, 'Договор');
            $user=User::find($contract->user_id);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Transaction::class,
                'change' => 'Новая транзакция',
                'action' => 'Создание транзакции',
                'old_value' => '',
                'new_value' => 'Сумама: ' . $avalible_dividends,
                'created_by' => Auth::id(),
            ]);
            $user->userNotifications()->create([
                'title' => 'Создание транзакции',
                'content'=> 'Создана транзакция на сумму: ' . $avalible_dividends,
            ]);
            $this->createTransaction($application, $application->dividends, 'Заявка');
            $user=User::find($contract->user_id);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Transaction::class,
                'change' => 'Новая транзакция',
                'action' => 'Создание транзакции',
                'old_value' => '',
                'new_value' => 'Сумама: ' . $application->dividends,
                'created_by' => Auth::id(),
            ]);
            $user->userNotifications()->create([
                'title' => 'Создание транзакции',
                'content'=> 'Создана транзакция на сумму: ' . $application->dividends,
            ]);

        }

        return 'Статус заявки успешно изменен!';
    }

    protected function fullPayoutDividends($application, $isCancelled = false)
    {
        $contract = Contract::find($application->contract_id);

        if (!$contract) {
            return 'Ошибка: Договор не найден.';
        }

        if ($isCancelled) {
            $application->update(['status' => 'Отменена']);
            return 'Заявка отменена.';
        }
        $newStatus = match ($application->status) {
            'В обработке' => 'Согласована',
            'Согласована' => 'Исполнена',
            default => $application->status,
        };
        $application->update(['status' => $newStatus]);
        if ($newStatus === 'Исполнена') {
            $contract->update([
                'last_payment_date' => now(),
            ]);
            $this->createTransaction($application, $application->dividends, 'Заявка');
            $user=User::find($contract->user_id);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Transaction::class,
                'change' => 'Новая транзакция',
                'action' => 'Создание транзакции',
                'old_value' => '',
                'new_value' => 'Сумама: ' . $application->dividends,
                'created_by' => Auth::id(),
            ]);
            $user->userNotifications()->create([
                'title' => 'Создание транзакции',
                'content'=> 'Создана транзакция на сумму: ' . $application->dividends,
            ]);

        }
        return 'Статус заявки успешно изменен!';

    }
    protected function fullPayout($application, $isCancelled = false)
    {
        $contract = Contract::find($application->contract_id);

        if (!$contract) {
            return 'Ошибка: Договор не найден.';
        }

        if ($isCancelled) {
            $application->update(['status' => 'Отменена']);
            return 'Заявка отменена.';
        }
        $newStatus = match ($application->status) {
            'В обработке' => 'Согласована',
            'Согласована' => 'Исполнена',
            default => $application->status,
        };
        $application->update(['status' => $newStatus]);
        if ($newStatus === 'Исполнена') {
            $contract->update([
                'contract_status' => false,
            ]);
            $this->createTransaction($application, $application->sum + $application->dividends, 'Заявка');
            $user=User::find($contract->user_id);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Contract::class,
                'change' => 'Смена статуса договора',
                'action' => 'Закрытие договора No ' . $contract->contract_number,
                'old_value' => 'Активный',
                'new_value' => 'Неактивный',
                'created_by' => Auth::id(),
            ]);
            $user->userNotifications()->create([
                'title' => 'Договор',
                'content'=> 'Договор No ' . $contract->contract_number . ' был закрыт',
            ]);
            
        }
        return 'Статус заявки успешно изменен!';
    }


        
        
    
}
