<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
class ProfileController extends Controller
{
    public function create (){
        $user = Auth::user();
        $role = $user->role->title;
        // dd($user, $role);
        return Inertia::render('Profile', [
            'user' => $user,
            'role' => $role,
        ]);
    }


    public function edit(){
        $user = Auth::user();
        $role = $user->role->title;
        $userInfo = [
            'last_name' => $user->last_name,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
        ];
        // dd($userInfo);
        return Inertia::render('Edit', [
            'user' => $userInfo,
            'role' => $role,
        ]);
    }
    public function update(Request $request){
        $request->validate([
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['required', 'string', 'max:255'],
        ]);

        // Явно указываем тип переменной $user
        /** @var User $user */

        $user = Auth::user();
        $user->last_name = $request->last_name;
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->save();
        return redirect()->route('login')->with('success', 'Ваши данные успешно изменены.');
    }

}
