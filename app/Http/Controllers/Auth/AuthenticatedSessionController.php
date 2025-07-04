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

        $loggedInUser = User::with('role')->find(Auth::id());

        $roleRedirects = [
            'admin' => '/admin/profile',
            'manager' => '/manager/profile',
            'client' => '/client/profile',
        ];

        $role = $loggedInUser->role->title ?? null;

        if (isset($roleRedirects[$role])) {
            return redirect()->intended($roleRedirects[$role]);
        }

    // Если роль не определена или по какой-то причине не подходит, можно вернуть на основной маршрут
        return redirect('/')->with(['status' => 'Роль пользователя не определена.']);
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
