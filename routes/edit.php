<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    //редакция профиль пользователя. смена пароля.емена почты
    Route::get('profile/edit',[ProfileController::class, 'editProfile'])->name('profile.edit')->middleware(['role:admin', 'role:manager']);
    Route::patch('profile/edit', [ProfileController::class, 'updateProfile'])->name('profile.update')->middleware(['role:admin', 'role:manager']);
    Route::get('password/edit', [ProfileController::class, 'editPassword'])->name('password.edit')->middleware(['role:admin', 'role:manager']);
    Route::patch('password/edit', [ProfileController::class, 'updatePassword'])->name('password.update')->middleware(['role:admin', 'role:manager']);
    Route::get('email/edit', [ProfileController::class, 'editEmail'])->name('email.edit')->middleware(['role:admin']);
    Route::patch('email/edit', [ProfileController::class, 'updateEmail'])->name('email.update')->middleware(['role:admin']);
});