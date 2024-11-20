<?php
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\ProfileController;
    use App\Http\Controllers\AdminController;

    Route::middleware('auth')->group(function () {
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
            Route::get('/registration-client', [AdminController::class, 'createClientsByAdmin'])->name('admin.registration.client')->middleware(['role:admin']);
            Route::post('/registration-client', [AdminController::class, 'storeClientsByAdmin'])->middleware(['role:admin']);
    
    
             //Добавление договора
             Route::get('/add-contract', [AdminController::class, 'createAddContractByAdmin'])->name('admin.add.contract')->middleware(['role:admin']);
             Route::post('/add-contract', [AdminController::class, 'storeAddContractByAdmin'])->middleware(['role:admin']);
    
             //редактирование договора
             Route::get('/edit-contract/{contract}', [AdminController::class, 'editContractByAdmin'])->name('admin.edit.contract')->middleware(['role:admin']);
             Route::patch('/edit-contract/{contract}', [AdminController::class, 'updateContractByAdmin'])->middleware(['role:admin']);
        });
    });