<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Log;
use App\Models\Contract;
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
          /** @var User $user */
        $role = $user->role->title; // Получаем его роль
        $clients = $user->managedUsers()
        ->with('userContracts')
        ->get()
        ->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' => $client->last_name . ' ' . $client->first_name . ' ' . $client->middle_name,
                'email' => $client->email,
                'phone_number' => $client->phone_number,
                'active' => $client->active,
                'user_contracts' => $client->userContracts->toArray(),
            ];
        });

        return Inertia::render('Clients', [
            'clients' => $clients,
            'role' => $role,
        ]);
    }

    public function showContracts(){
        $user = Auth::user();
        $role = $user->role->title; // Получаем его роль
         // Явно указываем тип переменной $user
        /** @var User $user */
        $contracts = $user->managerContracts()->with('user')->get()->map(function ($contract) {
            return [
                'id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'create_date' => $contract->create_date,
                'sum' => $contract->sum,
                'deadline' => $contract->deadline,
                'procent' => $contract->procent,
                'payments' => $contract->payments,
                'contract_status' => $contract->contract_status,
                'user' => [
                    'id' => $contract->user->id,
                    'full_name' => $contract->user->last_name . ' ' . $contract->user->first_name . ' ' . $contract->user->middle_name,
                ]
            ];
        });
        return Inertia::render('Contracts', [
            'contracts' => $contracts,
            'role' => $role,
        ]);
    }

    public function createClientsByManager()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        // Получаем всех пользователей с ролью менеджера (role_id = 2)
        // Передаем менеджеров на страницу регистрации
        return Inertia::render('RegisterClient', [
            'managers' => [],
            'role' => $role,
        ]);
    }

    public function storeClientsByManager(Request $request): RedirectResponse
    {
        //dd($request->all());

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class . ',email',
            'phone_number' => 'required|string|max:20|unique:' . User::class . ',phone_number',
            'contract_number' => 'required|integer|unique:' . Contract::class . ',contract_number',
            'deadline' => 'required|date_format:Y-m-d',
            'create_date' => 'required|date_format:Y-m-d',
            'sum' => 'required|integer',
        ]);
        

        $client = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role_id' => $request->role_id, // Предполагаем, что 3 — это ID роли клиента
            'token' => Str::random(60),
            'refresh_token' => Str::random(60),
        ]);

        // Логируем событие регистрации
        Log::create([
            'model_id' => $client->id,
            'model_type' => User::class,
            'change' => 'Добавление клиента',
            'action' => 'Регистрация пользователя',
            'old_value' => null,
            'new_value' => $client->email,
            'created_by' => Auth::id(), // ID самого пользователя
        ]);

        // $loggedInUser = Auth::user();
        $loggedInUser = User::with('role')->find(Auth::id());
        $manager_id = $loggedInUser->id;


        // Записываем менеджера в таблицу user_manager
        $client->managers()->attach($manager_id);

        // Создание контракта с user_id и manager_id
        $contract = $client->userContracts()->create([
            'manager_id' => $manager_id,
            'contract_number' => $request->contract_number,
            'create_date' => $request->create_date,
            'sum' => $request->sum,
            'deadline' => $request->deadline,
            'procent' => $request->procent,
            'payments' => $request->payments,
            'agree_with_terms' => $request->agree_with_terms,
            'contract_status' => $request->contract_status,
        ]);

        Log::create([
            'model_id' => $contract->user_id,
            'model_type' => Contract::class,
            'change' => 'Добавление договора',
            'action' => 'Создание',
            'old_value' => null,
            'new_value' => 'Договор No' . $contract->contract_number,
            'created_by' => Auth::id(), // ID самого пользователя
        ]);

        $client->userTransactions()->create([
            'contract_id'=>$contract->id,
            'manager_id' => $manager_id,
            'date_transition' => $request->create_date,
            'sum_transition' => $request->sum,
            'sourse' =>'Договор'
        ]);



        $token = Password::createToken($client);
        $client->notify(new PasswordEmail($token, $client->email));

        event(new Registered($client));
        return redirect(route('manager.clients'))->with('status', 'Клиент успешно зарегистрирован!');
    }

    public function editClientByManager(User $client): Response
      {
          $user = Auth::user(); // Получаем текущего пользователя
          $role = $user->role->title; // Получаем его роль
       
          return Inertia::render('EditClient', [
              'role' => $role,
              'client' => $client,
              'managers'=>[],
              'assignedManager' => $user->id
          ]);
      }
      public function updateClientByManager(Request $request, User $client): RedirectResponse
      {
        // dd($request->all());
          $request->validate([
              'phone_number' => 'required|string|max:20',
          ]);
          $message = 'Изменений не было';
          $oldPhone = $this->normalizeValue($client->phone_number);
          $newPhone = $this->normalizeValue($request->phone_number);
          if($oldPhone !== $newPhone){
            $client->update($request->only(['phone_number']));
            Log::create([
                'model_id' => $client->id,
                'model_type' => User::class,
                'change' => 'Изменен номер телефона',
                'action' => 'Обновление данных',
                'old_value' => $oldPhone,
                'new_value' => $newPhone,
                'created_by' => Auth::id(),
            ]);
            $message = 'Телефон успешно обновлен';
          }
          
          return redirect(route('manager.clients'))->with('info', $message);
      }

      public function createAddContractByManager()
      {
        
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        $clients = $user->managedUsers->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' => $client->first_name. ' ' .$client->last_name. ' ' .$client->middle_name,
            ];
        });
        return Inertia::render('AddContract', [
            'role' => $role,
            'clients' => $clients,
            'managers'=>[],
        ]);
      }
      public function storeAddContractByManager(Request $request)
      {
        //dd($request->all());
        $request->validate([
            'contract_number' => 'required|integer|unique:'. Contract::class,
            'procent' => 'required|integer',
            'deadline' => 'required|date_format:Y-m-d',
            'create_date' => 'required|date_format:Y-m-d',
            'sum' => 'required|integer',
        ]);

        $user = Auth::user();
        $client = User::findOrFail($request->user_id);
        /** @var User $user */
        $contract = $user->managerContracts()->create([
            'user_id' => $request->user_id,
            'manager_id' => $user->id,
            'contract_number' => $request->contract_number,
            'create_date' => $request->create_date,
            'sum' => $request->sum,
            'deadline' => $request->deadline,
            'procent' => $request->procent,
            'payments' => $request->payments,
            'agree_with_terms' => $request->agree_with_terms,
            'contract_status' => $request->contract_status,
        ]);
        Log::create([
            'model_id' => $request->user_id,
            'model_type' => null,
            'change' =>'Добавление договора',
            'action' => 'Создание',
            'old_value' => null,
            'new_value' =>'Договор No' . $request->contract_number,
            'created_by' => Auth::id(),
        ]);
        
       
        $client->userTransactions()->create([
            'contract_id'=>$contract->id,
            'manager_id' => $user->id,
            'date_transition' => $request->create_date,
            'sum_transition' => $request->sum,
            'sourse' =>'Договор'
        ]);
        return redirect(route('manager.contracts'))->with('status', 'Договор успешно создан!');
      }

      public function createApplications(){
        $user = Auth::user();
        $role = $user->role->title;
         /** @var User $user */
        $applications = $user ->managerApplications()
                        ->with('user',  'contract')
                        ->get()
                        ->map(function ($application) {
                            return [
                                'id' => $application->id,
                                'user_id' => $application->user_id,
                                'full_name' => $application->user->first_name. ' ' .$application->user->last_name. ' ' .$application->user->middle_name,
                                'contract_number' => $application->contract->contract_number,
                                'condition' => $application->condition,
                                'status' => $application->status,
                                'type_of_processing' => $application->type_of_processing,
                                'date_of_payments' => $application->date_of_payments,
                                'create_date' => $application->create_date,
                            ];
                        });
        //dd($applications);
        return Inertia::render('Applications', [
            'role' => $role,
            'applications' => $applications,
        ]);
      }



}

