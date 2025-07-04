<?php

namespace App\Http\Controllers;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
      
        'Ежеквартально' => $this->calculateQuarterlyDividends($contractStartDate, $currentDate, $paymentAmount, $lastPaymentDate ),
        'Ежегодно' => $this->calculateAnnualDividends($contractStartDate,$contractDeadline, $currentDate, $paymentAmount, $lastPaymentDate),
        default => 0,
    };
}





protected function calculateAnnualDividends($contractStartDate, $contractDeadline, $currentDate, $paymentAmount, $lastPaymentDate) {
    $startDate = $lastPaymentDate ? new DateTime($lastPaymentDate) : new DateTime($contractStartDate);
    $currentDate = new DateTime($currentDate);
    
    // Получаем конец года (12 месяцев спустя)
    $endDate = (clone $startDate)->modify('+1 year');

    // Проверка, високосный ли год
    $daysInYear = $startDate->format('L') == 1 ? 366 : 365;

    // Дни с начала договора до текущей даты
    $daysSinceStart = ($currentDate >= $startDate)? $startDate->diff($currentDate)->days : 0;

    //dd( $startDate, $daysSinceStart, $currentDate);
    // Дивиденды за 1 день
    $dailyDividend = $paymentAmount / $daysInYear;

    // Пропорциональные дивиденды за прошедшие дни
    $accruedDividends = $daysSinceStart * $dailyDividend;


    return round($accruedDividends);
}



protected function calculateQuarterlyDividends($contractStartDate, $currentDate, $paymentAmount, $lastPaymentDate) {   
    $startDate = $lastPaymentDate ? new DateTime($lastPaymentDate) : new DateTime($contractStartDate);
    $currentDate = new DateTime($currentDate);
    
    // Рассчитываем квартальные выплаты
    $quarterAmount = $paymentAmount / 4;
    
    // Определяем дату начала текущего квартала
    $quarterStart = clone $startDate;
    while ($quarterStart < $currentDate) {
        $quarterStart->modify('+3 months');
    }
    $quarterStart->modify('-3 months');  // Возвращаемся к началу текущего квартала

    // Определяем конец квартала
    $quarterEnd = clone $quarterStart;
    $quarterEnd->modify('+3 months');

    // Проверяем количество дней в квартале
    $quarterDays = $quarterStart->diff($quarterEnd)->days;
    
    // Страхуемся от деления на 90 дней, устанавливая минимум 91
    $quarterDays = max(91, $quarterDays);
   
    // Дивиденды за 1 день квартала
    $dailyDividend = $quarterAmount / $quarterDays;
   
    // Считаем, сколько дней прошло с начала квартала
    $daysSinceQuarterStart = $quarterStart->diff($currentDate)->days;

    // Рассчитываем накопленные дивиденды
    $accruedDividends = $daysSinceQuarterStart * $dailyDividend;

    return round($accruedDividends);
}




function calculateDailyDividends($contractStartDate, $contractEndDate, $totalAmount, $annualRate) {
    $startDate = Carbon::parse($contractStartDate);
    $endDate = Carbon::parse($contractEndDate);

    $dailyDividends = [];

    for ($date = $startDate->copy()->addDay(); $date <= $endDate; $date->addDay()) {
        // Фиксированное количество дней в году — 365
        $daysInYear = 365;

        // Дивиденды за день
        $dailyDividend = ($totalAmount * ($annualRate / 100)) / $daysInYear;

        $dailyDividends[] = [
            'date' => $date->format('Y-m-d'),
            'dividend' => round($dailyDividend, 2),
        ];
    }

    return $dailyDividends;
}

