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
// вход
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);


    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');


// создание пароля
    Route::get('create-password/{token}', [CreatingPasswordController::class, 'create'])
    ->name('password.set');

    Route::patch('create-password', [CreatingPasswordController::class, 'update'])
        ->name('password.create');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');


    Route::prefix(('admin'))->group(function () {
        Route::get('/',[DashboardController::class, 'create'])->name('admin.dashboard');
        Route::get('/profile', [ProfileController::class, 'create'])->name('admin.profile');
        Route::get('/clients',[AdminController::class, 'showClients'])->name('admin.clients');
        Route::get('/managers',[AdminController::class, 'showManagers'])->name('admin.managers');
        Route::get('/contracts',[AdminController::class, 'showAllContracts'])->name('admin.contracts');
        //рег менеджер
        Route::get('/registration-manager', [AdminController::class, 'createManagersByAdmin'])->name('admin.registration.manager');
        Route::post('/registration-manager', [AdminController::class, 'storeManagersByAdmin']);
        //рег клиент
        Route::get('/registration-client', [AdminController::class, 'createClientsByAdmin'])->name('admin.registration.client');
        Route::post('/registration-client', [AdminController::class, 'storeClientsByAdmin']);
    });

    Route::prefix(('manager'))->group(function () {
        Route::get('/',[DashboardController::class, 'create'])->name('manager.dashboard');
        Route::get('/profile', [ProfileController::class, 'create'])->name('manager.profile');
        Route::get('/clients',[ManagerController::class, 'showClients'])->name('manager.clients');
        //
        Route::get('/managers', [ManagerController::class, 'showManagers'])->name('manager.managers');
        //рег клиент
        // Route::get('/registration-client', [RegisteredClientController::class, 'create'])->name('registration.client');
        // Route::post('/registration-client', [RegisteredClientController::class, 'store']);
    });

    Route::prefix(('client'))->group(function () {
        Route::get('/',[DashboardController::class, 'create'])->name('client.dashboard');
        Route::get('/profile', [ProfileController::class, 'create'])->name('client.profile');
        Route::get('/contracts', [ClientController::class, 'showContracts'])->name('contracts');

        Route::get('/edit',[ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/edit', [ProfileController::class, 'update'])->name('profile.update');
        //
        Route::get('/clients', [ClientController::class, 'showClients'])->name('client.clients');
        Route::get('/managers', [ClientController::class, 'showManagers'])->name('client.managers');
    });

});
