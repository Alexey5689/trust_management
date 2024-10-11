<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;

class CreatingPasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        // Валидация только нового пароля и его подтверждения
        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // Обновляем пароль для пользователя
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect(route('login'));
    }
}

