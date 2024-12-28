<?php

namespace App\Http\Controllers;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

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
    //dd($contractStartDate, $contractDeadline, $currentDate, $paymentAmount, $paymentFrequency, $lastPaymentDate);

    return match ($paymentFrequency) {
        'По истечению срока' => $this->calculateEndOfTermDividends($contractStartDate, $contractDeadline, $currentDate, $paymentAmount),
        'Ежеквартально'   => $this->calculateQuarterlyDividends($contractStartDate, $currentDate, $paymentAmount, $lastPaymentDate ),
        'Ежегодно' => $this->calculateAnnualDividends($contractStartDate, $currentDate, $paymentAmount, $lastPaymentDate),
        default => 0,
    };
}


protected function calculateEndOfTermDividends($contractStartDate, $contractDeadline, $currentDate, $paymentAmount)
{
    $startDate = new DateTime($contractStartDate);
    $endDate = new DateTime($contractDeadline);
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
    // dd($daysInYear);
    // Дни с момента начала договора до текущей даты
    $daysSinceStart = $startDate->diff($currentDate)->days;
   // dd($daysSinceStart);
    // Дивиденды за 1 день
    $dailyDividend = $paymentAmount / $daysInYear;
    // dd($dailyDividend);
    // Пропорциональные дивиденды за прошедшие дни
    $accruedDividends = $daysSinceStart * $dailyDividend;
    //dd($accruedDividends);
    return round($accruedDividends);
}


protected function calculateQuarterlyDividends($contractStartDate, $currentDate, $paymentAmount, $lastPaymentDate )
{   
   // dd($contractStartDate, $currentDate, $paymentAmount, $lastPaymentDate);
    $startDate = $lastPaymentDate ? new DateTime($lastPaymentDate) : new DateTime($contractStartDate);
    $currentDate = new DateTime($currentDate);
   // dd($startDate, $currentDate);
    // Рассчитываем квартальные выплаты
    $quarterAmount = $paymentAmount / 4;
   // dd($quarterAmount);
    // Определяем, сколько дней прошло с момента начала квартала
    $daysSinceStart = $startDate->diff($currentDate)->days;
    //dd($daysSinceStart);
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
    //dd($quarterDays);
    // Дивиденды за 1 день
    $dailyDividend = $quarterAmount / $quarterDays;
    //dd($dailyDividend);
    // Считаем, сколько дней прошло с начала квартала
    $daysSinceQuarterStart = $quarterStart->diff($currentDate)->days;

    // Рассчитываем накопленные дивиденды
    $accruedDividends = $daysSinceQuarterStart * $dailyDividend;

    return round($accruedDividends);
}


// function calculateDailyDividends($contractStartDate, $currentDate, $totalAmount, $annualRate) {
//     $startDate = Carbon::parse($contractStartDate);
//     $currentDate = Carbon::parse($currentDate);

//     $dailyDividends = [];
//     $totalAccrued = 0;

//     // Проходим по каждому дню с начала договора до текущей даты
//     for ($date = $startDate->copy(); $date <= $currentDate; $date->addDay()) {
//         // Определяем, високосный ли год
//         $daysInYear = $date->isLeapYear() ? 366 : 365;

//         // Количество дней в текущем месяце
//         $daysInMonth = $date->daysInMonth;

//         // Дивиденды за месяц
//         $monthlyDividend = ($totalAmount * ($annualRate / 100)) / 12;

//         // Дивиденды за день (учитывая дни в месяце)
//         $dailyDividend = $monthlyDividend / $daysInMonth;

//         // Накопленные дивиденды
//         $totalAccrued += $dailyDividend;

//         // Добавляем запись в массив с дивидендами
//         $dailyDividends[] = [
//             'date' => $date->format('Y-m-d'),
//             'dividend' => round($dailyDividend, 2),
//         ];
//     }

//     return $dailyDividends;
// }
// function calculateDailyDividends($contractStartDate, $contractEndDate, $currentDate, $totalAmount, $annualRate) {
//     $startDate = Carbon::parse($contractStartDate);
//     $endDate = Carbon::parse($contractEndDate);
//     $currentDate = Carbon::parse($currentDate);

//     $dailyDividends = [];
//     $totalAccrued = 0;

//     // Проходим по каждому дню с начала договора до даты окончания или текущей даты
//     for ($date = $startDate->copy(); $date <= $currentDate; $date->addDay()) {
//         // Останавливаем начисления, если договор истёк
//         if ($date > $endDate) {
//             break;  // Прерываем цикл
//         }

//         $daysInYear = $date->isLeapYear() ? 366 : 365;
//         $annualDividend = $totalAmount * ($annualRate / 100);
//         $dailyDividend = $annualDividend / $daysInYear;