function calculateWeeklyDividends($contractStartDate, $contractEndDate, $totalAmount, $annualRate) {
    $startDate = Carbon::parse($contractStartDate);
    $endDate = Carbon::parse($contractEndDate);

    $weeklyDividends = [];


    // Дивиденды за год и за неделю
    $annualDividend = $totalAmount * ($annualRate / 100);
    $weeklyDividendAmount = $annualDividend / 52;

    // Количество лет
    $years = $startDate->diffInYears($endDate);
    
    // Полные недели за весь срок
    $totalWeeks = $years * 52;
    
    // Начисление дивидендов по неделям
    for ($i = 1; $i <= $totalWeeks; $i++) {
      

        $weeklyDividends[] = [
            'date' => $startDate->copy()->addWeeks($i)->format('Y-m-d'),
            'dividend' => round($weeklyDividendAmount, 2),
           
        ];
    }

    // Остаток дней (если есть)
    $remainingDays = $startDate->diffInDays($endDate) % 7;
    if ($remainingDays > 0) {
        $finalDividend = ($weeklyDividendAmount / 7) * $remainingDays;
       

        $weeklyDividends[] = [
            'date' => $endDate->format('Y-m-d'),
            'dividend' => round($finalDividend, 2),
            
        ];
    }

    return $weeklyDividends;
}







function calculateMonthlyDividends($contractStartDate, $contractEndDate, $totalAmount, $annualRate) {
    $startDate = Carbon::parse($contractStartDate);
    $endDate = Carbon::parse($contractEndDate);

    $monthlyDividends = [];
   
    // Дивиденды за месяц
    $monthlyDividendAmount = ($totalAmount * ($annualRate / 100)) / 12;

    // Начинаем начисления с даты подписания + 1 месяц
    $paymentDate = $startDate->copy()->addMonth();

    while ($paymentDate <= $endDate) {
     

        $monthlyDividends[] = [
            'date' => $paymentDate->format('Y-m-d'),
            'dividend' => round($monthlyDividendAmount, 2),
           
        ];

        // Переходим к следующему месяцу
        $paymentDate->addMonth();
    }

    return $monthlyDividends;
}


