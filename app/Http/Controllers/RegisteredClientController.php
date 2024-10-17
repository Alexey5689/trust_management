<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use App\Notifications\PasswordEmail;
use Illuminate\Support\Facades\Auth;

class RegisteredClientController extends Controller
{
    public function create()
    {
        // Получаем всех пользователей с ролью менеджера (role_id = 2)
        $managers = User::where('role_id', 2)->get(['id', 'last_name', 'first_name', 'middle_name']);

        // Передаем менеджеров на страницу регистрации
        return Inertia::render('Auth/RegisterClient',[
            'managers' => $managers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        dd($request->all());

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'phone_number' => 'required|string|max:20',
            'contract_number' => 'required|integer',
            // 'deadline' =>
            'create_date' => 'required|date_format:Y-m-d',
            'sum' => 'required|integer',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role_id' => $request->role_id, // Предполагаем, что 3 — это ID роли клиента
            'token' => Str::random(60),
            'refresh_token' => Str::random(60),
        ]);

        // $loggedInUser = Auth::user();
        $loggedInUser = User::with('role')->find(Auth::id());

        if ($loggedInUser->isAdmin()) {
            // Если админ — берем менеджера из запроса
            $manager_id = $request->manager_id;
        } else {
            // Если менеджер — его ID
            $manager_id = $loggedInUser->id;
        }

        // Записываем менеджера в таблицу user_manager
        $user->managers()->attach($manager_id);

        // Создание контракта с user_id и manager_id
        $user->userContracts()->create([
            'manager_id' => $manager_id,
            'contract_number' => $request->contract_number,
            'create_date' => $request->create_date,
            'sum' => $request->sum,
            'deadline' => $request->deadline,
            'procent' => $request->procent
        ]);




        $token = Password::createToken($user);
        $user->notify(new PasswordEmail($token, $user->email));

        event(new Registered($user));
        return redirect(route('success-registration'));
    }
}
