<?php

namespace App\Http\Controllers;

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
}