//         $totalAccrued += $dailyDividend;

//         $dailyDividends[] = [
//             'date' => $date->format('Y-m-d'),
//             'dividend' => round($dailyDividend, 2),
//             'total_accumulated' => round($totalAccrued, 2)
//         ];
//     }

//     return $dailyDividends;
// }



// function calculateWeeklyDividends($contractStartDate, $currentDate, $totalAmount, $annualRate) {
//     $startDate = Carbon::parse($contractStartDate);
//     $currentDate = Carbon::parse($currentDate);

//     $weeklyDividends = [];
//     $totalAccrued = 0;

//     // Рассчитываем дивиденды за неделю
//     $weeklyDividend = ($totalAmount * ($annualRate / 100)) / 52;

//     // Проходим по каждому дню с начала договора до текущей даты
//     $date = clone $startDate;
//     while ($date <= $currentDate) {
//         $totalAccrued += $weeklyDividend;

//         // Добавляем дивиденды на каждую полную неделю
//         $weeklyDividends[] = [
//             'date' => $date->format('Y-m-d'),
//             'dividend' => round($weeklyDividend, 2),
//             'week_number' => $date->weekOfYear,
//             'total_accumulated' => round($totalAccrued, 2),
//         ];

//         // Переход к следующей неделе
//         $date->addDays(7);
//     }

//     // Остаток дней (если остались дни, не укладывающиеся в полную неделю)
//     $remainingDays = $startDate->diffInDays($currentDate) % 7;

//     if ($remainingDays > 0) {
//         $remainingDividend = ($weeklyDividend / 7) * $remainingDays;
//         $totalAccrued += $remainingDividend;

//         $weeklyDividends[] = [
//             'date' => $currentDate->format('Y-m-d'),
//             'dividend' => round($remainingDividend, 2),
//             'week_number' => $currentDate->weekOfYear,
//             'total_accumulated' => round($totalAccrued, 2),
//         ];
//     }

//     return $weeklyDividends;
// }

// function calculateMonthlyDividends($contractStartDate, $contractEndDate, $currentDate, $totalAmount, $annualRate) {
//     $startDate = Carbon::parse($contractStartDate);
//     $endDate = Carbon::parse($contractEndDate);
//     $currentDate = Carbon::parse($currentDate);

//     $monthlyDividends = [];
//     $totalAccrued = 0;

//     // Ежемесячная выплата = годовая ставка / 12
//     $monthlyDividendAmount = ($totalAmount * ($annualRate / 100)) / 12;

//     // Следующая дата выплаты
//     $nextMonthlyPayment = $startDate->copy()->addMonth();

//     // Пока не превысили дату окончания договора
//     while ($nextMonthlyPayment <= $endDate && $nextMonthlyPayment <= $currentDate) {
//         // Корректируем на последний день месяца, если число превышает допустимое
//         $adjustedPaymentDate = $nextMonthlyPayment->copy()->endOfMonth();
//         if ($nextMonthlyPayment->day <= $adjustedPaymentDate->day) {
//             $adjustedPaymentDate = $nextMonthlyPayment;
//         }

//         // Накопление
//         $totalAccrued += $monthlyDividendAmount;

//         $monthlyDividends[] = [
//             'date' => $adjustedPaymentDate->format('Y-m-d'),
//             'dividend' => round($monthlyDividendAmount, 2),
//             'total_accumulated' => round($totalAccrued, 2)
//         ];

//         // Переход на следующий месяц
//         $nextMonthlyPayment->addMonth();
//     }

//     return $monthlyDividends;
// }
function calculateDailyDividends($contractStartDate, $contractEndDate, $currentDate, $totalAmount, $annualRate) {
    $startDate = Carbon::parse($contractStartDate);
    $endDate = Carbon::parse($contractEndDate);
    $currentDate = Carbon::parse($currentDate);

    $dailyDividends = [];
    $totalAccrued = 0;

    // Пройдём по каждому дню с начала договора до текущей даты
    for ($date = $startDate->copy(); $date <= $currentDate && $date <= $endDate; $date->addDay()) {
        // Количество дней в году (учёт високосных лет)
        $daysInYear = $date->isLeapYear() ? 366 : 365;

        // Дивиденды за день
        $dailyDividend = ($totalAmount * ($annualRate / 100)) / $daysInYear;

        $totalAccrued += $dailyDividend;

        $dailyDividends[] = [
            'date' => $date->format('Y-m-d'),
            'dividend' => round($dailyDividend, 2),
            'total_accumulated' => round($totalAccrued, 2)
        ];
    }

    return $dailyDividends;
}

