<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;

class DeleteController extends Controller
{
       
      // Удаление user
      public function deleteUser(User $user): RedirectResponse
      {
        //dd($user);
          try {
            $user->update(['active' => false]);
            //dd($user);
            Log::create([
                  'model_id' => $user->id,
                  'model_type' => User::class,
                  'change' => "Смена статуса пользователя",
                  'action' => 'Удаление пользователя',
                  'old_value' => 'Активный',
                  'new_value' => 'Удален',
                  'created_by' => Auth::id(),
              ]);
              return redirect(route('admin.users'))->with('status', ['Успех!', 'Статус пользователя изменен на неактивный'] );
            
          } catch (\Exception $e) {
              return redirect(route('admin.users'))->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
          }
         
         
      }
      public function deleteContract(Contract $contract): RedirectResponse
      {
          //dd($contract);
        try {
            $contract->update(['contract_status' => false]);
            $user = User::find($contract->user_id);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Contract::class,
                'change' => "Смена статуса договора ",
                'action' => 'Удаление договора No ' . $contract->contract_number,
                'old_value' => 'Активный',
                'new_value' => 'Неактивный',
                'created_by' => Auth::id(),
            ]);
            $user->userNotifications()->create([
                'title' => 'Договор',
                'content' => 'Договор No ' . $contract->contract_number . ' удален',
            ]);
          return redirect(route('admin.contracts'))->with('success', ['успех', 'Статус контракта изменен на неактивный'] );
        }catch (\Exception $e) {
            return redirect(route('admin.contracts'))
            ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
        }
        
    }
}
