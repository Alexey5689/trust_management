<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use App\Notifications\PasswordEmail;
use Illuminate\Auth\Events\Registered;

class ManagerController extends Controller
{
    public function showClients(): Response
    {

        // Получаем его роль
        $user = Auth::user();
        $role = $user->role->title; // Получаем его роль
        $clients = $user->managedUsers->load('userContracts')->map(function ($client) {
            return [
                'id' => $client->id,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'middle_name' => $client->middle_name,
                'email' => $client->email,
                'phone_number' => $client->phone_number,
                'contracts' => $client->userContracts ? $client->userContracts->toArray() : [], // Загружаем контракты
            ];
        });

        // $role = $user->role->title;
        return Inertia::render('Clients', [
            'clients' => $clients,
            'role' => $role,
        ]);
    }

    public function createClientsByManager()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        // Получаем всех пользователей с ролью менеджера (role_id = 2)
        $managers = User::where('role_id', 2)->get(['id', 'last_name', 'first_name', 'middle_name']);

        // Передаем менеджеров на страницу регистрации
        return Inertia::render('Auth/RegisterClient', [
            'managers' => $managers,
            'role' => $role,
        ]);
    }

    public function storeClientsByManager(Request $request): RedirectResponse
    {
        // dd($request->all());

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'phone_number' => 'required|string|max:20',
            'contract_number' => 'required|integer',
            'deadline' => 'required|date_format:Y-m-d',
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
