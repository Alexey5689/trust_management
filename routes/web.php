<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CreatingPasswordController;

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');  
    }

    $user = Auth::user();

    if ($user->role->title === 'admin') {
        return redirect()->route('admin.profile');
    } elseif ($user->role->title === 'manager') {
        return redirect()->route('manager.profile');
    } else {
        return redirect()->route('client.profile');
    }
});


Route::middleware('guest')->group(function () {
    
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

  
    Route::get('create-password/{token}', [CreatingPasswordController::class, 'create'])
        ->name('password.set');

    Route::patch('create-password', [CreatingPasswordController::class, 'update'])
        ->name('password.create');
});
Route::middleware('auth',)->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    Route::post('/reset-password/{user}', [ResetPasswordController::class, 'resetPassword'])->name('reset.password')->middleware(['role:admin,manager']);

});

require __DIR__ . '/admin.php';
require __DIR__ . '/manager.php';
require __DIR__ . '/client.php';
require __DIR__ . '/delete.php';
require __DIR__ . '/application.php';
require __DIR__ . '/edit.php';