function calculateWeeklyDividends($contractStartDate, $contractEndDate, $currentDate, $totalAmount, $annualRate) {
    $startDate = Carbon::parse($contractStartDate);
    $endDate = Carbon::parse($contractEndDate);
    $currentDate = Carbon::parse($currentDate);

    $weeklyDividends = [];
    $totalAccrued = 0;

    // Следующая дата выплаты (каждые 7 дней)
    $nextWeeklyPayment = $startDate->copy()->addWeek();

    while ($nextWeeklyPayment <= $endDate && $nextWeeklyPayment <= $currentDate) {
        // Рассчитываем, сколько дней в году (учёт високосных лет)
        $daysInYear = $nextWeeklyPayment->isLeapYear() ? 366 : 365;
        
        // Дивиденды за неделю с учётом количества дней в году
        $weeklyDividendAmount = ($totalAmount * ($annualRate / 100)) / $daysInYear * 7;

        // Учитываем неполную неделю в начале и в конце
        if ($nextWeeklyPayment->equalTo($startDate->copy()->addWeek())) {
            $daysPassed = $startDate->diffInDays($nextWeeklyPayment);
            $weeklyDividendAmount = ($weeklyDividendAmount / 7) * $daysPassed;
        } elseif ($nextWeeklyPayment->greaterThanOrEqualTo($endDate)) {
            $daysRemaining = $endDate->diffInDays($nextWeeklyPayment) + 1;
            $weeklyDividendAmount = ($weeklyDividendAmount / 7) * $daysRemaining;
        }

        $totalAccrued += $weeklyDividendAmount;

        $weeklyDividends[] = [
            'date' => $nextWeeklyPayment->format('Y-m-d'),
            'dividend' => round($weeklyDividendAmount, 2),
            'total_accumulated' => round($totalAccrued, 2)
        ];

        // Следующая неделя
        $nextWeeklyPayment->addWeek();
    }

    return $weeklyDividends;
}

// function calculateMonthlyDividends($contractStartDate, $contractEndDate, $currentDate, $totalAmount, $annualRate) {
//     $startDate = Carbon::parse($contractStartDate);
//     $endDate = Carbon::parse($contractEndDate);
//     $currentDate = Carbon::parse($currentDate);

//     $monthlyDividends = [];
//     $totalAccrued = 0;
//     //dd($startDate, $endDate, $currentDate);
//     // Ежемесячная выплата (полный месяц)
//     $monthlyDividendAmount = ($totalAmount * ($annualRate / 100)) / 12;
//     //dd($monthlyDividendAmount);
//     // Следующая дата выплаты
//     $nextMonthlyPayment = $startDate->copy()->addMonth();
//     //dd($nextMonthlyPayment);
//     while ($nextMonthlyPayment <= $endDate && $nextMonthlyPayment <= $currentDate) {
//         // Корректируем на последний день месяца
//         $adjustedPaymentDate = $nextMonthlyPayment->copy()->endOfMonth();
//         if ($nextMonthlyPayment->day <= $adjustedPaymentDate->day) {
//             $adjustedPaymentDate = $nextMonthlyPayment;
//         }

//         // Проверяем первый и последний месяц (если неполный)
//         $daysInMonth = $adjustedPaymentDate->daysInMonth;
//         $daysCovered = $adjustedPaymentDate->diffInDays($startDate) + 1;

//         if ($nextMonthlyPayment->equalTo($startDate->copy()->addMonth())) {
//             // Первый месяц (неполный)
//             $monthlyDividendAmount = ($totalAmount * ($annualRate / 100)) / 12;
//             $monthlyDividendAmount = ($monthlyDividendAmount / $daysInMonth) * $daysCovered;
//             // dd($monthlyDividendAmount);
//         } elseif ($nextMonthlyPayment->greaterThanOrEqualTo($endDate)) {
//             // Последний месяц (неполный)
//             $monthlyDividendAmount = ($totalAmount * ($annualRate / 100)) / 12;
//             $daysRemaining = $endDate->diffInDays($nextMonthlyPayment) + 1;
//             $monthlyDividendAmount = ($monthlyDividendAmount / $daysInMonth) * $daysRemaining;
//         }

//         $totalAccrued += $monthlyDividendAmount;
//         dd($monthlyDividendAmount);
//         $monthlyDividends[] = [
//             'date' => $adjustedPaymentDate->format('Y-m-d'),
//             'dividend' => round($monthlyDividendAmount, 2),
//             'total_accumulated' => round($totalAccrued, 2)
//         ];

//         // Следующий месяц
//         $nextMonthlyPayment->addMonth();
//     }

//     return $monthlyDividends;
// }

