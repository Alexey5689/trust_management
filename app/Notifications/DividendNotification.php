<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class DividendNotification extends Notification
{
    use Queueable;

    public $contract;
    public $paymentDate;

    public function __construct($contract, $paymentDate)
    {
        $this->contract = $contract;
        $this->paymentDate = $paymentDate;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $daysRemaining = Carbon::now()->diffInDays($this->paymentDate, false);
        return (new MailMessage)
            ->subject('Напоминание о выплате дивидендов')
            ->view('mail.dividend_reminder', [
                'contract' => $this->contract,
                'paymentDate' => $this->paymentDate,
                'daysRemaining' => $daysRemaining, 
            ]);

    }
}
