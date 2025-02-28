<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\SendDividendReminderEmails;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('notify:dividends', function () {
    $this->call(SendDividendReminderEmails::class); // 🚀 Запускает класс команды
    $this->comment('✅ Уведомления успешно отправлены!');
})->purpose('Отправить уведомления о выплатах дивидендов');

//app(Schedule::class)->command('notify:dividends')->dailyAt('00:00'); // Выполнять каждый день в 00:00
