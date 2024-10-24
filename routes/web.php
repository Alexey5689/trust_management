<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;





// Route::get('/', function () {
//     return redirect()->route('login');  // Перенаправляем на маршрут login
// });

Route::middleware('guest')->group(function () {
    // вход
    Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/', [AuthenticatedSessionController::class, 'store']);


    // Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    //     ->name('password.request');

    // Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    //     ->name('password.email');

    // Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    //     ->name('password.reset');

    // Route::post('reset-password', [NewPasswordController::class, 'store'])
    //     ->name('password.store');


    // // создание пароля
    // Route::get('create-password/{token}', [CreatingPasswordController::class, 'create'])
    //     ->name('password.set');

    // Route::patch('create-password', [CreatingPasswordController::class, 'update'])
    //     ->name('password.create');
});

Route::get('/success-registration', function () {
    return Inertia::render('Messages/SuccessRegistration');
})->name('success-registration');

require __DIR__ . '/auth.php';
