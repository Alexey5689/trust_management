<?php

namespace App\Console\Commands;
use App\Models\User;
use App\Models\Log;
use Illuminate\Console\Command;
use App\Models\Contract;
use Carbon\Carbon;
use App\Notifications\DividendNotification;

class SendDividendReminderEmails extends Command
{
    protected $signature = 'notify:dividends';
    protected $description = 'Отправка уведомлений о предстоящих выплатах дивидендов';

    public function handle()
    {
        $today = Carbon::now();
        
        $contracts = Contract::where('contract_status', true)
        ->with(['user', 'manager'])
        ->get();

       
        foreach ($contracts as $contract) {
            $nextPaymentDate = $this->calculateNextPaymentDate($contract);
        
            if ($nextPaymentDate) {
                // Если дата следующего платежа через 7 дней
                if ($nextPaymentDate->isSameDay($today->copy()->addDays(7))) {
                    $this->sendDividendNotification($contract, $contract->manager, 'менеджеру', $nextPaymentDate);
                }
        
                // Если дата следующего платежа через 14 дней
                if ($nextPaymentDate->isSameDay($today->copy()->addDays(2))) {
                    $this->sendDividendNotification($contract, $contract->user, 'клиенту', $nextPaymentDate);
                }
            }
        }

        $this->info('Уведомления о дивидендах отправлены');
    }


    protected function sendDividendNotification($contract, $user, $role, $nextPaymentDate)
{
    try {
        // Отправка уведомления
        $user->notify(new DividendNotification($contract, $nextPaymentDate));

        // Логирование
        $this->logAndNotify($user, $contract, $role);

        Log::info('Уведомления отправлены', [
            'contract_id' => $contract->id,
            'user_id' => $contract->user_id,
            'manager_id' => $contract->manager_id,
            'next_payment_date' => $nextPaymentDate->toDateString(),
        ]);
    } catch (\Exception $e) {
        Log::error("Ошибка отправки уведомления {$role}: " . $e->getMessage(), [
            'contract_id' => $contract->id
        ]);
        $this->error("Ошибка отправки {$role}: " . $e->getMessage());
    }
}

    private function calculateNextPaymentDate($contract)
    {
        $lastPaymentDate = $contract->last_payment_date 
            ? Carbon::parse($contract->last_payment_date) 
            : Carbon::parse($contract->create_date);

        return match ($contract->payments) {
            'Ежеквартально' => $lastPaymentDate->addMonths(3),
            'Ежегодно' => $lastPaymentDate->addYear(),
            'По истечению срока' => Carbon::parse($contract->deadline),
            default => null,
        };
    }
    private function logAndNotify($user, $contract, $recipientType)
    {
        $user->userNotifications()->create([
            'title' => 'Письмо о выплате дивидендов',
            'content' => 'К вам на почту отправлено письмо о выплате дивидендов',
        ]);

        Log::create([
            'model_id' => $user->id,
            'model_type' => Contract::class,
            'change' => 'Письмо о выплате дивидендов',
            'action' => 'Отправлено на почту ' . $recipientType,
            'old_value' => null,
            'new_value' => 'Договор No' . $contract->contract_number,
            'created_by' => 1, // ID системного пользователя
        ]);
    }
}
