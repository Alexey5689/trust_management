<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
                'password' => $user->password = Hash::make($user->password),
            ],
            'role' => $role,
            'status' => session('status'),
        ]);
    }

    public function editProfile()
    {
        $user = Auth::user();
        $role = $user->role->title;
        // dd($userInfo);
        // return Inertia::render('Edit', [
        //     'user' => [
        //         'last_name' => $user->last_name,
        //         'first_name' => $user->first_name,
        //         'middle_name' => $user->middle_name,
        //     ],
        //     'role' => $role,
        // ]);
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
        $user->last_name = $request->last_name;
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->save();
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

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function editEmail()
    {
        $user = Auth::user();
        $role = $user->role->title;
        $userEmail = $user->email;
        //dd($userEmail);
        return Inertia::render('EditEmail', [
            'role' => $role,
            'userEmail' => $userEmail
        ]);
    }
    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
        ]);

        // Явно указываем тип переменной $user
        /** @var User $user */

        $user = Auth::user();

        $user->email = $request->email;
        $user->save();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