// function calculateMonthlyDividends($contractStartDate, $contractEndDate, $currentDate, $totalAmount, $annualRate) {
//     $startDate = Carbon::parse($contractStartDate);
//     $endDate = Carbon::parse($contractEndDate);
//     $currentDate = Carbon::parse($currentDate);

//     $monthlyDividends = [];
//     $totalAccrued = 0;

//     // Ежемесячная выплата (полный месяц)
//     $monthlyDividendAmount = ($totalAmount * ($annualRate / 100)) / 12;

//     // Следующая дата выплаты
//     $nextMonthlyPayment = $startDate->copy()->addMonth();

//     while ($nextMonthlyPayment <= $endDate && $nextMonthlyPayment <= $currentDate) {
//         // Корректируем на последний день месяца
//         //dd($nextMonthlyPayment);
//         $adjustedPaymentDate = $nextMonthlyPayment->copy()->endOfMonth();
//         if ($nextMonthlyPayment->day <= $adjustedPaymentDate->day) {
//             $adjustedPaymentDate = $nextMonthlyPayment;
//         }

//         // Проверяем первый и последний месяц (если неполный)
//         $daysInMonth = $adjustedPaymentDate->daysInMonth;
//         $daysCovered = max($adjustedPaymentDate->diffInDays($startDate), 0) + 1;  // Не даём отрицательные дни

//         if ($nextMonthlyPayment->equalTo($startDate->copy()->addMonth())) {
//             // Первый месяц (неполный)
//             $monthlyDividendAmount = ($totalAmount * ($annualRate / 100)) / 12;
//             $monthlyDividendAmount = ($monthlyDividendAmount / $daysInMonth) * $daysCovered;
//         } elseif ($nextMonthlyPayment->greaterThanOrEqualTo($endDate)) {
//             // Последний месяц (неполный)
//             $monthlyDividendAmount = ($totalAmount * ($annualRate / 100)) / 12;
            
//             // Корректируем дни, чтобы избежать отрицательных значений
//             $daysRemaining = max($endDate->diffInDays($nextMonthlyPayment), 0) + 1;
//             $monthlyDividendAmount = ($monthlyDividendAmount / $daysInMonth) * $daysRemaining;
//         }

//         $totalAccrued += $monthlyDividendAmount;

//         $monthlyDividends[] = [
//             'date' => $adjustedPaymentDate->format('Y-m-d'),
//             'dividend' => round($monthlyDividendAmount, 2),
//             'total_accumulated' => round($totalAccrued, 2)
//         ];

//         // Следующий месяц
//         $nextMonthlyPayment->addMonth();
//     }

//     return $monthlyDividends;
// }
function calculateMonthlyDividends($contractStartDate, $contractEndDate, $currentDate, $totalAmount, $annualRate) {
    $startDate = Carbon::parse($contractStartDate);
    $endDate = Carbon::parse($contractEndDate);
    $currentDate = Carbon::parse($currentDate);

    $monthlyDividends = [];
    $totalAccrued = 0;

    // Дивиденды за месяц
    $monthlyDividendAmount = ($totalAmount * ($annualRate / 100)) / 12;

    // Начинаем начисления от даты начала договора
    $paymentDate = $startDate->copy();

    // Выплаты каждый месяц
    while ($paymentDate <= $endDate && $paymentDate <= $currentDate) {
        $totalAccrued += $monthlyDividendAmount;

        $monthlyDividends[] = [
            'date' => $paymentDate->format('Y-m-d'),
            'dividend' => round($monthlyDividendAmount, 2),
            'total_accumulated' => round($totalAccrued, 2)
        ];

        // Переходим к следующему месяцу (на ту же дату)
        $paymentDate->addMonth();
    }

    return $monthlyDividends;
}




function calculateAnnualDividendsContracts($contractStartDate, $contractEndDate, $currentDate, $totalAmount, $annualRate) {
    $startDate = Carbon::parse($contractStartDate);
    $endDate = Carbon::parse($contractEndDate);
    $currentDate = Carbon::parse($currentDate);

    $annualDividends = [];
    $totalAccrued = 0;

    // Выплата ежегодно в день старта договора
    $nextAnnualPayment = $startDate->copy()->addYear();

    // Пока не превысили дату окончания договора
    while ($nextAnnualPayment <= $endDate && $nextAnnualPayment <= $currentDate) {
        // Дивиденды за полный год
        $annualDividend = $totalAmount * ($annualRate / 100);

        $totalAccrued += $annualDividend;

        $annualDividends[] = [
            'date' => $nextAnnualPayment->format('Y-m-d'),
            'dividend' => round($annualDividend, 2),
            'total_accumulated' => round($totalAccrued, 2)
        ];

        // Переход на следующий год
        $nextAnnualPayment->addYear();
    }

    return $annualDividends;
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
