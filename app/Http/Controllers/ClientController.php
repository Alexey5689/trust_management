<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    // public function createProfile(){
    //     $user = Auth::user();
    //     $role = $user->role->title;
    //     // dd($user, $role);
    //       /** @var User $user */
    //     $sum_all_contracts = 0;
    //     $dividends = 0;
    //     $quarter_dividends = 0;

    //     $sum_all_contracts += $user->userContracts()->sum('sum');
    //     $manager = $user->userContracts()->with('manager')->first()->manager ?? null;
    //     $quarter_dividends += $user->userContracts()->get()->reduce(function ($carry, $contract) {
    //         return $carry + ($contract->sum * ($contract->procent / 100) * $this->termOfTheContract($contract->create_date, $contract->deadline) / $contract->number_of_payments);
    //     }, 0);
    //     foreach ($user->userContracts()->get() as $contract) {
    //         $dividends += $this->calculateAccumulatedDividends($contract->create_date, now(), $quarter_dividends);
    //     }
       
    //     return Inertia::render('Profile', [        
    //         'user' => [
    //             'id' => $user->id,
    //             'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
    //             'email' => $user->email,
    //             'phone_number' => $user->phone_number,
    //             'manager' =>  $manager->first_name . ' ' .$manager->last_name,
    //             'managerEmail' => $manager->email,
    //             'main_sum' => $sum_all_contracts,
    //             'dividends' => $dividends
    //         ],
    //         'role' => $role,
    //         'status' => session('status'),
    //     ]);
    // }
    public function createProfile()
{
    $user = Auth::user();
    $role = $user->role->title;
     /** @var User $user */
    // Загружаем контракты пользователя
    $userContracts = $user->userContracts()->get();

    // Сумма всех контрактов
    $sum_all_contracts = $userContracts->sum('sum');

    // Рассчитываем накопленные дивиденды
    $dividends = $userContracts->reduce(function ($carry, $contract) {
        $quarter_dividends = $contract->sum * ($contract->procent / 100);
        return $carry + $this->calculateAccumulatedDividends($contract->create_date, now(), $quarter_dividends);
    }, 0);

    // Получаем менеджера
    $manager = $userContracts->first()->manager ?? null;

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
            'dividends' => round($dividends, 2), // Округляем до 2 знаков
        ],
        'role' => $role,
        'status' => session('status'),
    ]);
}

    public function showContracts(){
        $user = Auth::user();
        $role = $user->role->title;
         /** @var User $user */
        $contracts = $user->userContracts()->get()->map(function ($contract) {
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
        $contracts = $user->userContracts()->get();
        // dd($transactions);
        return Inertia::render('BalanceTransactions', [
            'role' => $role,
            'transactions' => $transactions,
            'contracts' => $contracts
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
}
