<?php
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\ProfileController;
    use App\Http\Controllers\AdminController;

    Route::middleware('auth', 'role:admin')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/profile', [ProfileController::class, 'createProfile'])->name('admin.profile');
            Route::get('/users', [AdminController::class, 'showAllUsers'])->name('admin.users');
            Route::get('/contracts',[AdminController::class, 'showAllContracts'])->name('admin.contracts');
            Route::get('/applications', [AdminController::class, 'showApplications'])->name('admin.applications');
            //рег менеджер
            Route::get('/registration-manager', [AdminController::class, 'createManagersByAdmin'])->name('admin.registration.manager');
            Route::post('/registration-manager', [AdminController::class, 'storeManagersByAdmin']);
            //изменение менеджера
            Route::get('/edit-manager/{manager}', [AdminController::class, 'editManagersByAdmin'])->name('admin.edit.manager');
            Route::patch('/edit-manager/{manager}', [AdminController::class, 'updateManagersByAdmin']);
            //изменение клиента
            Route::get('/edit-client/{client}', [AdminController::class, 'editClientByAdmin'])->name('admin.edit.client');
            Route::patch('/edit-client/{client}', [AdminController::class, 'updateClientByAdmin']);
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
    });