<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Application;
use App\Models\User;
use App\Models\Log;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    
    public function createAddApplication(){
        $user = Auth::user();
        $role = $user->role->title;
        if($role === 'admin'){
            $clients = User::whereHas('role', function($query) {
                $query->where('title', 'client'); // Фильтрация по роли 'client'
            })
            ->with(['userContracts' => function ($query) {
                $query->where('contract_status', true); // Выбираем только активные договоры
            }])
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'full_name' => $client->last_name. ' ' .$client->first_name. ' ' .$client->middle_name,
                    'user_contracts' => $client->userContracts ? $client->userContracts->toArray() : [], // Загружаем контракты
                ];
            });
            
        }else{
            $clients = $user->managedUsers->load(['userContracts' => function ($query) {
                $query->where('contract_status', true); // Выбираем только активные договоры
            }])
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'full_name' =>  $client->last_name. ' ' .$client->first_name. ' ' .$client->middle_name,
                    'user_contracts' => $client->userContracts ? $client->userContracts->toArray() : [], // Загружаем контракты
                ];
            });
        }
       
        return Inertia::render('AddApplication', [
            'role' => $role,
            'clients' => $clients,
        ]);
      }
      public function storeAddApplication(Request $request){
        //dd($request->all());
        $request->validate([
            'create_date' => ['required', 'date_format:Y-m-d'],
            'date_of_payments' => ['required', 'date_format:Y-m-d', 'after_or_equal:create_date'], // Дата платежа не должна быть раньше даты создания
            'sum' => ['required', 'integer'],
        ]);
        $user = Auth::user();
        $role = $user->role->title;
        /** @var User $user */
        // Проверка, что менеджер имеет право работать с пользователем
        if (!$user->managedUsers()->where('id', $request->user_id)->exists() && $role !== 'admin') {
            abort(403, 'У вас нет прав для создания заявки на этого пользователя.');
        }

        // Проверка, что контракт принадлежит менеджеру
        if (!$user->managerContracts()->where('id', $request->contract_id  )->exists() && $role !== 'admin') {
            abort(403, 'У вас нет прав для использования этого контракта.');
        }
        

        $application = Application::create([
            'create_date'=> $request->create_date,
            'user_id'=> $request->user_id,
            'manager_id'=> $request->manager_id,
            'contract_id'=> $request->contract_id,
            'condition'=>$request->condition,
            'status'=>$request->status,
            'type_of_processing'=>$request->type_of_processing,
            'date_of_payments'=>$request->date_of_payments,
            'sum'=>$request->sum,
        ]);
        Log::create([
            'model_id' => $application->user_id,
            'model_type' => Application::class,
            'change' => null ,
            'action' => "Создание заявки",
            'old_value' => null,
            'new_value' => 'Заявка No' . $application->id,
            'created_by' => Auth::id(),
        ]);
        $client = $application->user;
        $client->userNotifications()->create([
            'content'=> 'Создана заявки No' . $application->id ,
        ]);
        return redirect()->route($role . '.applications')->with('status', 'Заявка успешно создана!');
      }
    public function showApplication(Application $application){
        $user = Auth::user();
        $role = $user->role->title;
        return Inertia::render('ShowApplication', [
            'role' => $role,
            'application' => [
                'id' => $application->id,
                'create_date' => $application->create_date,
                'date_of_payments' => $application->date_of_payments,
                'condition' => $application->condition,
                'status' => $application->status,
                'type_of_processing' => $application->type_of_processing,
                'sum' => $application->sum,
                'user' => [
                    'id' => $application->user->id,
                    'full_name' => $application->user->first_name . ' ' . $application->user->last_name . ' ' . $application->user->middle_name,
                ],
                'contract' => [
                    'id' => $application->contract->id,
                    'contract_number' => $application->contract->contract_number,
                    'sum' => $application->contract->sum,
                    'create_date' => $application->contract->create_date,
                    'deadline' => $application->contract->deadline,
                    'procent' => $application->contract->procent,
                ]
            ],
        ]);
    }
    
}
