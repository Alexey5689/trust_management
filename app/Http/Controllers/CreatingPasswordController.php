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
        //dd($request->all());
        // Валидация только нового пароля и его подтверждения
         $validated = $request->validate([
            'email' => 'required|email',
            'password' => ['required', Password::defaults(), 'confirmed'],
            'token' => 'required'
        ]);

        $status = PasswordFacade::reset(
            $validated,
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->active = true;
                $user->save();
            }
        );
        //dd($status);
         // Если сброс пароля прошел успешно
        if ($status === PasswordFacade::PASSWORD_RESET) {
            // return redirect(route('login'))->with('status', __($status));
            return redirect(route('login'))->with('status', 'Пароль успешно установлен');
        }

        // В случае ошибки возвращаем пользователя обратно с сообщением об ошибке
        return back()->withErrors(['email' => [__($status)]]);
        }
}

