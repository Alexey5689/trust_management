<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use App\Notifications\PasswordEmail;
class RegisteredManagerController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('RegisterManager');
    }

    public function store(Request $request): RedirectResponse
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
        return redirect(route('success-registration'));
    }
}
