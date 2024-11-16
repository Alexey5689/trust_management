<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ApplicationController;
use Illuminate\Support\Facades\App;

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
    //редакция профиль пользователя. смена пароля.емена почты
    Route::get('profile/edit',[ProfileController::class, 'editProfile'])->name('profile.edit');
    Route::patch('profile/edit', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::get('password/edit', [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::patch('password/edit', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::get('email/edit', [ProfileController::class, 'editEmail'])->name('email.edit')->middleware(['role:admin']);
    Route::patch('email/edit', [ProfileController::class, 'updateEmail'])->name('email.update')->middleware(['role:admin']);


    //сброс пароля
    Route::post('/reset-password/{user}', [ResetPasswordController::class, 'resetPassword'])->name('reset.password');


     // Добавление заявки
    Route::get('/add-applications', [ApplicationController::class, 'createAddApplication'])->name('add.application');
    Route::post('/add-applications', [ApplicationController::class, 'storeAddApplication']);
      //просмотр заявок
    Route::get('/show-application/{application}', [ApplicationController::class, 'showApplication'])->name('show.application');
    Route::get('/change-status-application/{application}', [ApplicationController::class, 'changeStatusApplication'])->name('change.status.application');

});

require __DIR__ . '/auth.php';


