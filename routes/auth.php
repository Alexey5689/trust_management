<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisteredManagerController;
use App\Http\Controllers\RegisteredClientController;
use App\Http\Controllers\CreatingPasswordController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;




Route::middleware('guest')->group(function () {
    //вход
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);


    // Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    //     ->name('password.request');

    // Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    //     ->name('password.email');

    // Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    //     ->name('password.reset');

    // Route::post('reset-password', [NewPasswordController::class, 'store'])
    //     ->name('password.store');


    // создание пароля
    Route::get('create-password/{token}', [CreatingPasswordController::class, 'create'])
        ->name('password.set');

    Route::patch('create-password', [CreatingPasswordController::class, 'update'])
        ->name('password.create');
});






Route::middleware('auth')->group(function () {
    // Route::get('verify-email', EmailVerificationPromptController::class)
    //     ->name('verification.notice');

    // Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    //     ->middleware(['signed', 'throttle:6,1'])
    //     ->name('verification.verify');

    // Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    //     ->middleware('throttle:6,1')
    //     ->name('verification.send');

    // Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
    //     ->name('password.confirm');

    // Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');


    Route::prefix('admin')->group(function () {
        Route::get('/profile', [ProfileController::class, 'create'])->name('admin.profile');
        Route::get('/clients',[AdminController::class, 'showClients'])->name('admin.clients');
        Route::get('/managers',[AdminController::class, 'showManagers'])->name('admin.managers');
        Route::get('/contracts',[AdminController::class, 'showAllContracts'])->name('admin.contracts');
        //рег менеджер
        Route::get('/registration-manager', [AdminController::class, 'createManagersByAdmin'])->name('admin.registration.manager');
        Route::post('/registration-manager', [AdminController::class, 'storeManagersByAdmin']);
        //изменение менеджера
        Route::get('/edit-manager/{manager}', [AdminController::class, 'editManagersByAdmin'])->name('admin.edit.manager');
        Route::patch('/edit-manager/{manager}', [AdminController::class, 'updateManagersByAdmin']);

        //Удаление менеджера
        Route::delete('/delete-user/{user}', [AdminController::class, 'deleteUserByAdmin'])->name('admin.delete.user');
        //рег клиент
        Route::get('/registration-client', [AdminController::class, 'createClientsByAdmin'])->name('admin.registration.client');
        Route::post('/registration-client', [AdminController::class, 'storeClientsByAdmin']);
        //изменение менеджера
        Route::get('/edit-client/{client}', [AdminController::class, 'editClientByAdmin'])->name('admin.edit.client');
        Route::patch('/edit-client/{client}', [AdminController::class, 'updateClientByAdmin']);

         //сброс пароля
         Route::post('/reset-password/{user}', [AdminController::class, 'resetPassword'])->name('admin.reset.password');


    });

    Route::prefix('manager')->group(function () {
        Route::get('/profile', [ProfileController::class, 'create'])->name('manager.profile');
        Route::get('/clients',[ManagerController::class, 'showClients'])->name('manager.clients');
        Route::get('/contracts', [ManagerController::class, 'showContracts'])->name('manager.contracts');
        //рег клиент
        Route::get('/registration-client', [ManagerController::class, 'createClientsByManager'])->name('manager.registration.client');
        Route::post('/registration-client', [ManagerController::class, 'storeClientsByManager']);
        //изменение клиента
        Route::get('/edit-client/{client}', [ManagerController::class, 'editClientByManager'])->name('manager.edit.client');
        Route::patch('/edit-client/{client}', [ManagerController::class, 'updateClientByManager']);
        //Добавление договора
        Route::get('/add-contract', [ManagerController::class, 'createAddContractByManager'])->name('manager.add.contract');
        Route::post('/add-contract', [ManagerController::class, 'storeAddContractByManager']);

        //сброс пароля
        Route::post('/reset-password/{user}', [AdminController::class, 'resetPassword'])->name('manager.reset.password');
    });

    Route::prefix('client')->group(function () {
        Route::get('/profile', [ProfileController::class, 'create'])->name('client.profile');
        Route::get('/contracts', [ClientController::class, 'showContracts'])->name('client.contracts');
    });

});


