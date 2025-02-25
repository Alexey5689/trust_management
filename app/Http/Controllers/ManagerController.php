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
use Illuminate\Validation\Rule;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerController extends Controller
{

    public function createProfile()
    {
        $user = Auth::user();
        $role = $user->role->title;
       
           /** @var User $user */
        $user_notification = $user->userNotifications()
        ->where('is_read', false)
        ->get()
        ->values();
      
        return Inertia::render('Profile', [
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number
            ],
            'role' => $role,
            'status' => session('status'),
            'notifications' => $user_notification
        ]);
    }

    public function showClients(): Response
    {
        // Получаем его роль
        $user = Auth::user();
          /** @var User $user */
        $role = $user->role->title; // Получаем его роль
        $clients = $user->managedUsers()
       
        ->with(['userContracts' => function ($query) {
            $query->where('contract_status', true);  // Загружаем только активные контракты
        }])
        ->get()
        ->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' => $client->last_name . ' ' . $client->first_name . ' ' . $client->middle_name,
                'email' => $client->email,
                'phone_number' => $client->phone_number,
                'active' => $client->active,
                'user_contracts' => $client->userContracts->count(),
                'created_at' => $client->created_at,
                'updated_at' => $client->updated_at
            ];
        });
        $user_notification = $user->userNotifications()
        ->where('is_read', false)
        ->get()
        ->values();
        return Inertia::render('Clients', [
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
            ],
            'clients' => $clients,
            'role' => $role,
            'status' => session('status'),
            'notifications' => $user_notification ?? []
        ]);
    }

    public function showContracts(){
        $user = Auth::user();
        $role = $user->role->title; // Получаем его роль
         // Явно указываем тип переменной $user
        /** @var User $user */
        $contracts = $user->managerContracts()->with('user')->get()->map(function ($contract) {
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

        $clients = $user->managedUsers()
         
            ->with('userContracts')
            ->get()  //олучаем коллекцию пользователей
            ->map(function ($client) {
            return [
                'id' => $client->id,
                'full_name' =>  $client->last_name . ' ' . $client->first_name . ' ' . $client->middle_name,
                'avaliable_balance' => $client->avaliable_balance,
                'active' => $client->active,
            ];
        });
        $user_notification = $user->userNotifications()
        ->where('is_read', false)
        ->get()
        ->values();
        return Inertia::render('Contracts', [
            'contracts' => $contracts,
            'role' => $role,
            'clients' => $clients,
            'status' => session('status'),
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
            ],
            'notifications' => $user_notification
        ]);
    }


    public function showApplications(){
        $user = Auth::user();
        $role = $user->role->title;
         /** @var User $user */

        $clients = $user->managedUsers()
        ->where('active', true)
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
                    $dividends = match ($contract->payments) {
                        'Ежеквартально' => $contract->sum * ($contract->procent / 100) / 4,
                        'Ежегодно' => $contract->sum * ($contract->procent / 100),
                        'По истечению срока' => null,
                        default => null,
                    };
                    $afterTheExpirationDate = $contract->payments === 'По истечению срока' ? true : false;
                  
                     // Проверяем, истёк ли срок договора
                     $isExpired = now()->greaterThanOrEqualTo(Carbon::parse($contract->deadline)->endOfDay());
                   
                    $canRequestPayoutOnTime = now()->greaterThanOrEqualTo($nextPaymentDate->copy()->subDays(7)) && now()->lessThanOrEqualTo($nextPaymentDate);
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
       
        $applications = $user->managerApplications()
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
                                'sum' => $application->sum,
                                'dividends'=> $application->dividends,
                                'created_at' => $application->created_at,
                                'updated_at' => $application->updated_at
                            ];
                        });
 
        $user_notification = $user->userNotifications()
        ->where('is_read', false)
        ->get()
        ->values();
        return Inertia::render('Applications', [
            'role' => $role,
            'applications' => $applications,
            'clients' => $clients,
            'status' => session('status'),
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
                
            ],
            'notifications' => $user_notification
        ]);
      }


    public function storeClientsByManager(Request $request): RedirectResponse
    {
        

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
            'payments' => ['required', 'string', 'in:Ежеквартально,Ежегодно,По истечению срока'],
        ]);

        DB::beginTransaction();
        try{
            $client = User::create([
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
                'model_id' => $client->id,
                'model_type' => User::class,
                'change' => 'Добавление клиента',
                'action' => 'Регистрация пользователя',
                'old_value' => null,
                'new_value' => $client->email,
                'created_by' => Auth::id(), // ID самого пользователя
            ]);
            
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
                'dividends' => $request->dividends,
                'number_of_payments'=> $request->number_of_payments
            ]);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Contract::class,
                'change' => 'Добавление договора',
                'action' => 'Создание',
                'old_value' => null,
                'new_value' => 'Договор № ' . $contract->contract_number,
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
            DB::commit();
            return redirect()->route('manager.clients')->with('status',[ 'Успех', 'Клиент успешно зарегистрирован!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('manager.clients')
            ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
        }
        
  
    }

    public function editClientByManager(User $user) {
        return response()->json([
            'client'=> [
                'id' => $user->id,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ],
        ]);
    }
    public function updateClientByManager(Request $request, User $user): RedirectResponse
    {
        
        $request->validate([
            'phone_number' => ['required', 'string', 'max:12', 'min:6', Rule::unique('users', 'phone_number')->ignore($user->id)],
        ]);
        $oldPhone = $this->normalizeValue($user->phone_number);
        $newPhone = $this->normalizeValue($request->phone_number);
        
        if($oldPhone !== $newPhone){
            DB::beginTransaction();
            try{
                $user->update($request->only(['phone_number']));
                Log::create([
                    'model_id' => $user->id,
                    'model_type' => User::class,
                    'change' => 'Изменен номер телефона',
                    'action' => 'Обновление данных',
                    'old_value' => $oldPhone,
                    'new_value' => $newPhone,
                    'created_by' => Auth::id(),
                ]);  
                $user->userNotifications()->create([
                    'title' => 'Телефон',
                    'content'=> 'Номер вашего телефона был изменен на '.$request->phone_number,
                ]);
                DB::commit();
                return redirect()->route('manager.clients')->with('status',['Успех', 'Телефон успешно обновлен'] );
            }catch(\Exception $e){
                DB::rollBack();
                return redirect()->route('manager.clients')
                ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
            }
            
        }
        return redirect()->route('manager.clients') ->with('status', ['Информация', 'Данные не изменились']); 
    }
    public function storeAddContractByManager(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'contract_number' =>['required', 'integer', 'unique:contracts,contract_number'],
            'deadline' => ['required', 'date_format:Y-m-d'],
            'create_date' => ['required', 'date_format:Y-m-d'],
            'sum' => ['required', 'integer'],
            'procent' => ['required', 'integer', 'min:0', 'max:100'],
            'payments' => ['required', 'string', 'in:Ежеквартально,Ежегодно,По истечению срока'],
        ]);
        DB::beginTransaction();
        try {
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
                'agree_with_terms' => $request->agree_with_terms ?? false,
                'contract_status' => $request->contract_status,
                'dividends' => $request->dividends,
                'number_of_payments'=> $request->number_of_payments
            ]);
            $client->userTransactions()->create([
                'contract_id'=>$contract->id,
                'manager_id' => $user->id,
                'date_transition' => $request->create_date,
                'sum_transition' => $request->sum,
                'sourse' =>'Договор'
            ]);
            $client->userNotifications()->create([
                'title' => 'Транзакция',
                'content'=> 'Была создана транзакция по договору № '.$request->contract_number,
            ]);
            $client->userNotifications()->create([
                'title' => 'Договор',
                'content'=> 'Был создан договор № '.$request->contract_number,
            ]);
            Log::create([
                'model_id' => $contract->user_id,
                'model_type' => Contract::class,
                'change' =>  'Добавление договора',
                'action' => 'Создание',
                'old_value' => null,
                'new_value' => 'Договор № ' . $contract->contract_number,
                'created_by' => Auth::id(), // ID самого пользователя
            ]);
            DB::commit();
            return redirect()->route('manager.contracts')
            ->with('status', ['Успех', 'Договор успешно создан!']);
        } catch (\Exception $e) {
            return redirect()->route('admin.contracts')
            ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
        }
    }
    public function showNotifications(){
        $user = Auth::user();
        $role = $user->role->title;
         /** @var User $user */
        $notifivcations = $user->userNotifications()->get();
        $user_notification = $user->userNotifications()
        ->where('is_read', false)
        ->get()
        ->values();
        return Inertia::render('Notifications', [
            'role' => $role,
            'notifications' => $notifivcations,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
            ],
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'notification' => $user_notification
        ]);
    }

    public function updateNotification(Request $request, Notification $notification){
        
        $request->validate(['is_read' => ['required', 'boolean'],]);
        $notification->update($request->all());
        return redirect()->route('manager.notification');
    }
}

