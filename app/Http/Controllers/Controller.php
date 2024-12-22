<?php

namespace App\Http\Controllers;
use App\Models\Contract;

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
    protected function calculateAccumulatedDividends($contractStartDate, $currentDate, $quarterlyPayment, $lastPaymentDate = null) {
       // dd($contractStartDate, $currentDate, $quarterlyPayment, $lastPaymentDate);
        $startDate = $lastPaymentDate ? new DateTime($lastPaymentDate) : new DateTime($contractStartDate);
        $currentDate = new DateTime($currentDate);
    
        $quarterStartDate = clone $startDate;
        while ($quarterStartDate < $currentDate) {
            $quarterStartDate->modify('+3 months');
        }
        $quarterStartDate->modify('-3 months');
    
        $quarterEndDate = clone $quarterStartDate;
        $quarterEndDate->modify('+3 months');
    
        $daysInQuarter = $quarterStartDate->diff($quarterEndDate)->days;
        $daysSinceQuarterStart = $quarterStartDate->diff($currentDate)->days;
    
        $dailyDividend = $quarterlyPayment / $daysInQuarter;
    
        return round($daysSinceQuarterStart * $dailyDividend);
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



    protected function handleExecutedApplication($application) {
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
        }

       
        $message = 'Статус заявки успешно изменен!';
        return $message;
    }

    // protected function onTimePayout($application, $isCancelled = false) {
       

    //     $actions = [
    //         'Забрать дивиденды частично' => fn() => $this->partialPayout($application, $isCancelled),
    //     ];
    //     $action = $actions[$application->type_of_processing];
    //     $message = $action();

    //     return $message;
    // }


    // protected function partialPayout($application,  $isCancelled = false) {
    //     $contract = Contract::find($application->contract_id);
    //     if ($isCancelled) {
    //         $application->update(['status' => 'Отменена']);
    //         $contract->avalible_dividends->update(['avaliable_dividends' => null]);
    //         return 'Заявка отменена.';

    //     }
    //     $newStatus = match ($application->status) {
    //         'В обработке' => 'Согласована',
    //         'Согласована' => 'Исполнена',
    //         default => $application->status,
    //     };
    //     // Обновляем статус заявки
    //     $application->update(['status' => $newStatus]);
    //     $mainSum = $contract->sum;
    //     $avalible_dividends = round($contract->avalible_dividends);
    //     $contract->update([
    //         'sum' => $mainSum + $avalible_dividends,
    //         'last_payment_date' => now(),
    //     ]);

    //     if ($newStatus === 'Исполнена') {
    //         $contract->avalible_dividends->update(['avaliable_dividends' => null]);
    //         $this->createTransaction($application, $avalible_dividends, 'Договор');
    //     }

    //     $message = 'Статус заявки успешно изменен!';
       
       
        
    //     return  $message;
    // }
    protected function onTimePayout($application, $isCancelled = false)
    {
        $actions = [
            'Забрать дивиденды частично' => fn() => $this->partialPayout($application, $isCancelled),
        ];

        // Обрабатываем заявку в зависимости от типа
        $action = $actions[$application->type_of_processing] ?? fn() => 'Тип обработки не предусмотрен.';
        $message = $action();

        return $message;
    }

    protected function partialPayout($application, $isCancelled = false)
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
            $this->createTransaction($application, $application->dividends, 'Заявка');

        }

        return 'Статус заявки успешно изменен!';
    }


        
        
    
}
