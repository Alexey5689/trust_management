<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
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
        return Inertia::render('Contracts', [
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
                            ];
                        });
        //dd($applications);
        return Inertia::render('Applications', [
            'role' => $role,
            'applications' => $applications,
        ]);
    }
}
