<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->isActive()) {
            Auth::logout();
            return redirect()->route('login') ->with('status', ['Неуспех:(', 'Ваш аккаунт деактивирован.']);
        }

        return $next($request);
    }
}

