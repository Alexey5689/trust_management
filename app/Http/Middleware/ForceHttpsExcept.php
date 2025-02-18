<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ForceHttpsExcept
{
    /**
     * Маршруты, которые НЕ должны перенаправляться на https.
     *
     * @var array<int, string>
     */
    protected $except = [
        'health',
        // добавьте другие пути, если нужно
    ];

    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('production')) {
            // Проверяем, попадает ли текущий URL под исключение
            foreach ($this->except as $exclude) {
                // Если путь совпадает (можно использовать $request->is() )
                if ($request->is($exclude)) {
                    // просто пропускаем без forceScheme
                    return $next($request);
                }
            }
            // Для всех остальных - принудительно https
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
