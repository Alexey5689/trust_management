<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth', 'role:admin,manager')->group(function () {
    //редакция профиль пользователя. смена пароля.емена почты
    Route::get('profile-edit',[ProfileController::class, 'editProfile'])->name('profile.edit');
    Route::patch('profile-edit', [ProfileController::class, 'updateProfile'])->name('profile.update');

    Route::get('password-edit', [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::patch('password-edit', [ProfileController::class, 'updatePassword'])->name('password.update');
    
    Route::get('email-edit', [ProfileController::class, 'editEmailByAdmin'])->name('email.edit');
    Route::patch('email-edit', [ProfileController::class, 'updateEmailByAdmin'])->name('email.update');

});