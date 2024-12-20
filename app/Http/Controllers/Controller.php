<?php

namespace App\Http\Controllers;

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
    $application->user->userTransactions()->create([
        'contract_id' => $application->contract_id,
        'manager_id' => $application->manager_id,
        'user_id' => $application->user_id,
        'date_transition' => $application->date_of_payments,
        'sum_transition' => $sum,
        'sourse' => $source,
    ]);
}
    
    
    
}
