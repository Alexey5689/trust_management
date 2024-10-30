<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contract;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Notifications\PasswordEmail;
use Illuminate\Auth\Events\Registered;

class AdminController extends Controller
{
    // всеклиенты
    public function showClients()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        // Фильтрация клиентов
        $clients = User::whereHas('role', function($query) {
            $query->where('title', 'client'); // Фильтрация по роли 'client'
        })->with('userContracts')->get();
        // dd($clients);

        return Inertia::render('Clients', [
            'clients' => $clients,
            'role' => $role, // Передаем роль пользователя в Vue-компонент

        ]);
    }
    //все менеджеры
    public function showManagers()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        // dd($user, $role);
        // Фильтрация менеджеров
        $managers = User::whereHas('role', function($query) {
            $query->where('title', 'manager');
        })->with('managerContracts')->get();

        return Inertia::render('Managers', [
            'managers' => $managers,
            'role' => $role, // Передаем роль пользователя в Vue-компонент
        ]);
    }
    // все договоры
    public function showAllContracts(){
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        $contracts = Contract::with(['user'])->get();
        return Inertia::render('Contracts', [
            'role' => $role, // Передаем роль пользователя в Vue-компонент
            'contracts'=> $contracts
        ]);
    }

    // регистрация user как клиент
    public function createClientsByAdmin()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        // Получаем всех пользователей с ролью менеджера (role_id = 2)
        $managers = User::where('role_id', 2)->get(['id', 'last_name', 'first_name', 'middle_name']);

        // Передаем менеджеров на страницу регистрации
        return Inertia::render('RegisterClient',[
            'managers' => $managers,
            'role' => $role,
        ]);
    }
    public function storeClientsByAdmin(Request $request ):RedirectResponse
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
        // $loggedInUser = User::with('role')->find(Auth::id());

        // if ($loggedInUser->isAdmin()) {
        //     // Если админ — берем менеджера из запроса

        // } else {
        //     // Если менеджер — его ID
        //     $manager_id = $loggedInUser->id;
        // }
        $manager_id = $request->manager_id;
        // Записываем менеджера в таблицу user_manager
        $user->managers()->attach($manager_id);
        // Создание контракта с user_id и manager_id
        $user->userContracts()->create([
            'manager_id' => $request->manager_id,
            'contract_number' => $request->contract_number,
            'create_date' => $request->create_date,
            'sum' => $request->sum,
            'deadline' => $request->deadline,
            'procent' => $request->procent,
            'payments' => $request->payments,
        ]);

        $token = Password::createToken($user);
        $user->notify(new PasswordEmail($token, $user->email));

        event(new Registered($user));
        return redirect(route('admin.clients'))->with('status', 'Клиент успешно зарегистрирован!');
    }



    // регистрация user как менеджера
    public function createManagersByAdmin(): Response
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль

        // dd($user, $role);
        return Inertia::render('RegisterManager', [
            'role' => $role,
        ]);
    }
    public function storeManagersByAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'phone_number' => 'required|string|max:20',
            'role_id' => 'required|integer'
        ]);

        $user = User::create([
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role_id' => $request->role_id,
            'token' => Str::random(60),
            'refresh_token' => Str::random(60),
        ]);


        $token = Password::createToken($user);
        $user->notify(new PasswordEmail($token, $user->email));

        event(new Registered($user));
        return redirect(route('admin.managers'))->with('success', 'Менеджер успешно зарегистрирован!');
    }


     // изменение контактных данных user менеджер
    public function editManagersByAdmin(User $manager): Response
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль

        // dd($user, $role);
        return Inertia::render('EditManager', [
            'role' => $role,
            'manager' => $manager
        ]);
    }
    public function updateManagersByAdmin(Request $request, User $manager): RedirectResponse
    {
        $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class . ',email,' . $manager->id,
            'phone_number' => 'required|string|max:20',
        ]);


        $manager->last_name = $request->last_name;
        $manager->first_name = $request->first_name;
        $manager->middle_name = $request->middle_name;
        $manager->email = $request->email;
        $manager->phone_number = $request->phone_number;
        $manager->save();
        return redirect(route('admin.managers'))->with('success', 'Данные обновлены');
    }





      // изменение контактных данных user клиент
      public function editClientByAdmin(User $client): Response
      {
          $user = Auth::user(); // Получаем текущего пользователя
          $role = $user->role->title; // Получаем его роль
          $managers = User::where('role_id', 2)->get(['id', 'last_name', 'first_name', 'middle_name']);
          $assignedManagerId = $client->userContracts()->first()->manager_id;
          //dd($user, $role, $assignedManager);
          return Inertia::render('EditClient', [
              'role' => $role,
              'client' => $client,
              'managers'=>$managers,
              'assignedManager' => $assignedManagerId
          ]);
      }
      public function updateClientByAdmin(Request $request, User $client): RedirectResponse
      {
        // dd($request->all());
          $request->validate([
              'last_name' => 'required|string|max:255',
              'first_name' => 'required|string|max:255',
              'middle_name' => 'required|string|max:255',
              'email' => 'required|string|lowercase|email|max:255|unique:' . User::class . ',email,' . $client->id,
              'phone_number' => 'required|string|max:20',
          ]);
          $client->last_name = $request->last_name;
          $client->first_name = $request->first_name;
          $client->middle_name = $request->middle_name;
          $client->email = $request->email;
          $client->phone_number = $request->phone_number;


          $client->userContracts()->update([
            'manager_id' => $request->manager_id,
          ]);
          $client->save();
          return redirect(route('admin.clients'))->with('success', 'Данные обновлены');
      }




    // сброс пароля
    public function resetPassword(User $user){
         // Генерация токена сброса пароля
        $token = Password::createToken($user);

        // Отправка уведомления с токеном на email менеджера
        $user->notify(new PasswordEmail($token, $user->email));

        // Flash-сообщение об успешной отправке
        return redirect()->back()->with('success', 'Ссылка для сброса пароля отправлена менеджеру на email.');
    }


      // Удаление user
      public function deleteUserByAdmin(User $user): RedirectResponse
      {
        // dd($user);
          $user->delete();
          return redirect('/')->with('success', 'User удален');
      }
}
