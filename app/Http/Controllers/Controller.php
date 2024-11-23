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
}
