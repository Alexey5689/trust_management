<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Log;

class ProfileController extends Controller
{
    // public function createProfile()
    // {
    //     $user = Auth::user();
    //     $role = $user->role->title;
    //     // dd($user, $role);
    //        /** @var User $user */
    //     $user_notification = $user->userNotifications()
    //     ->where('is_read', false)
    //     ->get()
    //     ->values();
    //     //dd($user_notification);
    //     return Inertia::render('Profile', [
    //         'user' => [
    //             'id' => $user->id,
    //             'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
    //             'email' => $user->email,
    //             'phone_number' => $user->phone_number
    //         ],
    //         'role' => $role,
    //         'status' => session('status'),
    //         'notifications' => $user_notification ?? []
    //     ]);
    // }

    public function editProfile()
    {
        $user = Auth::user();
        return response()->json([
            'user' => [
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
            ],
        ]);
    }

    // public function updateProfile(Request $request)
    // {
    //     $request->validate([
    //         'first_name' => ['required','string' ,'max:255', 'min:2'],
    //         'last_name' => ['required','string','max:255', 'min:2'],
    //         'middle_name' => ['required','string','max:255', 'min:2'],
    //     ]);

    //     // Явно указываем тип переменной $user
    //     /** @var User $user */

    //     $user = Auth::user();
    //     $role = $user->role->title;
    //     $originalData = $user->only(['last_name', 'first_name', 'middle_name']);
    //     $user->update($request->only(['last_name', 'first_name', 'middle_name']));

    //      // Логируем изменения
    //     foreach ($request->only(['last_name', 'first_name', 'middle_name']) as $field => $newValue) {
    //         if ($originalData[$field] !== $newValue) {
    //             Log::create([
    //                 'model_id' => $user->id,
    //                 'model_type' => User::class,
    //                 'change' => 'Изменено поле '. $field,
    //                 'action' => 'Обновление данных',
    //                 'old_value' => $originalData[$field],
    //                 'new_value' => $newValue,
    //                 'created_by' =>  $user->id,
    //             ]);
    //         }
    //     }
    //     $user->userNotifications()->create([
    //         'title' => 'Контактные данные',
    //         'content'=> 'Ваши контактные данные были изменены',
    //     ]);
       
    //     return redirect()->route($role . '.profile')->with('status', ['Успех!', 'Данные обновлены']);
    // }
//     public function updateProfile(Request $request)
// {
//     $request->validate([
//         'first_name' => ['required','string' ,'max:255', 'min:2'],
//         'last_name' => ['required','string','max:255', 'min:2'],
//         'middle_name' => ['required','string','max:255', 'min:2'],
//     ]);

//     /** @var User $user */
//     $user = Auth::user();
//     $role = $user->role->title;

//     $originalData = $user->only(['last_name', 'first_name', 'middle_name']);
    
//     try {
//         $user->update($request->only(['last_name', 'first_name', 'middle_name']));

//         // Логирование изменений
//         foreach ($user->getDirty() as $field => $newValue) {
//             Log::create([
//                 'model_id' => $user->id,
//                 'model_type' => User::class,
//                 'change' => 'Изменено поле '. $field,
//                 'action' => 'Обновление данных',
//                 'old_value' => $originalData[$field] ?? 'не было',
//                 'new_value' => $newValue,
//                 'created_by' =>  $user->id,
//             ]);
//         }

//         // Уведомление пользователя
//         $user->userNotifications()->create([
//             'title' => 'Контактные данные',
//             'content'=> 'Ваши контактные данные были изменены',
//         ]);

//         return redirect()->route($role . '.profile')
//             ->with('status', ['Успех!', 'Данные обновлены']);

//     } catch (\Exception $e) {
//         return redirect()->route($role . '.profile')
//             ->with('status', ['Ошибка!', 'Не удалось обновить данные.']);
//     }
// }
public function updateProfile(Request $request)
{
    $request->validate([
        'first_name' => ['required', 'string', 'max:255', 'min:2'],
        'last_name' => ['required', 'string', 'max:255', 'min:2'],
        'middle_name' => ['required', 'string', 'max:255', 'min:2'],
    ]);

    /** @var User $user */
    $user = Auth::user();
    $role = $user->role->title;

    $originalData = $user->only(['last_name', 'first_name', 'middle_name']);

    $user->fill($request->only(['last_name', 'first_name', 'middle_name']));
    // Проверка на изменения перед сохранением
    if ($user->isDirty()) {
        try {
            $dirtyFields = $user->getDirty();
            $user->save();  // Сохраняем изменения, но "грязные" поля уже зафиксированы
            
            foreach ($dirtyFields as $field => $newValue) {
                Log::create([
                    'model_id' => $user->id,
                    'model_type' => User::class,
                    'change' => 'Изменено поле ' . $field,
                    'action' => 'Обновление данных',
                    'old_value' => $originalData[$field],
                    'new_value' => $newValue,
                    'created_by' => $user->id,
                ]);
            }
    
            // Уведомление пользователя
            $user->userNotifications()->create([
                'title' => 'Контактные данные',
                'content' => 'Ваши контактные данные были изменены',
            ]);
    
            return redirect()->route($role . '.profile')
                ->with('status', ['Успех!', 'Данные обновлены']);
        } catch (\Exception $e) {
            return redirect()->route($role . '.profile')
                ->with('status', ['Неуспех:(', 'Что то пошло не так, повторите попытку снова. Если после второй попытки ничего не получилось, повторите позже']);
            
        }
       
    }
    // Если изменений нет, просто редиректим без сообщений
    return redirect()->route($role . '.profile')
        ->with('status', ['Информация', 'Данные не изменились']);
}



    public function editPassword()
    {
        $user = Auth::user();
        $role = $user->role->title;
        // dd($userInfo);
        return Inertia::render('EditPassword', [
            'role' => $role,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // Явно указываем тип переменной $user
        /** @var User $user */

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();
        // Логируем изменения
        Log::create([
            'model_id' => $user->id,
            'model_type' => User::class,
            'change' => 'password',
            'action' => 'Изменение пароля',
            'old_value' => '********', // Не указываем старое значение
            'new_value' => '********', // Указываем сообщение, а не пароль
            'created_by' => $user->id,
        ]);
        $user->userNotifications()->create([
            'title' => 'Пароль',
            'content'=> 'Ваши пароль были изменены',
        ]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function editEmailByAdmin()
    {
        $user = Auth::user();
        $userEmail = $user->email;
        return response()->json([
            'email' => $userEmail,
        ]); 
    }
    public function updateEmailByAdmin(Request $request)
    {
        $request->validate([
           'email' => ['required','string','email','max:255','min:6', 'unique:users'],
        ]);

        // Явно указываем тип переменной $user
        /** @var User $user */

        $user = Auth::user();
        $oldEmail = $user->email;
        $user->email = $request->email;
        $user->save();
        Log::create([
            'model_id' => $user->id,
            'model_type' => User::class,
            'change' => 'email',
            'action' => 'Изменение email',
            'old_value' => $oldEmail,
            'new_value' => $request->email,
            'created_by' => Auth::id(),
        ]);
        $user->userNotifications()->create([
            'title' => 'Email',
            'content'=> 'Ваши email были изменены',
        ]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
