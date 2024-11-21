<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Проверяем, есть ли пользователь и соответствует ли его роль одной из указанных
        if ($request->user() && in_array($request->user()->role->title, $roles)) {
            return $next($request);
        }

        // Если роль не соответствует — возвращаем доступ запрещён
        abort(403, 'Access denied');
    }
}



