<?php
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\ManagerController;
    use App\Http\Controllers\ProfileController;

    Route::middleware('auth', 'role:manager')->group(function () {
        Route::prefix('manager')->group(function () {
            Route::get('/profile', [ProfileController::class, 'createProfile'])->name('manager.profile');
            Route::get('/clients',[ManagerController::class, 'showClients'])->name('manager.clients');
            Route::get('/contracts', [ManagerController::class, 'showContracts'])->name('manager.contracts');
            Route::get('/applications', [ManagerController::class, 'showApplications'])->name('manager.applications');
            //рег клиент
            Route::get('/registration-client', [ManagerController::class, 'createClientsByManager'])->name('manager.registration.client');
            Route::post('/registration-client', [ManagerController::class, 'storeClientsByManager']);
            //изменение клиента
            Route::get('/edit-client/{user}', [ManagerController::class, 'editClientByManager'])->name('manager.edit.client');
            Route::patch('/edit-client/{user}', [ManagerController::class, 'updateClientByManager']);
            //Добавление договора
            Route::get('/add-contract', [ManagerController::class, 'createAddContractByManager'])->name('manager.add.contract');
            Route::post('/add-contract', [ManagerController::class, 'storeAddContractByManager']);
        });
    });
    