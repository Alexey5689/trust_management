<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResetPasswordController;

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');  // Если пользователь не авторизован
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


Route::middleware('auth')->group(function () {
    Route::get('profile/edit',[ProfileController::class, 'editProfile'])->name('profile.edit');
    Route::patch('profile/edit', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::get('password/edit', [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::patch('password/edit', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::get('email/edit', [ProfileController::class, 'editEmail'])->name('email.edit')->middleware(['role:admin']);
    Route::patch('email/edit', [ProfileController::class, 'updateEmail'])->name('email.update')->middleware(['role:admin']);


    //сброс пароля
    Route::post('/reset-password/{user}', [ResetPasswordController::class, 'resetPassword'])->name('reset.password');

});

require __DIR__ . '/auth.php';
// asdas

