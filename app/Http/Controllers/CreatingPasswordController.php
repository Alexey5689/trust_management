<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\Password as PasswordFacade;
use App\Models\User;
use App\Models\Log;

class CreatingPasswordController extends Controller
{

    public function create(Request $request): Response
    {

        return Inertia::render('CreatingPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Валидация только нового пароля и его подтверждения
         $validated = $request->validate([
            'email' => ['required','string','email'],
            'token' => 'required',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            
        ]);

        $status = PasswordFacade::reset(
            $validated,
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->active = true;
                $user->save();
                Log::create([
                    'model_id' => $user->id,
                    'model_type' => User::class,
                    'change' => 'Активация пользователя',
                    'action' => 'Создание пароля пользователем' ,
                    'old_value' => "*******",
                    'new_value' =>  "*******",
                    'created_by' => $user->id, // ID самого пользователя
                ]);
            }
        );
       
         // Если сброс пароля прошел успешно
        if ($status === PasswordFacade::PASSWORD_RESET) {
            return redirect(route('login'))->with('status', ['Успех!', 'Пароль успешно установлен']);
        }

        // В случае ошибки возвращаем пользователя обратно с сообщением об ошибке
        return back()->withErrors(['email' => [__($status)]]);
        }
}

