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
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    public function createProfile()
    {
        $user = Auth::user();
        $role = $user->role->title;
        // dd($user, $role);
           /** @var User $user */
        $user_notification = $user->userNotifications()
        ->where('is_read', false)
        ->get()
        ->values();
        //dd($user_notification);
        return Inertia::render('Profile', [
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number
            ],
            'role' => $role,
            'status' => session('status'),
            'notifications' => $user_notification ?? []
        ]);
    }
    
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
                'created_at' => $client->created_at,
                'updated_at' => $client->updated_at,
            ];
        });
        $managers = User::whereHas('role', function($query) {
            $query->where('title', 'manager');
        })
        ->with('managerContracts')
        ->get()
        ->map(function ($manager) {
            return [
                'id' => $manager->id,
                'full_name' => $manager->last_name . ' ' . $manager->first_name . ' ' . $manager->middle_name,
                'email' => $manager->email,
                'phone_number' => $manager->phone_number,
                'active' => $manager->active,
                'created_at' => $manager->created_at,
                'updated_at' => $manager->updated_at,
            ];
        });


        return Inertia::render('AllUsers', [
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
            ],
            'clients' => $clients,
            'managers' => $managers,
            'role' => $role,
            'status' => session('status'),
        ]);
    }
    // все договоры
    public function showAllContracts(){
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        $contracts = Contract::with(['user'])->get() ->map(function ($contract) {
            $term = $this->termOfTheContract($contract->create_date, $contract->deadline);
            return [
                'id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'create_date' => $contract->create_date,
                'sum' => $contract->sum,
                'deadline' => $contract->deadline,
                'procent' => $contract->procent,
                'payments' => $contract->payments,
                'contract_status' => $contract->contract_status,
                'term' => $term,
                'user' => [
                    'id' => $contract->user->id,
                    'full_name' => $contract->user->last_name . ' ' . $contract->user->first_name . ' ' . $contract->user->middle_name,
                ],
                'created_at' => $contract->created_at,
                'updated_at' => $contract->updated_at
            ];
        });
        $clients = User::whereHas('role', function ($query) {
            $query->where('title', 'client'); // Фильтрация по роли 'client'
        })->with('userContracts') // Загружаем контракты для клиентов
        ->get() // Получаем коллекцию пользователей
        ->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' =>  $client->last_name . ' ' . $client->first_name . ' ' .$client->middle_name,
                'avaliable_balance' => $client->avaliable_balance,
                'active' => $client->active,
            ];
        });
        return Inertia::render('Contracts', [
            'role' => $role, // Передаем роль пользователя в Vue-компонент
            'contracts'=> $contracts,
            'clients' => $clients,
            'status' => session('status'),
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number
            ],
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
            $query->where('contract_status', true);
            $query->where('is_aplication', false); // Выбираем только активные договоры
        }])
        ->get()
        ->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' =>  $client->last_name. ' ' .$client->first_name. ' ' .$client->middle_name,               
                'user_contracts' => $client->userContracts->map(function ($contract) {
                    $term = $this->termOfTheContract($contract->create_date, $contract->deadline);
                    // Рассчитываем дату следующей выплаты
                    $lastPaymentDate = $contract->last_payment_date ?? $contract->create_date;
                    $nextPaymentDate = match ($contract->payments) {
                        'Ежеквартально' => Carbon::parse($lastPaymentDate)->addMonths(3),
                        'Ежегодно' => Carbon::parse($lastPaymentDate)->addYear(),
                        'По истечению срока' => Carbon::parse($contract->deadline),
                        default => null,
                    };
                   // dd($nextPaymentDate);
                    $dividends = match ($contract->payments) {
                        'Ежеквартально' => $contract->sum * ($contract->procent / 100) * $term /  $term * 4 ,
                        'Ежегодно' => $contract->sum * ($contract->procent / 100) * $term /  $term * 1 ,
                        'По истечению срока' => null,
                        default => null,
                    };
                   //dd($nextPaymentDate);
                     // Проверяем, истёк ли срок договора
                    $isExpired = now()->greaterThanOrEqualTo(Carbon::parse($contract->deadline)->endOfDay());
                    
                    //dd($nextPaymentDate);
                   //dd($isExpired);
                    $canRequestPayoutOnTime = now()->greaterThanOrEqualTo($nextPaymentDate->copy()->subDays(7)) || now()->lessThanOrEqualTo($nextPaymentDate);
                    //dd($canRequestPayoutOnTime);
                    return [
                        'id' => $contract->id,
                        'contract_number' => $contract->contract_number,
                        'sum' => $contract->sum,
                        'create_date' => $contract->create_date,
                        'deadline' => $contract->deadline,
                        'procent' => $contract->procent,
                        'manager_id' => $contract->manager_id,
                        'dividends' => $dividends,
                        'term' => $term,
                        'next_payment_date' => $nextPaymentDate,
                        'can_request_payout' => $canRequestPayoutOnTime && !$isExpired,  // Запретить заявки для истёкших договоров
                        'is_expired' => $isExpired,
                        'agree_with_terms' => $contract->agree_with_terms

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
                'dividends' => $application->dividends,
                'created_at' => $application->created_at,
                'updated_at' => $application->updated_at
            ];
        });
        return Inertia::render('Applications', [
            'role' => $role, // Передаем роль пользователя в Vue-компонент
            'applications'=> $applications,
            'clients' => $clients,
            'status' => session('status'),
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
            ],
        ]);

    }

    public function storeClientsByAdmin(Request $request ):RedirectResponse
    {
       //dd($request->all());
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
            'procent' => ['required', 'integer', 'min:0', 'max:100'],
            'manager_id' => ['required', 'integer', 'exists:users,id'],
            'payments' => ['required', 'string', 'in:Ежеквартально,Ежегодно,По истечению срока'],
        ]);
        //dd($request->all());
        DB::beginTransaction();
        try {
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
            $manager = User::find($manager_id);
            $manager->userNotifications()->create([
                'title' => 'Новый клиент',
                'content'=> 'У вас появился новый клиент: '.$user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
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
            DB::commit();
            return redirect()->route('admin.users')->with('status', [
                'Успех!',
                'Клиент успешно зарегистрирован'
            ]);
           
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.users')
            ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
        }
        
    }

     // изменение контактных данных user клиент
     public function editClientByAdmin(User $user)
     {
        //dd($user);
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
            'manager_id' => ['required', 'integer', 'exists:users,id'],
         ]);
         //dd($request->all());
        
        $originalData = $user->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']);
        $user->fill($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']));
         // Логируем изменения
        if ($user->isDirty()) {
            DB::beginTransaction();
            try{
                $dirtyFields = $user->getDirty();
                $user->save();  // Сохраняем изменения, но "грязные" поля уже зафиксированы
                foreach ($dirtyFields as $field => $newValue) {
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
                if (!empty($dirtyFields)) {
                    $user->userNotifications()->create([
                        'title' => "Контактные данные",
                        'content' => 'Ваши контактные данные были изменены',
                    ]);
                }
                DB::commit();
                return redirect()->route('admin.users')->with('status', [
                    'Успех!',
                    'Данные пользователя успешно обновлены'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('admin.users') 
                    ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
            }
        }

        $currentManager = $user->managers()->first();
        $newManager = User::find($request->manager_id);
        if ($currentManager->id !== $newManager->id) {
            DB::beginTransaction();
            try {
                $user->managers()->sync([$request->manager_id]);
                $user->userNotifications()->create([
                    'title' => "Менеджер",
                    'content'=> 'Ваш менеджер был изменен',
                ]);
                $user->userContracts()->update([
                    'manager_id' => $request->manager_id,
                ]);
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
                DB::commit();
                return redirect()->route('admin.users')->with('status', [
                    'Успех!',
                    'Менеджер пользователя был изменен'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('admin.users') 
                    ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
                
            }
                            
        }
       
        return redirect()->route('admin.users') ->with('status', ['Информация', 'Данные не изменились']);
     }



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
        DB::beginTransaction();
        try {
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
            DB::commit();
            return redirect()->route('admin.users')->with('status',  [
                'Успех!',
                'Менеджер успешно зарегистрирован!'
            ]);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.users') 
                ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
        }

        
    }


     // изменение контактных данных user менеджер
    public function editManagersByAdmin(User $user)
    {
        //dd($manager);
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
        $user->fill($request->only(['last_name', 'first_name', 'middle_name', 'email', 'phone_number']));
        if ($user->isDirty()) {
            DB::beginTransaction();
            try {
                $dirtyFields = $user->getDirty();
                $user->save();
                foreach ($dirtyFields as $field => $newValue) {
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
        
                if (!empty($dirtyFields)) {
                    $user->userNotifications()->create([
                        'title' => "Контактные данные",
                        'content' => 'Ваши контактные данные были изменены',
                    ]);
                }
                DB::commit();
                return redirect()->route('admin.users')->with('status',  [
                    'Успех!',
                    'Данные пользователя успешно обновлены'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('admin.users')
                ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
            }
        }
        return redirect()->route('admin.users') ->with('status', ['Информация', 'Данные не изменились']);
    }
      public function storeAddContractByAdmin(Request $request)
      {
        //dd($request->all());
        $request->validate([
            'contract_number' =>['required', 'integer', 'unique:contracts,contract_number'],
            'deadline' => ['required', 'date_format:Y-m-d'],
            'create_date' => ['required', 'date_format:Y-m-d'],
            'sum' => ['required', 'integer'],
            'procent' => ['required', 'integer', 'min:0', 'max:100'],
            'user_id' => ['required', 'integer'],
            'payments' => ['required', 'string', 'in:Ежеквартально,Ежегодно,По истечению срока'],
        ]);
        //dd($request->all());
         // Находим клиента по user_id из запроса
        DB::beginTransaction();
        try {
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
                'title' => "Новый договор",
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
            DB::commit();
            return redirect()->route('admin.contracts')->with('status',  [
                'Успех!',
                'Договор успешно создан!'
            ]);
            
        }catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.contracts')
            ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
        }
       
        
      }
      //редакция договора
      public function editContractByAdmin(Contract $contract)
      {
        //dd($contract);
        return response()->json([
            'contract' => $contract,
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
            'user_id' => ['required', 'integer'],
            'payments' => ['required', 'string', 'in:Ежеквартально,Ежегодно,По истечению срока'],
        ]);
       
        $originalData = $contract->only(['user_id', 'contract_number', 'create_date', 'sum', 'deadline', 'procent', 'payments', 'agree_with_terms', 'contract_status', 'dividends']);
        $contract->fill($request->only(['user_id', 'contract_number', 'create_date', 'sum', 'deadline', 'procent', 'payments', 'agree_with_terms', 'contract_status', 'dividends']));
        if ($contract->isDirty()) {
            try {
                $dirtyFields = $contract->getDirty();
                $contract->save();
                foreach ($dirtyFields as $field => $newValue) {
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
                    'title' => "Изменение договора",
                    'content'=> 'Договор № ' . $contract->contract_number . ' был изменен',
                ]);
                // dd($manager_id);
                
                return redirect()->route('admin.contracts')->with('status',  [
                    'Успех!',
                    'Договор успешно обновлен!'
                ]);
            }
            catch (\Exception $e) {
                return redirect()->route('admin.contracts')->with('status', [
                    'Неуспех:(',
                    'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже'
                ]);
            }

        }
        return redirect()->route('admin.contracts')
        ->with('status', ['Информация', 'Данные не изменились']);
       
      }

    public function changeStatusApplication(Application $application){
        $user = Auth::user();
        return response()->json([
            'application' => [
                'id' => $application->id,
                'status' => $application->status,
            ],
        ]);
    }


public function updateStatusApplication(Request $request, Application $application)
{
    
    $user = Auth::user();
    $originalStatus = $application->status;
    $actions = [
        'В обработке' => fn() => $this->handleInProgressApplication($application),
        'Согласована' => fn() => $this->handleAgreedApplication($application),
        'Исполнена' => fn() => $this->handleExecutedApplication($application, $user),
        'Отменена' => fn() => $this->handleCancelledApplication($application),
    ];

    try {
        $action = $actions[$request->status];

        $message = $action();

        $client = $application->user;
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
        $client->userNotifications()->create([
            'title' => "Изменение статуса заявки",
            'content'=> 'Статус заявки № ' . $application->id . ' был изменен',
        ]);

        // Обновляем статус заявки
        // $application->update(['status' => $request->status]);
        return redirect()->route('admin.applications')->with('status', ['Успех!', $message] );

    }catch(\Exception $e){
        return redirect()->route('admin.applications')->with('status', [
            'Неуспех:(',
            'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже'
        ]);
    }
    
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
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number
            ],
            'role' => $role,
            'logs' => $logs
        ]);
    }
}
