<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ClientController extends Controller
{
   
    public function createProfile()
{
    $user = Auth::user();
    $role = $user->role->title;
     /** @var User $user */
  
    $userContracts = $user->userContracts()->get()->where('contract_status', true);

    
    $sum_all_contracts = $userContracts->sum('sum');

   
    $dividends = $userContracts->reduce(function ($carry, $contract) {
        $sum = $contract->sum ?? 0;  
        $procent = $contract->procent ?? 0;  
        
        $annual_dividends = $sum * ($procent / 100);  
      
        $createDate = $contract->create_date instanceof \Carbon\Carbon 
            ? $contract->create_date 
            : \Carbon\Carbon::parse($contract->create_date);
    
        $deadline = $contract->deadline instanceof \Carbon\Carbon 
            ? $contract->deadline
            : \Carbon\Carbon::parse($contract->deadline);
        
    
        return $carry + $this->calculateAccumulatedDividends(
            $createDate,
            $deadline,
            now(),
            $annual_dividends,  
            $contract->payments,
            $contract->last_payment_date
        );
    }, 0);
    
    $manager = $user->managers->first();
    $user_notification = $user->userNotifications()
    ->where('is_read', false)
    ->get()
    ->values();

 
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
            'dividends' => $dividends,
        ],
        'role' => $role,
        'notifications' => $user_notification,
        'status' => session('status'),
    ]);
}

