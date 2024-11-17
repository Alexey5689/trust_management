<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function createAddApplication(){
        $user = Auth::user();
        $role = $user->role->title;
        $clients = $user->managedUsers->load('userContracts')->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' => $client->first_name. ' ' .$client->last_name. ' ' .$client->middle_name,
                'user_contracts' => $client->userContracts ? $client->userContracts->toArray() : [], // Загружаем контракты
            ];
        });
        return Inertia::render('AddApplication', [
            'role' => $role,
            'clients' => $clients,
        ]);
      }
      public function storeAddApplication(Request $request){
        //dd($request->all());
        $request->validate([
            'create_date' => 'required|date_format:Y-m-d',
            'date_of_payments' => 'required|date_format:Y-m-d|after_or_equal:create_date', // Дата платежа не должна быть раньше даты создания
            'user_id' => 'required|exists:users,id', // Убедиться, что пользователь существует
            'contract_id' => 'required|exists:contracts,id', // Проверить существование контракта
            'condition' => 'required|string|max:255', // Проверка условия
            'status' => 'required|string|max:50', // Проверка статуса
            'type_of_processing' => 'required|string|max:255', // Проверка типа обработки
        ]);
        $user = Auth::user();
        $role = $user->role->title;
        /** @var User $user */
        // Проверка, что менеджер имеет право работать с пользователем
        if (!$user->managedUsers()->where('id', $request->user_id)->exists()) {
            abort(403, 'У вас нет прав для создания заявки на этого пользователя.');
        }

        // Проверка, что контракт принадлежит менеджеру
        if (!$user->managerContracts()->where('id', $request->contract_id)->exists()) {
            abort(403, 'У вас нет прав для использования этого контракта.');
        }
        $user->managerApplications()->create([
            'create_date'=> $request->create_date,
            'user_id'=> $request->user_id,
            'manager_id'=> $user->id,
            'contract_id'=> $request->contract_id,
            'condition'=>$request->condition,
            'status'=>$request->status,
            'type_of_processing'=>$request->type_of_processing,
            'date_of_payments'=>$request->date_of_payments,
        ]);
        return redirect(route($role . '.applications'))->with('success', 'Заявка успешно создана!');
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
    public function changeStatusApplication(Application $application){
        $user = Auth::user();
        $role = $user->role->title;
        return Inertia::render('ChangeStatusApplication', [
            'role' => $role,
            'application' => [
                'id' => $application->id,
                'status' => $application->status,
            ],
        ]);
    }
    public function updateStatusApplication(Request $request, Application $application){
        // dd($request->all());
        $user = Auth::user();
        $role = $user->role->title;
        $application->status = $request->status;
        $application->save();
        return redirect(route($role . '.applications'))->with('success', 'Статус заявки успешно изменен!');
    }
}
