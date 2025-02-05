<?php
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AdminController;


  
    Route::middleware('auth', 'role:admin')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/profile', [AdminController::class, 'createProfile'])->name('admin.profile');
            Route::get('/users', [AdminController::class, 'showAllUsers'])->name('admin.users');
            Route::get('/contracts',[AdminController::class, 'showAllContracts'])->name('admin.contracts');
            Route::get('/applications', [AdminController::class, 'showAllApplications'])->name('admin.applications');
            Route::get('/notifications', [AdminController::class, 'showNotification'])->name('admin.notification');
            Route::get('/logs', [AdminController::class, 'createLogs'])->name('admin.logs');
            
            Route::get('/registration-manager', [AdminController::class, 'createManagersByAdmin'])->name('admin.registration.manager');
            Route::post('/registration-manager', [AdminController::class, 'storeManagersByAdmin']);
           
            Route::get('/edit-manager/{user}', [AdminController::class, 'editManagersByAdmin'])->name('admin.edit.manager');
            Route::patch('/edit-manager/{user}', [AdminController::class, 'updateManagersByAdmin']);
    
            Route::get('/registration-client', [AdminController::class, 'createClientsByAdmin'])->name('admin.registration.client');
            Route::post('/registration-client', [AdminController::class, 'storeClientsByAdmin']);
          
            Route::get('/edit-client/{user}', [AdminController::class, 'editClientByAdmin'])->name('admin.edit.client');
            Route::patch('/edit-client/{user}', [AdminController::class, 'updateClientByAdmin']);
           
            Route::get('/add-contract', [AdminController::class, 'createAddContractByAdmin'])->name('admin.add.contract');
            Route::post('/add-contract', [AdminController::class, 'storeAddContractByAdmin']);
           
            Route::get('/edit-contract/{contract}', [AdminController::class, 'editContractByAdmin'])->name('admin.edit.contract');
            Route::patch('/edit-contract/{contract}', [AdminController::class, 'updateContractByAdmin']);

        });
    });