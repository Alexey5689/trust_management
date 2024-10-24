<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Получаем аутентифицированного пользователя
        $loggedInUser = User::with('role')->find(Auth::id());
    // Проверяем роль пользователя и перенаправляем на соответствующий маршрут
        if ($loggedInUser->isAdmin()) {
            return redirect()->intended('/admin/profile');
        }
        if ($loggedInUser->isManager()) {
            return redirect()->intended('/manager/profile');
        }
        if ($loggedInUser->isClient()) {
            return redirect()->intended('/client/profile');
        }
    // Если роль не определена или по какой-то причине не подходит, можно вернуть на основной маршрут
        return redirect()->intended('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
