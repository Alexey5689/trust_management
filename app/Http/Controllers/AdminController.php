<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contract;
use App\Models\Log;
use App\Models\Application;
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
    
    // все пользователи
    public function showAllUsers(){
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль

        $clients = User::whereHas('role', function ($query) {
        $query->where('title', 'client'); // Фильтрация по роли 'client'
        })
            ->with(['userContracts']) // Предзагрузка контрактов
            ->get()
            ->map(function ($client) {
                 // Получаем первый контракт
                $contract = $client->userContracts->first();

                // Получаем данные менеджера, если контракт существует
                $manager = $contract ? $contract->manager : null;

                return [
                    'id' => $client->id,
                    'full_name' => $client->last_name . ' ' . $client->first_name . ' ' . $client->middle_name,
                    'email' => $client->email,
                    'phone_number' => $client->phone_number,
                    'manager_full_name' => $manager
                            ? $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name
                            : 'Менеджер не назначен',
                ];
        });
        $managers = User::whereHas('role', function($query) {
            $query->where('title', 'manager');
        })->with('managerContracts')->get()->map(function ($manager) {
            return [
                'id' => $manager->id,
                'full_name' => $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name,
                'email' => $manager->email,
                'phone_number' => $manager->phone_number,
            ];
        });
        return Inertia::render('AllUsers', [
           
            'clients' => $clients,
            'managers' => $managers,
            'role' => $role,
        ]);
    }
    // все договоры
    public function showAllContracts(){
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        $contracts = Contract::with(['user'])->get() ->map(function ($contract) {
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
            'role' => $role, // Передаем роль пользователя в Vue-компонент
            'contracts'=> $contracts
        ]);
    }
    //все заявки
    public function showApplications(){
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        $applications = Application::with(['user', 'contract'])->get()->map(function ($application) {
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
        return Inertia::render('Applications', [
            'role' => $role, // Передаем роль пользователя в Vue-компонент
            'applications'=> $applications
        ]);

    }



    // регистрация user как клиент
    public function createClientsByAdmin()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        // Получаем всех пользователей с ролью менеджера (role_id = 2)
        $managers = User::where('role_id', 2)->get()->map(function ($manager) {
            return [
                'id' => $manager->id,
                'full_name' => $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name,
            ];
        });
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
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class . ',email',
            'phone_number' => 'required|string|max:20|unique:' . User::class . ',phone_number',
            'contract_number' => 'required|integer|unique:' . Contract::class . ',contract_number',
            'deadline' => 'required|date_format:Y-m-d',
            'create_date' => 'required|date_format:Y-m-d',
            'sum' => 'required|integer',
        ]);
        //dd($request->all());
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
         // Логируем событие регистрации
         Log::create([
            'model_id' => $user->id,
            'model_type' => User::class,
            'change' => 'Регистрация пользователя',
            'action' => 'create',
            'old_value' => 'Регистрация',
            'new_value' => $user->email,
            'created_by' => Auth::id(), // ID самого пользователя
        ]);

        $manager_id = $request->manager_id;
        // Записываем менеджера в таблицу user_manager
        $user->managers()->attach($manager_id);
        // Создание контракта с user_id и manager_id
        $contract = $user->userContracts()->create([
            'manager_id' => $request->manager_id,
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
            'action' => 'create',
            'old_value' => null,
            'new_value' => 'Договор No' . $contract->contract_number,
            'created_by' => Auth::id(), // ID самого пользователя
        ]);
        $user->userTransactions()->create([
            'contract_id'=>$contract->id,
            'manager_id' => $manager_id,
            'date_transition' => $request->create_date,
            'status' => $request->contract_status,
            'sum_transition' => $request->sum,
            'sourse' =>'Договор'
        ]);
       


        $token = Password::createToken($user);
        $user->notify(new PasswordEmail($token, $user->email));

        event(new Registered($user));
        return redirect(route('admin.users'))->with('status', 'Клиент успешно зарегистрирован!');
    }

     // изменение контактных данных user клиент
     public function editClientByAdmin(User $client): Response
     {
         $user = Auth::user(); // Получаем текущего пользователя
         $role = $user->role->title; // Получаем его роль
         $managers = User::where('role_id', 2)->get()->map(function ($manager) {
            return [
                'id' => $manager->id,
                'full_name' => $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name,
            ];
        });;
         $assignedManagerId = $client->userContracts()->first()->manager_id ?? 'Менеджер не назначен';
         //dd($user, $role, $assignedManager);
         return Inertia::render('EditClient', [
             'role' => $role,
             'client' =>[
                 'id' => $client->id,
                 'last_name' => $client->last_name,
                 'first_name' => $client->first_name,
                 'middle_name' => $client->middle_name,
                 'email' => $client->email,
                 'phone_number' => $client->phone_number
             ] ,
             'managers'=>$managers,
             'assignedManager' => $assignedManagerId
         ]);
     }
     public function updateClientByAdmin(Request $request, User $client): RedirectResponse
     {
       //dd($request->all());
         $request->validate([
             'last_name' => 'required|string|max:255',
             'first_name' => 'required|string|max:255',
             'middle_name' => 'required|string|max:255',
             'email' => 'required|string|lowercase|email|max:255|unique:' . User::class . ',email,' . $client->id,
             'phone_number' => 'required|string|max:20',
         ]);

        

         $originalData = $client->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']);
         $client->update($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']));
         // Логируем изменения
         foreach ($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']) as $field => $newValue) {
            $oldValue =$this->normalizeValue($originalData[$field]);
            $newValue =$this->normalizeValue($newValue);
            if ($oldValue !== $newValue) {
                Log::create([
                    'model_id' => $client->id,
                    'model_type' => User::class,
                    'change' => $field,
                    'action' => 'update',
                    'old_value' => $originalData[$field],
                    'new_value' => $newValue,
                    'created_by' => Auth::id(),
                ]);
            }
        }
        // Проверяем, изменился ли менеджер
        $currentManager = $client->managers()->first();
        $newManager = User::find($request->manager_id);

        if ($currentManager?->id != $newManager?->id) {
            // Обновляем менеджера в промежуточной таблице user_manager
            $client->managers()->sync([$request->manager_id]);
            // Логируем смену менеджера
            Log::create([
                'model_id' => $client->id,
                'model_type' => User::class,
                'change' => 'manager_id',
                'action' => 'update',
                'old_value' => $currentManager ? $currentManager->last_name . ' ' . $currentManager->first_name . ' ' . $currentManager->middle_name : null,
                'new_value' => $newManager ? $newManager->last_name . ' ' . $newManager->first_name . ' ' . $newManager->middle_name : null,
                'created_by' => Auth::id(),
            ]);
        }
        $client->userContracts()->update([
            'manager_id' => $request->manager_id,
        ]);
         $client->save();
         return redirect(route('admin.users'))->with('success', 'Данные клиента обновлены');
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
        // Логируем событие регистрации
        Log::create([
            'model_id' => $user->id,
            'model_type' => User::class,
            'change' => 'Регистрация пользователя',
            'action' => 'create',
            'old_value' => 'Регистрация',
            'new_value' => $user->email,
            'created_by' => Auth::id(), // ID самого пользователя
        ]);


        $token = Password::createToken($user);
        $user->notify(new PasswordEmail($token, $user->email));

        event(new Registered($user));
        return redirect(route('admin.users'))->with('success', 'Менеджер успешно зарегистрирован!');
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

      
        
        $originalData = $manager->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']);
        $manager->update($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']));
        foreach ($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']) as $field => $newValue) {
            $oldValue = $this->normalizeValue($originalData[$field]);
            $newValue = $this->normalizeValue($newValue);
            if ($oldValue !== $newValue) {
                Log::create([
                    'model_id' => $manager->id,
                    'model_type' => User::class,
                    'change' => $field,
                    'action' => 'update',
                    'old_value' => $originalData[$field],
                    'new_value' => $newValue,
                    'created_by' => Auth::id(),
                ]);
            }
        }
        return redirect(route('admin.users'))->with('success', 'Данные обновлены');
    }


     
     


    //новый договор
      public function createAddContractByAdmin()
      {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        $clients = User::whereHas('role', function ($query) {
            $query->where('title', 'client'); // Фильтрация по роли 'client'
        })->with('userContracts') // Загружаем контракты для клиентов
        ->get() // Получаем коллекцию пользователей
        ->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' => $client->first_name . ' ' . $client->last_name . ' ' . $client->middle_name,
            ];
        });
        // dd($clients);
        return Inertia::render('AddContract', [
            'role' => $role,
            'clients' => $clients,
        ]);
      }


      public function storeAddContractByAdmin(Request $request)
      {
        // dd($request->all());
        $request->validate([
            'contract_number' => 'required|integer|unique:'. Contract::class,
            'procent' => 'required|integer',
            'deadline' => 'required|date_format:Y-m-d',
            'create_date' => 'required|date_format:Y-m-d',
            'sum' => 'required|integer',
        ]);
        //dd($request->all());
         // Находим клиента по user_id из запроса
        $client = User::findOrFail($request->user_id);
        // Получаем ID первого менеджера, закрепленного за клиентом
        $manager_id = optional($client->managers->first())->id;
        // dd($manager_id);
        $contract = Contract::create([
            'user_id' => $request->user_id,
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
        // Логируем событие регистрации
        Log::create([
            'model_id' => $contract->user_id,
            'model_type' => Contract::class,
            'change' => 'Добавление договора',
            'action' => 'create',
            'old_value' => null,
            'new_value' => 'Договор No ' . $contract->contract_number,
            'created_by' => Auth::id(), // ID самого пользователя
        ]);
        return redirect(route('admin.contracts'))->with('success', 'Контракт успешно создан!');
      }



      //редакция договора
      public function editContractByAdmin(Contract $contract): Response
      {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        $clients = User::whereHas('role', function ($query) {
            $query->where('title', 'client'); // Фильтрация по роли 'client'
        })->with('userContracts') // Загружаем контракты для клиентов
        ->get() // Получаем коллекцию пользователей
        ->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' => $client->first_name . ' ' . $client->last_name . ' ' . $client->middle_name,
            ];
        });
        // dd($contract);
        return Inertia::render('EditContract', [
            'role' => $role,
            'contract' => $contract,
            'clients' => $clients,
        ]);
      }

      public function updateContractByAdmin(Request $request, Contract $contract)
      {
        // dd($request->all());
        $request->validate([
            'contract_number' => 'required|integer',
            'procent' => 'required|integer',
            'deadline' => 'required|date_format:Y-m-d',
            'create_date' => 'required|date_format:Y-m-d',
            'sum' => 'required|integer',
        ]);
         // Находим клиента по user_id из запроса
        // $client = User::findOrFail($request->user_id);
        // Получаем ID первого менеджера, закрепленного за клиентом
        // $manager_id = optional($client->managers->first())->id;

        $originalData = $contract->only(['user_id', 'contract_number', 'create_date', 'sum', 'deadline', 'procent', 'payments', 'agree_with_terms', 'contract_status']);
        $contract->update($request->only(['user_id', 'contract_number', 'create_date', 'sum', 'deadline', 'procent', 'payments', 'agree_with_terms', 'contract_status']));

        foreach ($request->only(['user_id', 'contract_number', 'create_date', 'sum', 'deadline', 'procent', 'payments', 'agree_with_terms', 'contract_status']) as $field => $newValue) {
            $oldValue = $this->normalizeValue($originalData[$field]);
            $newValue = $this->normalizeValue($newValue);
            if ($oldValue !== $newValue) {
                Log::create([
                    'model_id' => $contract->user_id,
                    'model_type' => Contract::class,
                    'change' => $field,
                    'action' => 'update',
                    'old_value' => $originalData[$field],
                    'new_value' => $newValue,
                    'created_by' => Auth::id(),
                ]);
            }
        }
        // dd($manager_id);
        return redirect(route('admin.contracts'))->with('success', 'Контракт успешно обновлен!');
      }

      public function changeStatusApplication(Application $application){
        $user = Auth::user();
        $role = $user->role->title;
        return Inertia::render('ChangeStatusApplication', [
            'role' => $role,
            'application' => [
                'id' => $application->id,
                'status' => $application->status,
            ],
        ]);
    }
    public function updateStatusApplication(Request $request, Application $application)
{
    $user = Auth::user();
    $role = $user->role->title;

    $originalStatus = $application->status;

    // Обновляем статус заявки
    $application->update(['status' => $request->status]);

    // Если статус "Исполнена", создаем транзакцию
    if ($request->status === 'Исполнена') {
        $application->user->userTransactions()->create([
            'contract_id' => $application->contract_id,
            'manager_id' => $application->manager_id,
            'user_id' => $application->user_id,
            'date_transition' => $application->date_of_payments,
            'sum_transition' => $application->sum,
            'sourse' => 'Заявка',
        ]);

        $message = 'Статус заявки успешно изменен! Транзакция создана.';

        if ($application->condition === 'Раньше срока') {
            $contract = Contract::find($application->contract_id);
            $contract->update(['contract_status' => false]);
        }
    } else {
        $message = 'Статус заявки успешно изменен!';
    }

    // Логирование изменения статуса
    if ($originalStatus !== $application->status) {
        Log::create([
            'model_id' => $application->id,
            'model_type' => Application::class,
            'change' => 'status',
            'action' => 'update',
            'old_value' => $originalStatus,
            'new_value' => $application->status,
            'created_by' => Auth::id(),
        ]);
    }

    return redirect(route($role . '.applications'))->with('success', $message);
} 
    public function createLogs(){
        $user = Auth::user();
        $role = $user->role->title;
        $logs = Log::with(['creator', 'target'])->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'model_type' => $log->model_type,
                'created_at' => $log->created_at,
                'action' => $log->action,
                'creator' => [
                    'id' => $log->creator->id,
                    'full_name' => $log->creator->last_name . ' ' . $log->creator->first_name . ' ' . $log->creator->middle_name,
                ],
                'target' => [
                    'id' => $log->target->id,
                    'full_name' => $log->target->last_name  . ' ' . $log->target->first_name . ' ' . $log->target->middle_name, 
                ],
                'change' => $log->change,
                'old_value' => $log->old_value,
                'new_value' => $log->new_value,

            ];
        });
        return Inertia::render('Logs', [
            'role' => $role,
            'logs' => $logs
        ]);
    }
}