function calculateAnnualDividendsContracts($contractStartDate, $contractEndDate, $totalAmount, $annualRate) {
    $startDate = Carbon::parse($contractStartDate);
    $endDate = Carbon::parse($contractEndDate);

    $annualDividends = [];
    $totalAccrued = 0;

    // Выплата ежегодно в день старта договора
    $nextAnnualPayment = $startDate->copy()->addYear();

    // Пока не достигли даты окончания договора
    while ($nextAnnualPayment <= $endDate) {
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
       $contract = Contract::find($application->contract_id);
        if ($isCancelled) {
            $application->update(['status' => 'Отменена']);
            $contract->update(['is_aplication' => false]);
            return 'Заявка отменена.';
        }

        $newStatus = match ($application->status) {
            'В обработке' => 'Согласована',
            'Согласована' => 'Исполнена',
            default => $application->status,
        };

        // Обновляем статус заявки
        $application->update(['status' => $newStatus]);
        $message = 'Статус заявки успешно изменен!';
        // Если заявка переходит в "Исполнена", обрабатываем транзакцию и договор
        if ($newStatus === 'Исполнена') {
            DB::beginTransaction();
            try {
                 // Завершаем договор и создаём транзакцию
                $contract->update(['contract_status' => false, 'is_aplication' => false]);
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
                    'action' => 'Закрытие договора № ' . $contract->contract_number,
                    'old_value' => 'Активный',
                    'new_value' => 'Неактивный',
                    'created_by' => Auth::id(),
                ]);
                $user->userNotifications()->create([
                    'title' => 'Закрытие договора',
                    'content'=> 'Договор № ' . $contract->contract_number . 'был закрыт.',
                ]);
                DB::commit();
                $message = 'Статус заявки успешно изменен!';
            }
            catch (\Exception $e) {
                DB::rollBack();
                $message = 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже';
            }
        }
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
        // Отмена заявки
        if ($isCancelled) {
            $application->update(['status' => 'Отменена']);
            $contract->update(['avaliable_dividends' => null, 'is_aplication' => false]);
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
       
        // Обновляем сумму по договору и сбрасываем доступные дивиденды
        // Если заявка исполнена, создаём транзакцию
        if ($newStatus === 'Исполнена') {
            $term = $this->termOfTheContract($contract->create_date, $contract->deadline);
            $isExpired = now()->greaterThanOrEqualTo(Carbon::parse($contract->deadline)->subDays(7));
            $user=User::find($contract->user_id);
            $manager=User::find($contract->manager_id);
            DB::beginTransaction();
            try{
                if($isExpired){
                    $oldCrateDate = $contract->create_date;
                    $contract->update([
                        'sum' => $mainSum + $avalible_dividends,
                        'create_date'=> $contract->deadline,
                        'deadline' => Carbon::parse($contract->deadline)->addYear($term),
                        'avaliable_dividends' => null,
                        'last_payment_date' => null,
                        'is_aplication' => false,
                    ]);
                    Log::create([
                        'model_id' => $contract->user_id,
                        'model_type' => Contract::class,
                        'change' => 'Пролонгация договора',
                        'action' => 'Продления срока договора',
                        'old_value' => $oldCrateDate,
                        'new_value' => $contract->create_date,
                        'created_by' => Auth::id(),
                    ]);
                     // Создание уведомлений для пользователя и менеджера
                    $content = $term === 1 ? $term . ' год' : $term . ' года';
                    $user->userNotifications()->create([
                        'title' => 'Пролонгация договора',
                        'content' => 'Продлен срок договора № ' . $contract->contract_number . ' на ' . $content,
                    ]);
                
                    $manager->userNotifications()->create([
                        'title' => 'Пролонгация договора',
                        'content' => 'Продлен срок договора № ' . $contract->contract_number . ' на ' . $content,
                    ]);
                }
                else{
                    $lastPaymentDate = $contract->last_payment_date ?? $contract->create_date;
                    $nextPaymentDate = match ($contract->payments) {
                        'Ежеквартально' => Carbon::parse($lastPaymentDate)->addMonths(3),
                        'Ежегодно' => Carbon::parse($lastPaymentDate)->addYear(),
                        default => null,
                    };
                    $contract->update([
                        'sum' => $mainSum + $avalible_dividends,
                        'last_payment_date' => $nextPaymentDate,
                        'avaliable_dividends' =>  null,
                        'is_aplication' => false,
                    ]);
                }
                $this->createTransaction($application, $avalible_dividends, 'Договор');
                Log::create([
                    'model_id' => $contract->user_id,
                    'model_type' => Transaction::class,
                    'change' => 'Новая транзакция',
                    'action' => 'Создание транзакции',
                    'old_value' => '',
                    'new_value' => 'Сумма: ' . round($avalible_dividends,0),
                    'created_by' => Auth::id(),
                ]);
                $user->userNotifications()->create([
                    'title' => 'Создание транзакции',
                    'content'=> 'Создана транзакция на сумму: ' . round($avalible_dividends, 0),
                ]);
                $this->createTransaction($application, $application->dividends, 'Заявка');
                Log::create([
                    'model_id' => $contract->user_id,
                    'model_type' => Transaction::class,
                    'change' => 'Новая транзакция',
                    'action' => 'Создание транзакции',
                    'old_value' => '',
                    'new_value' => 'Сумма: ' . round($application->dividends, 0),
                    'created_by' => Auth::id(),
                ]);
                $user->userNotifications()->create([
                    'title' => 'Создание транзакции',
                    'content'=> 'Создана транзакция на сумму: ' . round($application->dividends, 0),
                ]);

                DB::commit();
                $message = 'Статус заявки успешно изменен!';

            }
            catch (\Exception $e) {
                DB::rollBack();
                $message = 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже';
            }
           
        }
        $message = 'Статус заявки успешно изменен!';
        return $message;
    }

    protected function fullPayoutDividends($application, $isCancelled = false)
    {
        $contract = Contract::find($application->contract_id);

        if ($isCancelled) {
            $application->update(['status' => 'Отменена']);
            $contract->update(['avaliable_dividends' => null, 'is_aplication' => false]);
            return 'Заявка отменена.';
        }
        //dd($application->status);
        $newStatus = match ($application->status) {
            'В обработке' => 'Согласована',
            'Согласована' => 'Исполнена',
            default => $application->status,
        };
        $application->update(['status' => $newStatus]);
        

        if ($newStatus === 'Исполнена') {
            $term = $this->termOfTheContract($contract->create_date, $contract->deadline);
            $isExpired = now()->greaterThanOrEqualTo(Carbon::parse($contract->deadline)->subDays(7));
            $user=User::find($contract->user_id);
            $manager=User::find($contract->manager_id);
            DB::beginTransaction();
            try{

                if ($isExpired) {
                    $oldCrateDate = $contract->create_date;
                    $contract->update([
                        'create_date'=> $isExpired ? $contract->deadline : $contract->create_date,
                        'deadline' => Carbon::parse($contract->deadline)->addYear($term),
                        'last_payment_date' => null,
                        'is_aplication' => false,
                    ]);
                    Log::create([
                        'model_id' => $contract->user_id,
                        'model_type' => Contract::class,
                        'change' => 'Пролонгация договора',
                        'action' => 'Продления срока договора',
                        'old_value' => $oldCrateDate,
                        'new_value' => $contract->create_date,
                        'created_by' => Auth::id(),
                    ]);
                    $content = $term === 1 ? $term . ' год' : $term . ' года';
                    $user->userNotifications()->create([
                        'title' => 'Пролонгация договора',
                        'content' => 'Продлен срок договора № ' . $contract->contract_number . ' на ' . $content,
                    ]);
                
                    $manager->userNotifications()->create([
                        'title' => 'Пролонгация договора',
                        'content' => 'Продлен срок договора № ' . $contract->contract_number . ' на ' . $content,
                    ]);
                }else{
                    $lastPaymentDate = $contract->last_payment_date ?? $contract->create_date;
                    $nextPaymentDate = match ($contract->payments) {
                        'Ежеквартально' => Carbon::parse($lastPaymentDate)->addMonths(3),
                        'Ежегодно' => Carbon::parse($lastPaymentDate)->addYear(),
                        default => null,
                    };
                    $contract->update([
                        'last_payment_date' => $nextPaymentDate,
                        'is_aplication' => false,
                    ]);
                }
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
                    'content'=> 'Создана транзакция на сумму: ' . round($application->dividends,0),
                ]);
                DB::commit();
                $message = 'Статус заявки успешно изменен!';

            }
            catch (\Exception $e) {
                DB::rollBack();
                $message = 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже';
            }
            

        }
        $message = 'Статус заявки успешно изменен!';
        return $message;

    }
    protected function fullPayout($application, $isCancelled = false)
    {
        $contract = Contract::find($application->contract_id);

        if ($isCancelled) {
            $application->update(['status' => 'Отменена']);
            $contract->update(['avaliable_dividends' => null, 'is_aplication' => false]);
            return 'Заявка отменена.';
        }
        $newStatus = match ($application->status) {
            'В обработке' => 'Согласована',
            'Согласована' => 'Исполнена',
            default => $application->status,
        };
        $application->update(['status' => $newStatus]);
        if ($newStatus === 'Исполнена') {
            DB::beginTransaction();
            try{
                $contract->update([
                    'contract_status' => false,
                    'is_aplication' => false,
                    'avaliable_dividends' => null
                ]);
                $this->createTransaction($application, $application->sum + $application->dividends, 'Заявка');
                $user=User::find($contract->user_id);
                Log::create([
                    'model_id' => $contract->user_id,
                    'model_type' => Contract::class,
                    'change' => 'Смена статуса договора',
                    'action' => 'Закрытие договора № ' . $contract->contract_number,
                    'old_value' => 'Активный',
                    'new_value' => 'Неактивный',
                    'created_by' => Auth::id(),
                ]);
                $user->userNotifications()->create([
                    'title' => 'Договор',
                    'content'=> 'Договор № ' . $contract->contract_number . ' был закрыт',
                ]);
                DB::commit();
                $message = 'Статус заявки успешно изменен!';

            }catch (\Exception $e) {
                DB::rollBack();
                $message='Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже';
            }
           
            
        }
        $message = 'Статус заявки успешно изменен!';
        return $message;
    }


        
        
    
}
