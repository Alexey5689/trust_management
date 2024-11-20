<?php
 
 use App\Http\Controllers\Auth\AuthenticatedSessionController;
 use Illuminate\Support\Facades\Route;
 use App\Http\Controllers\CreatingPasswordController;

 Route::middleware('guest')->group(function () {
    //вход
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // создание пароля
    Route::get('create-password/{token}', [CreatingPasswordController::class, 'create'])
        ->name('password.set');

    Route::patch('create-password', [CreatingPasswordController::class, 'update'])
        ->name('password.create');
});