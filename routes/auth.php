<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreatingPasswordController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;




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






Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::prefix('admin')->group(function () {
        Route::get('/profile', [ProfileController::class, 'createProfile'])->name('admin.profile')->middleware(['role:admin']);
        Route::get('/users', [AdminController::class, 'showAllUsers'])->name('admin.users')->middleware(['role:admin']);
        Route::get('/contracts',[AdminController::class, 'showAllContracts'])->name('admin.contracts')->middleware(['role:admin']);
        Route::get('/applications', [AdminController::class, 'showApplications'])->name('admin.applications')->middleware(['role:admin']);
        //рег менеджер
        Route::get('/registration-manager', [AdminController::class, 'createManagersByAdmin'])->name('admin.registration.manager')->middleware(['role:admin']);
        Route::post('/registration-manager', [AdminController::class, 'storeManagersByAdmin'])->middleware(['role:admin']);
        //изменение менеджера
        Route::get('/edit-manager/{manager}', [AdminController::class, 'editManagersByAdmin'])->name('admin.edit.manager')->middleware(['role:admin']);
        Route::patch('/edit-manager/{manager}', [AdminController::class, 'updateManagersByAdmin'])->middleware(['role:admin']);
        //изменение клиента
        Route::get('/edit-client/{client}', [AdminController::class, 'editClientByAdmin'])->name('admin.edit.client')->middleware(['role:admin']);
        Route::patch('/edit-client/{client}', [AdminController::class, 'updateClientByAdmin'])->middleware(['role:admin']);



        //рег клиент
        Route::get('/registration-client', [AdminController::class, 'createClientsByAdmin'])->name('admin.registration.client');
        Route::post('/registration-client', [AdminController::class, 'storeClientsByAdmin']);


         //Добавление договора
         Route::get('/add-contract', [AdminController::class, 'createAddContractByAdmin'])->name('admin.add.contract');
         Route::post('/add-contract', [AdminController::class, 'storeAddContractByAdmin']);

         //редактирование договора
         Route::get('/edit-contract/{contract}', [AdminController::class, 'editContractByAdmin'])->name('admin.edit.contract');
         Route::patch('/edit-contract/{contract}', [AdminController::class, 'updateContractByAdmin']);
    });

    Route::prefix('manager')->group(function () {
        Route::get('/profile', [ProfileController::class, 'createProfile'])->name('manager.profile')->middleware(['role:manager']);
        Route::get('/clients',[ManagerController::class, 'showClients'])->name('manager.clients');
        Route::get('/contracts', [ManagerController::class, 'showContracts'])->name('manager.contracts');
        Route::get('/applications', [ManagerController::class, 'createApplications'])->name('manager.applications');
        //рег клиент
        Route::get('/registration-client', [ManagerController::class, 'createClientsByManager'])->name('manager.registration.client');
        Route::post('/registration-client', [ManagerController::class, 'storeClientsByManager']);
        //изменение клиента
        Route::get('/edit-client/{client}', [ManagerController::class, 'editClientByManager'])->name('manager.edit.client');
        Route::patch('/edit-client/{client}', [ManagerController::class, 'updateClientByManager']);
        //Добавление договора
        Route::get('/add-contract', [ManagerController::class, 'createAddContractByManager'])->name('manager.add.contract');
        Route::post('/add-contract', [ManagerController::class, 'storeAddContractByManager']);


    });

    Route::prefix('client')->group(function () {
        Route::get('/profile', [ProfileController::class, 'createProfile'])->name('client.profile');
        Route::get('/contracts', [ClientController::class, 'showContracts'])->name('client.contracts');
        Route::get('/applications', [ClientController::class, 'showApplications'])->name('client.applications');
        Route::get ('/balance-transactions', [ClientController::class, 'showBalanceTransactions'])->name('client.balance.ransactions');
    });

});


// Route::get('/clients',[AdminController::class, 'showClients'])->name('admin.clients')->middleware(['role:admin']);
// Route::get('/managers',[AdminController::class, 'showManagers'])->name('admin.managers')->middleware(['role:admin']);