<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class DeleteController extends Controller
{
       
      

    public function deleteUser(User $user): RedirectResponse
    {
        try {
            // Деактивируем пользователя
            $user->update([
                'active' => false,
            ]);
    
            // Если удаляемый пользователь сейчас в системе — разлогиниваем его
            if (Auth::check() && Auth::id() === $user->id) {
                Auth::logout();
                Session::invalidate(); // Очищаем сессию
                Session::regenerateToken();
            }
    
            Log::create([
                'model_id' => $user->id,
                'model_type' => User::class,
                'change' => "Смена статуса пользователя",
                'action' => 'Удаление пользователя',
                'old_value' => 'Активный',
                'new_value' => 'Деактивирован',
                'created_by' => Auth::id(),
            ]);
    
            return redirect(route('admin.users'))->with('status', ['Успех!', 'Аккаунт пользователя успешно деактивирован']);
    
        } catch (\Exception $e) {
            return redirect(route('admin.users'))->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
        }
    }

      public function deleteContract(Contract $contract): RedirectResponse
      {
          
        try {
            $contract->update(['contract_status' => false]);
            $user = User::find($contract->user_id);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Contract::class,
                'change' => "Смена статуса договора ",
                'action' => 'Удаление договора № ' . $contract->contract_number,
                'old_value' => 'Активный',
                'new_value' => 'Деактивирован',
                'created_by' => Auth::id(),
            ]);
            $user->userNotifications()->create([
                'title' => 'Договор',
                'content' => 'Договор  ' . $contract->contract_number . ' удален',
            ]);
          return redirect(route('admin.contracts'))->with('success', ['успех', 'Договор успешно деактивирован'] );
        }catch (\Exception $e) {
            return redirect(route('admin.contracts'))
            ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
        }
        
    }
}
