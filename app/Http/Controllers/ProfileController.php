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
    public function createProfile()
    {
        $user = Auth::user();
        $role = $user->role->title;
        // dd($user, $role);
        return Inertia::render('Profile', [
            'user' => [
                'id' => $user->id,
                'full_name' => $user->last_name . ' ' . $user->first_name . ' ' . $user->middle_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number
            ],
            'role' => $role,
            'status' => session('status'),
        ]);
    }

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

    public function updateProfile(Request $request)
    {
        $request->validate([
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['required', 'string', 'max:255'],
        ]);

        // Явно указываем тип переменной $user
        /** @var User $user */

        $user = Auth::user();
        $role = $user->role->title;
        $originalData = $user->only(['last_name', 'first_name', 'middle_name']);
        $user->update($request->only(['last_name', 'first_name', 'middle_name']));

         // Логируем изменения
        foreach ($request->only(['last_name', 'first_name', 'middle_name']) as $field => $newValue) {
            if ($originalData[$field] !== $newValue) {
                Log::create([
                    'model_id' => $user->id,
                    'model_type' => User::class,
                    'change' => 'Изменено поле'.$field,
                    'action' => 'Обновление данных',
                    'old_value' => $originalData[$field],
                    'new_value' => $newValue,
                    'created_by' =>  $user->id,
                ]);
            }
        }
       
        return redirect()->route($role . '.profile')->with('status', 'Данные обновлены');
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
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
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

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
