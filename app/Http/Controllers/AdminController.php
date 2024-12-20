<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contract;
use Illuminate\Validation\Rule;
use App\Models\Log;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
        // ->where('active', true)
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
                'active' => $client->active,
                'manager_full_name' => $manager
                        ? $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name
                        : 'Менеджер не назначен',
            ];
        });
        $managers = User::whereHas('role', function($query) {
            $query->where('title', 'manager');
        })
        // ->where('active', true)
        ->with('managerContracts')
        ->get()
        ->map(function ($manager) {
            return [
                'id' => $manager->id,
                'full_name' => $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name,
                'email' => $manager->email,
                'phone_number' => $manager->phone_number,
                'active' => $manager->active
            ];
        });

    //     User::select(['id', 'last_name', 'first_name', 'middle_name', 'email', 'phone_number'])
    // ->whereHas('role', function($query) {
    //     $query->where('title', 'manager');
    // })
    // ->get()
    // ->map(...);

    // $managers = User::whereHas('role', function ($query) {
    //     $query->where('title', 'manager'); // Фильтруем пользователей с ролью 'manager'
    // })
    // ->where('active', true) // Фильтруем только активных пользователей
    // ->with('managerContracts') // Подгружаем связанные данные, если нужно
    // ->get()
    // ->map(function ($manager) {
    //     return [
    //         'id' => $manager->id,
    //         'full_name' => $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name,
    //         'email' => $manager->email,
    //         'phone_number' => $manager->phone_number,
    //     ];
    // });
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
        $clients = User::whereHas('role', function ($query) {
            $query->where('title', 'client'); // Фильтрация по роли 'client'
        })->with('userContracts') // Загружаем контракты для клиентов
        ->get() // Получаем коллекцию пользователей
        ->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' => $client->first_name . ' ' . $client->last_name . ' ' . $client->middle_name,
                'avaliable_balance' => $client->avaliable_balance,
                'active' => $client->active,
            ];
        });
        return Inertia::render('Contracts', [
            'role' => $role, // Передаем роль пользователя в Vue-компонент
            'contracts'=> $contracts,
            'clients' => $clients
        ]);
    }
    //все заявки
    public function showAllApplications(){
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        $clients = User::whereHas('role', function($query) {
            $query->where('title', 'client')->where('active', true); // Фильтрация по роли 'client'
        })
        ->with(['userContracts' => function ($query) {
            $query->where('contract_status', true); // Выбираем только активные договоры
        }])
        ->get()
        ->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' =>  $client->last_name. ' ' .$client->first_name. ' ' .$client->middle_name,               
                'user_contracts' => $client->userContracts->map(function ($contract) {
                    $term = $this->termOfTheContract($contract->create_date, $contract->deadline);
                    $dividends = $contract->sum * ($contract->procent / 100) * $term / $contract->number_of_payments;
                    return [
                        'id' => $contract->id,
                        'contract_number' => $contract->contract_number,
                        'sum' => $contract->sum,
                        'create_date' => $contract->create_date,
                        'deadline' => $contract->deadline,
                        'procent' => $contract->procent,
                        'manager_id' => $contract->manager_id,
                        'dividends' => $dividends,
                        'term' => $term
                    ];
                }),
            ];
        });
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
                'sum' => $application->sum,
                'dividends' => $application->dividends
            ];
        });
        return Inertia::render('Applications', [
            'role' => $role, // Передаем роль пользователя в Vue-компонент
            'applications'=> $applications,
            'clients' => $clients
        ]);

    }



    // регистрация user как клиент
    //public function createClientsByAdmin()
   // {
        // $user = Auth::user(); // Получаем текущего пользователя
        // $role = $user->role->title; // Получаем его роль
        // Получаем всех пользователей с ролью менеджера (role_id = 2)
        // $managers = User::where('role_id', 2)->get()->map(function ($manager) {
        //     return [
        //         'id' => $manager->id,
        //         'full_name' => $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name,
        //     ];
        // });
        // Передаем менеджеров на страницу регистрации
        // return Inertia::render('RegisterClient',[
        //     'managers' => $managers,
        //     'role' => $role,
        // ]);
        // return response()->json([
            // 'user' =>[
            //     'managers' => $managers
            // ] 
        // ]);
   // }
    public function storeClientsByAdmin(Request $request ):RedirectResponse
    {
       // dd($request->all());
        $request->validate([
            'first_name' => ['required','string' ,'max:255', 'min:2'],
            'last_name' => ['required','string','max:255', 'min:2'],
            'middle_name' => ['required','string','max:255', 'min:2'],
            'email' => ['required','string','email','max:255','min:6', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:12', 'min:6', 'unique:users,phone_number'],
            'contract_number' =>['required', 'integer', 'unique:contracts,contract_number'],
            'deadline' => ['required', 'date_format:Y-m-d'],
            'create_date' => ['required', 'date_format:Y-m-d'],
            'sum' => ['required', 'integer'],
            'procent' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        //dd($request->all());
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role_id' => 3, // Предполагаем, что 3 — это ID роли клиента
            'token' => Str::random(60),
            'refresh_token' => Str::random(60),
        ]);
         // Логируем событие регистрации
        Log::create([
            'model_id' => $user->id,
            'model_type' => User::class,
            'change' => 'Добавление клиента',
            'action' => 'Регистрация пользователя' ,
            'old_value' => null,
            'new_value' =>  $user -> last_name . ' ' . $user -> first_name . ' ' . $user -> middle_name,
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
            'dividends' => $request->dividends,
            'number_of_payments'=> $request->number_of_payments
        ]);
        Log::create([
            'model_id' => $contract->user_id,
            'model_type' => Contract::class,
            'change' => 'Добавление договора',
            'action' => 'Создание',
            'old_value' => null,
            'new_value' => 'Договор No'.$contract->contract_number,
            'created_by' => Auth::id(), // ID самого пользователя
        ]);
        $user->userTransactions()->create([
            'contract_id'=>$contract->id,
            'manager_id' => $manager_id,
            'date_transition' => $request->create_date,
            'sum_transition' => $request->sum,
            'sourse' =>'Договор'
        ]);
       
        $token = Password::createToken($user);
        $user->notify(new PasswordEmail($token, $user->email));

        event(new Registered($user));
        return redirect()->route('admin.users')->with('status', 'Клиент успешно зарегистрирован!');
    }

     // изменение контактных данных user клиент
     public function editClientByAdmin(User $user)
     {
        //dd($user);
        //  $user = Auth::user(); // Получаем текущего пользователя
        //  $role = $user->role->title; // Получаем его роль
        //  $managers = User::where('role_id', 2)->get()->map(function ($manager) {
        //     return [
        //         'id' => $manager->id,
        //         'full_name' => $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name,
        //     ];
        // });
         $assignedManagerId = $user->userContracts()->first()->manager_id;
         return response()->json([
           'user'=> [
                'id' => $user->id,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'manager_id' => $assignedManagerId
            ],
        ]);
     }
     public function updateClientByAdmin(Request $request, User $user): RedirectResponse
     {
       //dd($request->all());
         $request->validate([
            'first_name' => ['required','string' ,'max:255', 'min:2'],
            'last_name' => ['required','string','max:255', 'min:2'],
            'middle_name' => ['required','string','max:255', 'min:2'],
             'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'min:6',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone_number' => ['required', 'string', 'max:12', 'min:6', Rule::unique('users', 'phone_number')->ignore($user->id)],
         ]);

         $originalData = $user->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']);
         $user->update($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']));
         // Логируем изменения
         foreach ($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']) as $field => $newValue) {
            $oldValue =$this->normalizeValue($originalData[$field]);
            $newValue =$this->normalizeValue($newValue);
            if ($oldValue !== $newValue) {
                Log::create([
                    'model_id' => $user->id,
                    'model_type' => User::class,
                    'change' => 'Изменено поле' . $field,
                    'action' => 'Обновление данных',
                    'old_value' => $originalData[$field],
                    'new_value' => $newValue,
                    'created_by' => Auth::id(),
                ]);
            }
        }
        $user->userNotifications()->create([
            'content'=> 'Ваши контактные данные были изменены',
        ]);
        // Проверяем, изменился ли менеджер
        $currentManager = $user->managers()->first();
        $newManager = User::find($request->manager_id);

        if ($currentManager?->id != $newManager?->id) {
            // Обновляем менеджера в промежуточной таблице user_manager
            $user->managers()->sync([$request->manager_id]);
            // Логируем смену менеджера
            Log::create([
                'model_id' => $user->id,
                'model_type' => User::class,
                'change' => 'Изменен менеджер',
                'action' => 'Обновление данных',
                'old_value' => $currentManager ? $currentManager->last_name . ' ' . $currentManager->first_name . ' ' . $currentManager->middle_name : null,
                'new_value' => $newManager ? $newManager->last_name . ' ' . $newManager->first_name . ' ' . $newManager->middle_name : null,
                'created_by' => Auth::id(),
            ]);
            $user->userNotifications()->create([
                'content'=> 'Ваш менеджер был изменен',
            ]);
        }
        $user->userContracts()->update([
            'manager_id' => $request->manager_id,
        ]);
         return redirect()->route('admin.users')->with('status', 'Данные пользователя успешно обновлены');
     }






    // регистрация user как менеджера
    // public function createManagersByAdmin(): Response
    // {
    //     $user = Auth::user(); // Получаем текущего пользователя
    //     $role = $user->role->title; // Получаем его роль
    //     // dd($user, $role);
    //     return Inertia::render('RegisterManager', [
    //         'role' => $role,
    //     ]);
    // }
    public function storeManagersByAdmin(Request $request): RedirectResponse
    {
        //dd($request->all());
        $request->validate([
            'first_name' => ['required','string' ,'max:255', 'min:2'],
            'last_name' => ['required','string','max:255', 'min:2'],
            'middle_name' => ['required','string','max:255', 'min:2'],
            'email' => ['required','string','email','max:255','min:6', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:12', 'min:6', 'unique:users,phone_number'],
        ]);

        $user = User::create([
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role_id' => 2,
            'token' => Str::random(60),
            'refresh_token' => Str::random(60),
        ]);
        // Логируем событие регистрации
        Log::create([
            'model_id' => $user->id,
            'model_type' => User::class,
            'change' => 'Добавление менеджера',
            'action' => 'Регистрация пользователя',
            'old_value' => null,
            'new_value' => $user -> last_name . ' ' . $user -> first_name . ' ' . $user -> middle_name,
            'created_by' => Auth::id(), // ID самого пользователя
        ]);


        $token = Password::createToken($user);
        $user->notify(new PasswordEmail($token, $user->email));

        event(new Registered($user));
        return redirect()->route('admin.users')->with('status', 'Менеджер успешно зарегистрирован!');
    }


     // изменение контактных данных user менеджер
    public function editManagersByAdmin(User $user)
    {
        //dd($manager);
        // $user = Auth::user(); // Получаем текущего пользователя
        // $role = $user->role->title; // Получаем его роль

        // // dd($user, $role);
        // return Inertia::render('EditManager', [
        //     'role' => $role,
        //     'manager' => $manager
        // ]);
        return response()->json([
            'user' =>[
                'id' => $user->id,
                'last_name' =>$user->last_name,
                'first_name' =>$user->first_name,
                'middle_name' =>$user->middle_name,
                'email' =>$user->email,
                'phone_number' =>$user->phone_number
            ] 
        ]);

    }
    public function updateManagersByAdmin(Request $request, User $user): RedirectResponse
    {
        //dd($request->all(), $user);
        $request->validate([
            'first_name' => ['required','string' ,'max:255', 'min:2'],
            'last_name' => ['required','string','max:255', 'min:2'],
            'middle_name' => ['required','string','max:255', 'min:2'],
            'email' => ['required','string','email','max:255','min:6', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['required', 'string', 'max:12', 'min:6', Rule::unique('users', 'phone_number')->ignore($user->id)],
        ]);

        $originalData = $user->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']);
        $user->update($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']));
        foreach ($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']) as $field => $newValue) {
            $oldValue = $this->normalizeValue($originalData[$field]);
            $newValue = $this->normalizeValue($newValue);
            if ($oldValue !== $newValue) {
                Log::create([
                    'model_id' => $user->id,
                    'model_type' => User::class,
                    'change' => 'Изменено поле' . $field,
                    'action' => 'Обновление данных',
                    'old_value' => $originalData[$field],
                    'new_value' => $newValue,
                    'created_by' => Auth::id(),
                ]);
            }
        }

        $user->userNotifications()->create([
            'content'=> 'Ваши контактные данные были изменены',
        ]);

        return redirect()->route('admin.users')->with('status', 'Данные пользователя успешно обновлены');
    }


     
     


    //новый договор
    //   public function createAddContractByAdmin()
    //   {
    //     $user = Auth::user(); // Получаем текущего пользователя
    //     $role = $user->role->title; // Получаем его роль
    //     $clients = User::whereHas('role', function ($query) {
    //         $query->where('title', 'client'); // Фильтрация по роли 'client'
    //     })->with('userContracts') // Загружаем контракты для клиентов
    //     ->get() // Получаем коллекцию пользователей
    //     ->map(function ($client) {
    //         return [
    //             'id' => $client->id,
    //             'full_name' => $client->first_name . ' ' . $client->last_name . ' ' . $client->middle_name,
    //         ];
    //     });
    //     // dd($clients);
    //     return Inertia::render('AddContract', [
    //         'role' => $role,
    //         'clients' => $clients,
    //     ]);
    //   }


      public function storeAddContractByAdmin(Request $request)
      {
        //dd($request->all());
        $request->validate([
            'contract_number' =>['required', 'integer', 'unique:contracts,contract_number'],
            'deadline' => ['required', 'date_format:Y-m-d'],
            'create_date' => ['required', 'date_format:Y-m-d'],
            'sum' => ['required', 'integer'],
            'procent' => ['required', 'integer', 'min:0', 'max:100'],
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
            'agree_with_terms' => $request->agree_with_terms ?? false,
            'contract_status' => $request->contract_status,
            'dividends' => $request->dividends,
            'number_of_payments'=> $request->number_of_payments
        ]);
        $client->userTransactions()->create([
            'contract_id'=>$contract->id,
            'manager_id' => $manager_id,
            'date_transition' => $request->create_date,
            'sum_transition' => $request->sum,
            'sourse' =>'Договор'
        ]);
        $client->userNotifications()->create([
            'content'=> 'Был создан договор No' . $contract->contract_number,
        ]);

        // Логируем событие регистрации
        Log::create([
            'model_id' => $contract->user_id,
            'model_type' => Contract::class,
            'change' =>  'Добавление договора',
            'action' => 'Создание',
            'old_value' => null,
            'new_value' => 'Договор No ' . $contract->contract_number,
            'created_by' => Auth::id(), // ID самого пользователя
        ]);
        return redirect()->route('admin.contracts')->with('status', 'Договор успешно создан!');
      }



      //редакция договора
      public function editContractByAdmin(Contract $contract)
      {
        //dd($contract);
        // $user = Auth::user(); // Получаем текущего пользователя
        // $role = $user->role->title; // Получаем его роль
        // $clients = User::whereHas('role', function ($query) {
        //     $query->where('title', 'client'); // Фильтрация по роли 'client'
        // })->with('userContracts') // Загружаем контракты для клиентов
        // ->get() // Получаем коллекцию пользователей
        // ->map(function ($client) {
        //     return [
        //         'id' => $client->id,
        //         'full_name' => $client->first_name . ' ' . $client->last_name . ' ' . $client->middle_name,
        //     ];
        // });
        //dd($contract, $clients);
        // return Inertia::render('EditContract', [
        //     'contract' => $contract,
        //     'clients' => $clients,
        // ]);
        return response()->json([
            'contract' => $contract,
            // 'clients' => $clients
        ]);
      }

      public function updateContractByAdmin(Request $request, Contract $contract)
      {
       // dd($request->all());
        $request->validate([
            'contract_number' =>['required', 'integer', Rule::unique('contracts', 'contract_number')->ignore($contract->id)],
            'deadline' => ['required', 'date_format:Y-m-d'],
            'create_date' => ['required', 'date_format:Y-m-d'],
            'sum' => ['required', 'integer'],
            'procent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);
       
        $originalData = $contract->only(['user_id', 'contract_number', 'create_date', 'sum', 'deadline', 'procent', 'payments', 'agree_with_terms', 'contract_status', 'dividends']);
        $contract->update($request->only(['user_id', 'contract_number', 'create_date', 'sum', 'deadline', 'procent', 'payments', 'agree_with_terms', 'contract_status', 'dividends']));

        foreach ($request->only(['user_id', 'contract_number', 'create_date', 'sum', 'deadline', 'procent', 'payments', 'agree_with_terms', 'contract_status']) as $field => $newValue) {
            $oldValue = $this->normalizeValue($originalData[$field]);
            $newValue = $this->normalizeValue($newValue);
            if ($oldValue !== $newValue) {
                Log::create([
                    'model_id' => $contract->user_id,
                    'model_type' => Contract::class,
                    'change' => 'Изменено поле '.$field,
                    'action' => 'Обновление договора',
                    'old_value' => $originalData[$field],
                    'new_value' => $newValue,
                    'created_by' => Auth::id(),
                ]);
            }
        }
        $user = $contract->user; 
        $user->userNotifications()->create([
            'content'=> 'Договор No' . $contract->contract_number . ' был изменен',
        ]);
        // dd($manager_id);
        return redirect()->route('admin.contracts')->with('status', 'Договор успешно обновлен!');
      }

      public function changeStatusApplication(Application $application){
        $user = Auth::user();
        $role = $user->role->title;
        // return Inertia::render('ChangeStatusApplication', [
        //     'role' => $role,
        //     'application' => [
        //         'id' => $application->id,
        //         'status' => $application->status,
        //     ],
        // ]);
        return response()->json([
            'application' => [
                'id' => $application->id,
                'status' => $application->status,
            ],
        ]);
    }
//     public function updateStatusApplication(Request $request, Application $application)
// {   
//     //dd($application);
//     $user = Auth::user();
//     $role = $user->role->title;

//     $originalStatus = $application->status;

//     // Обновляем статус заявки
//     $application->update(['status' => $request->status]);
//     // Если статус "Исполнена", создаем транзакцию
//     if ($request->status === 'Исполнена') {
//         $message = 'Статус заявки успешно изменен! Транзакция создана.';
//         if( $application->condition === 'В срок' ) {
//             if($application->type_of_processing === 'Забрать дивиденды частично') {
//                 $application->user->userTransactions()->create([
//                     'contract_id' => $application->contract_id,
//                     'manager_id' => $application->manager_id,
//                     'user_id' => $application->user_id,
//                     'date_transition' => $application->date_of_payments,
//                     'sum_transition' => $application->dividends,
//                     'sourse' => 'Заявка',
//                 ]);


                
//                 $contract = Contract::find($application->contract_id);
//                 $mainSum = $contract->sum;
//                 $avalible_balance = $application->user->avaliable_balance;
//                 $contract->update(['sum' => $mainSum + $avalible_balance]);

//                 $application->user->userTransactions()->create([
//                     'contract_id' => $application->contract_id,
//                     'manager_id' => $application->manager_id,
//                     'user_id' => $application->user_id,
//                     'date_transition' => $application->date_of_payments,
//                     'sum_transition' => $avalible_balance,
//                     'sourse' => 'Договор',
//                 ]);
//                 $application->user->update(['avaliable_balance' => null]);
//                 $contract->update(['last_payment_date' => now()]);

//             }
//         }
//         if ($application->condition === 'Раньше срока') {
//             $contract = Contract::find($application->contract_id);
//             $contract->update(['contract_status' => false]);
//             $sumTransition = $application->sum;
//             $application->user->userTransactions()->create([
//                 'contract_id' => $application->contract_id,
//                 'manager_id' => $application->manager_id,
//                 'user_id' => $application->user_id,
//                 'date_transition' => $application->date_of_payments,
//                 'sum_transition' => $sumTransition,
//                 'sourse' => 'Заявка',
//             ]);
//         }
        
//     } else if ($request->status === 'Отменена') {
//        // dd($application->user);
//         $application->user->update(['avaliable_balance' => null]);
//         $message = 'Статус заявки успешно изменен!';
//     }
//     else {
//         $message = 'Статус заявки успешно изменен!';
//     }

//     // Логирование изменения статуса
//     if ($originalStatus !== $application->status) {
//         Log::create([
//             'model_id' => $application->user_id,
//             'model_type' => Application::class,
//             'change' => 'Изменен статус',
//             'action' => 'Изменение статуса заявки',
//             'old_value' => $originalStatus,
//             'new_value' => $application->status,
//             'created_by' => $user->id,
//         ]);
//     }
//     $user = $application->user;
//     $user->userNotifications()->create([
//         'content'=> 'Статус заявки No' . $application->id . ' был изменен',
//     ]);

//     return redirect()->route($role . '.applications')->with('status', $message);
// } 

public function updateStatusApplication(Request $request, Application $application)
{
    $user = Auth::user();
    $role = $user->role->title;

    $originalStatus = $application->status;

    // Обновляем статус заявки
    $application->update(['status' => $request->status]);

    if ($request->status === 'Исполнена') {
        $message = 'Статус заявки успешно изменен! Транзакция создана.';
        $contract = Contract::find($application->contract_id);

        if (!$contract) {
            return redirect()->back()->withErrors(['message' => 'Договор не найден.']);
        }

        if ($application->condition === 'В срок') {
            if ($application->type_of_processing === 'Забрать дивиденды частично') {
                $this->createTransaction($application, $application->dividends, 'Заявка');

                $contract->update([
                    'sum' => $contract->sum + $application->user->available_balance,
                    'last_payment_date' => now(),
                ]);

                $this->createTransaction($application, $application->user->available_balance, 'Договор');
                $application->user->update(['available_balance' => null]);
            }
        } elseif ($application->condition === 'Раньше срока') {
            $contract->update(['contract_status' => false]);
            $this->createTransaction($application, $application->sum, 'Заявка');
        }
    } elseif ($request->status === 'Отменена') {
        $application->user->update(['available_balance' => null]);
        $message = 'Статус заявки успешно изменен!';
    } else {
        $message = 'Статус заявки успешно изменен!';
    }

    // Логирование изменения статуса
    if ($originalStatus !== $application->status) {
        Log::create([
            'model_id' => $application->user_id,
            'model_type' => Application::class,
            'change' => 'Изменен статус',
            'action' => 'Изменение статуса заявки',
            'old_value' => $originalStatus,
            'new_value' => $application->status,
            'created_by' => $user->id,
        ]);
    }

    // Уведомление пользователя
    $application->user->userNotifications()->create([
        'content' => 'Статус заявки No' . $application->id . ' был изменен',
    ]);

    return redirect()->route($role . '.applications')->with('status', $message);
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
