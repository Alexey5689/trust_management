<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Notifications\PasswordEmail;


class ResetPasswordController extends Controller
{
    public function resetPassword(User $user){

        log::create([
            'model_id' => $user->id,
            'model_type' => User::class,
            'change' => 'password',
            'action' => 'reset password',
            'old_value' => null,
            'new_value' => null,
            'created_by' => Auth::id(),
        ]);
        // Генерация токена сброса пароля
       $token = Password::createToken($user);
       // Отправка уведомления с токеном на email менеджера
       $user->notify(new PasswordEmail($token, $user->email));
       // Flash-сообщение об успешной отправке
       return redirect('/')->with('success', 'Ссылка для сброса пароля отправлена на email.');
   }
}