public function showContracts()
{
    $user = Auth::user();
    $role = $user->role->title;
       /** @var User $user */
    
    $userContracts = $user->userContracts()->get()->where('contract_status', true);

  
    $sum_all_contracts = $userContracts->sum('sum');

   
    $dividends = $userContracts->reduce(function ($carry, $contract) {
        $sum = $contract->sum ?? 0;  
        $procent = $contract->procent ?? 0;  
        
      
        $annual_dividends = $sum * ($procent / 100);  
        
       
        $createDate = $contract->create_date instanceof \Carbon\Carbon 
            ? $contract->create_date 
            : \Carbon\Carbon::parse($contract->create_date);
    
        $deadline = $contract->deadline instanceof \Carbon\Carbon 
            ? $contract->deadline
            : \Carbon\Carbon::parse($contract->deadline);
        
        
        return $carry + $this->calculateAccumulatedDividends(
            $createDate,
            $deadline,
            now(),
            $annual_dividends,  
            $contract->payments,
            $contract->last_payment_date
        );
    }, 0);
   
    $user_notification = $user->userNotifications()
    ->where('is_read', false)
    ->get()
    ->values();
    $manager = $user->managers->first();
    $contracts = $user->userContracts()
        ->where('contract_status', true)
        ->get()
        ->map(function ($contract) {
            $term = $this->termOfTheContract($contract->create_date, $contract->deadline);
            if (in_array($contract->payments, ['Ежегодно', 'Ежеквартально'])) {
                $anualDividends = $this->calculateAnnualDividendsContracts($contract->create_date, $contract->deadline,$contract->sum, $contract->procent);
                $monthDividends = $this->calculateMonthlyDividends($contract->create_date, $contract->deadline,$contract->sum, $contract->procent);
                $weekDividends = $this->calculateWeeklyDividends($contract->create_date, $contract->deadline, $contract->sum, $contract->procent);
                $dayDividends = $this->calculateDailyDividends($contract->create_date, $contract->deadline, $contract->sum, $contract->procent);
            }else {
               
                $anualDividends = null;
                $monthDividends = null;
                $weekDividends = null;
                $dayDividends = null;
            }
           
            return [
                'id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'create_date' => $contract->create_date,
                'procent' => $contract->procent,
                'sum' => $contract->sum,
                'deadline' => $contract->deadline,
                'term' => $term,
                'created_at' => $contract->create_date,
                'updated_at' => $contract->updated_at,
                'anualDividends' => $anualDividends,
                'monthDividends' => $monthDividends,
                'weekDividends' => $weekDividends,
                'dayDividends' => $dayDividends,
                'agree_with_terms' => $contract->agree_with_terms
            ];
        });

    return Inertia::render('ContractsClient', [
        'role' => $role,
        'contracts' => $contracts,
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
            'dividends' => round($dividends, 2),
        ],
        'notifications' => $user_notification,
    ]);
}

    public function showBalanceTransactions(){
        $user = Auth::user();
        $role = $user->role->title;

        /** @var User $user */
        $transactions = $user->userTransactions()->get();
        $contracts = $user->userContracts()->where('contract_status', true)->get();
        $userContracts = $user->userContracts()->get()->where('contract_status', true);

       
    
        $sum_all_contracts = $userContracts->sum('sum');

       
        $dividends = $userContracts->reduce(function ($carry, $contract) {
            $sum = $contract->sum ?? 0;  
            $procent = $contract->procent ?? 0;  
            
           
            $annual_dividends = $sum * ($procent / 100);  
            
          
            $createDate = $contract->create_date instanceof \Carbon\Carbon 
                ? $contract->create_date 
                : \Carbon\Carbon::parse($contract->create_date);
        
            $deadline = $contract->deadline instanceof \Carbon\Carbon 
                ? $contract->deadline
                : \Carbon\Carbon::parse($contract->deadline);
            
           
            return $carry + $this->calculateAccumulatedDividends(
                $createDate,
                $deadline,
                now(),
                $annual_dividends, 
                $contract->payments,
                $contract->last_payment_date
            );
        }, 0);
        
        $user_notification = $user->userNotifications()
        ->where('is_read', false)
        ->get()
        ->values();
        $manager = $user->managers->first();
       
        return Inertia::render('BalanceTransactions', [
            'role' => $role,
            'transactions' => $transactions,
            'contracts' => $contracts ?? [],
            'balance' => [
                'main_sum' => $sum_all_contracts,
                'dividends' => round($dividends, 2),
            ],
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
                'dividends' => round($dividends, 2),
            ],
            'notifications' => $user_notification,
            
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
      
        $userContracts = $user->userContracts()->get()->where('contract_status', true);

       
        $sum_all_contracts = $userContracts->sum('sum');
    
      
        $dividends = $userContracts->reduce(function ($carry, $contract) {
            $sum = $contract->sum ?? 0;  
            $procent = $contract->procent ?? 0;  
            
         
            $annual_dividends = $sum * ($procent / 100);  
            
           
            $createDate = $contract->create_date instanceof \Carbon\Carbon 
                ? $contract->create_date 
                : \Carbon\Carbon::parse($contract->create_date);
        
            $deadline = $contract->deadline instanceof \Carbon\Carbon 
                ? $contract->deadline
                : \Carbon\Carbon::parse($contract->deadline);
            
            
            return $carry + $this->calculateAccumulatedDividends(
                $createDate,
                $deadline,
                now(),
                $annual_dividends,  
                $contract->payments,
                $contract->last_payment_date
            );
        }, 0);
      
        $user_notification = $user->userNotifications()
        ->where('is_read', false)
        ->get()
        ->values();
        $manager = $user->managers->first();                 
        
        return Inertia::render('Applications', [
            'role' => $role,
            'applications' => $applications,
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
                'dividends' => round($dividends, 2),
            ],
            'notifications' => $user_notification,
        ]);
    }
    public function showNotifications(){
        $user = Auth::user();
        $role = $user->role->title;
         /** @var User $user */
        $notifivcations = $user->userNotifications()->get();
        $userContracts = $user->userContracts()->get()->where('contract_status', true);

      
        $sum_all_contracts = $userContracts->sum('sum');
    
      
        $dividends = $userContracts->reduce(function ($carry, $contract) {
            $sum = $contract->sum ?? 0;  
            $procent = $contract->procent ?? 0;  
            
           
            $annual_dividends = $sum * ($procent / 100);  
            
           
            $createDate = $contract->create_date instanceof \Carbon\Carbon 
                ? $contract->create_date 
                : \Carbon\Carbon::parse($contract->create_date);
        
            $deadline = $contract->deadline instanceof \Carbon\Carbon 
                ? $contract->deadline
                : \Carbon\Carbon::parse($contract->deadline);
            
           
            return $carry + $this->calculateAccumulatedDividends(
                $createDate,
                $deadline,
                now(),
                $annual_dividends,  
                $contract->payments,
                $contract->last_payment_date
            );
        }, 0);
      
        $user_notification = $user->userNotifications()
        ->where('is_read', false)
        ->get()
        ->values();
        $manager = $user->managers->first();
        return Inertia::render('Notifications', [
            'role' => $role,
            'notifications' => $notifivcations,
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
                'dividends' => round($dividends, 2),
            ],
            'notification' => $user_notification,
        ]);
    }
    public function updateNotification(Request $request, Notification $notification){
       
        $request->validate([
           'is_read' => ['required', 'boolean'],
        ]);
        $notification->update($request->all());
        return redirect()->route('client.notification');
    }
}
