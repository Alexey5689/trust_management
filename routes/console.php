<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('notify:dividends', function () {
    $this->comment('Уведомления отправлены!');
})->purpose('Отправить уведомления о выплатах дивидендов');

app(Schedule::class)->command('notify:dividends')
    ->dailyAt('09:00'); // Выполнять каждый день в 9 утра