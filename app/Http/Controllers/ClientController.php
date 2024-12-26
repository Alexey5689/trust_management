<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
   
    public function createProfile()
{
    $user = Auth::user();
    $role = $user->role->title;
     /** @var User $user */
    // Загружаем контракты пользователя
    $userContracts = $user->userContracts()->get()->where('contract_status', true);

    // Сумма всех контрактов
    $sum_all_contracts = $userContracts->sum('sum');

    // Рассчитываем накопленные дивиденды
    $dividends = $userContracts->reduce(function ($carry, $contract) {
        $sum = $contract->sum ?? 0;  
        $procent = $contract->procent ?? 0;  
        
        // Рассчитываем годовые дивиденды
        $annual_dividends = $sum * ($procent / 100);  
        
        // Приводим даты к Carbon
        $createDate = $contract->create_date instanceof \Carbon\Carbon 
            ? $contract->create_date 
            : \Carbon\Carbon::parse($contract->create_date);
    
        $deadline = $contract->deadline instanceof \Carbon\Carbon 
            ? $contract->deadline
            : \Carbon\Carbon::parse($contract->deadline);
        
        // Передаём годовые дивиденды в метод
        return $carry + $this->calculateAccumulatedDividends(
            $createDate,
            $deadline,
            now(),
            $annual_dividends,  // Передаём годовые дивиденды!
            $contract->payments,
            $contract->last_payment_date
        );
    }, 0);
    // dd($dividends);
    // Получаем менеджера
    $manager = $user->managers->first();

    return Inertia::render('Profile', [
        'user' => [
            'id' => $user->id,
            'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'manager' => $manager 
                ? $manager->first_name . ' ' . $manager->last_name 
                : 'Менеджер не назначен',
            'managerEmail' => $manager ? $manager->email : '—',
            'main_sum' => $sum_all_contracts,
            'dividends' => round($dividends, 2),// Округляем до 2 знаков
        ],
        'role' => $role,
        'status' => session('status'),
    ]);
}

public function showContracts()
{
    $user = Auth::user();
    $role = $user->role->title;
       /** @var User $user */
    // Фильтруем по статусу на уровне базы данных
    $contracts = $user->userContracts()
        ->where('contract_status', true)
        ->get()
        ->map(function ($contract) {
            return [
                'id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'create_date' => $contract->create_date,
                'procent' => $contract->procent,
                'sum' => $contract->sum,
                'deadline' => $contract->deadline,
            ];
        });

    return Inertia::render('ContractsClient', [
        'role' => $role,
        'contracts' => $contracts
    ]);
}

    public function showBalanceTransactions(){
        $user = Auth::user();
        $role = $user->role->title;

        /** @var User $user */
        $transactions = $user->userTransactions()->get();
        $contracts = $user->userContracts()->where('contract_status', true)->get();
        $userContracts = $user->userContracts()->get()->where('contract_status', true);

        // Сумма всех контрактов
        $sum_all_contracts = $userContracts->sum('sum');
    
        // Рассчитываем накопленные дивиденды
        $dividends = $userContracts->reduce(function ($carry, $contract) {
            $sum = $contract->sum ?? 0;  
            $procent = $contract->procent ?? 0;  
            
            // Рассчитываем годовые дивиденды
            $annual_dividends = $sum * ($procent / 100);  
            
            // Приводим даты к Carbon
            $createDate = $contract->create_date instanceof \Carbon\Carbon 
                ? $contract->create_date 
                : \Carbon\Carbon::parse($contract->create_date);
        
            $deadline = $contract->deadline instanceof \Carbon\Carbon 
                ? $contract->deadline
                : \Carbon\Carbon::parse($contract->deadline);
            
            // Передаём годовые дивиденды в метод
            return $carry + $this->calculateAccumulatedDividends(
                $createDate,
                $deadline,
                now(),
                $annual_dividends,  // Передаём годовые дивиденды!
                $contract->payments,
                $contract->last_payment_date
            );
        }, 0);
        // dd($transactions);
        return Inertia::render('BalanceTransactions', [
            'role' => $role,
            'transactions' => $transactions,
            'contracts' => $contracts ?? [],
            'balance' => [
                'main_sum' => $sum_all_contracts,
                'dividends' => round($dividends, 2),
            ]
            
        ]);
    }

    public function showApplications(){
        $user = Auth::user();
        $role = $user->role->title;
         /** @var User $user */
        $applications = $user ->userApplications()
                        ->with('user',  'contract')
                        ->get()
                        ->map(function ($application) {
                            return [
                                'id' => $application->id,
                                'user_id' => $application->user_id,
                                'full_name' => $application->user->first_name. ' ' .$application->user->last_name. ' ' .$application->user->middle_name,
                                'contract_number' => $application->contract->contract_number,
                                'condition' => $application->condition,
                                'status' => $application->status,
                                'type_of_processing' => $application->type_of_processing,
                                'date_of_payments' => $application->date_of_payments,
                                'create_date' => $application->create_date,
                                'sum' => $application->sum,
                                'dividends'=> $application->dividends
                            ];
                        });
        //dd($applications);
        return Inertia::render('Applications', [
            'role' => $role,
            'applications' => $applications,
        ]);
    }
    public function showNotifications(){
        $user = Auth::user();
        $role = $user->role->title;
         /** @var User $user */
        $notifivcations = $user->userNotifications()->get();
        return Inertia::render('Notifications', [
            'role' => $role,
            'notifications' => $notifivcations,
        ]);
    }
}
