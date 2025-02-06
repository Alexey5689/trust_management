<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Application;
use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    
      public function storeAddApplication(Request $request){
       
        $user = Auth::user();
        $role = $user->role->title;
        /** @var User $user */
        $request->validate([
            'condition' => ['required', 'string'],
            'user_id' => ['required', 'integer'],
            'contract_id' => ['required', 'integer'],
            'create_date' => ['required', 'date_format:Y-m-d'],
            'date_of_payments' => ['required', 'date_format:Y-m-d', 'after_or_equal:create_date'], // Дата платежа не должна быть раньше даты создания
            'dividends' => [ 'nullable', 'numeric' ], // Сумма должна быть больше 0.01
            'dividendsAfterExpiration' => [ 'nullable', 'numeric' ], // Сумма должна быть больше 0.01
        ]);
        DB::beginTransaction();
        try {
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
                    'dividends'=>$request->dividends,
                    'dividendsAfterExpiration'=>$request->dividendsAfterExpiration
                ]);
            
                Log::create([
                    'model_id' => $application->user_id,
                    'model_type' => Application::class,
                    'change' =>  $application->type_of_processing,
                    'action' => "Создание заявки",
                    'old_value' => null,
                    'new_value' => 'Заявка № ' . $application->id,
                    'created_by' => Auth::id(),
                ]);
                $client = $application->user;
                $contract = $application->contract;
                if ($request->available_balance) {
                    $balance = $request->available_balance;
                    $currentBalance = $contract->avaliable_dividends;
                    $newBalance = $currentBalance + $balance;
                    $contract->update([
                        'avaliable_dividends' => $newBalance,
                    ]);
                }
                $contract->update([
                    'is_aplication' => true
                ]);
                
                $client->userNotifications()->create([
                    'title'=> 'Заявка',
                    'content'=> 'Создана заявка № ' . $application->id . ' на договор № ' . $application->contract->contract_number,
                ]);
               
                DB::commit();
                return redirect()->route($role . '.applications')->with('status', ['Успех!', 'Заявка успешно создана!']);
                    
            }catch(\Exception $e){
                DB::rollBack();
                return redirect()->route($role . '.applications') 
                ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
            }
      }
    
    
      public function showApplication(Application $application){
       
        $term = $this->termOfTheContract($application->contract->create_date, $application->contract->deadline);
       
        $dividends = match ($application->contract->payments) {
            'Ежеквартально' => $application->contract->sum * ($application->contract->procent / 100) * $term /  $term * 4 ,
            'Ежегодно' => $application->contract->sum * ($application->contract->procent / 100) * $term /  $term * 1 ,
            'По истечению срока' => $application->dividends,
            default => null,
        };
        return response()->json([
            'application' => [
                'id' => $application->id,
                'create_date' => $application->create_date,
                'date_of_payments' => $application->date_of_payments,
                'condition' => $application->condition,
                'status' => $application->status,
                'type_of_processing' => $application->type_of_processing,
                'sum' => $application->sum,
                'dividends' => $application->dividends,
                'user' => [
                    'id' => $application->user->id,
                    'full_name' =>$application->user->last_name  . ' ' . $application->user->first_name . ' ' . $application->user->middle_name,
                ],
                'contract' => [
                    'id' => $application->contract->id,
                    'contract_number' => $application->contract->contract_number,
                    'sum' => $application->contract->sum,
                    'create_date' => $application->contract->create_date,
                    'term' => $term,
                    'procent' => $application->contract->procent,
                    'dividends' =>$dividends,
                    'agree_with_terms' => $application->contract->agree_with_terms
                ]
            ],
        ]);
    }
    
}
