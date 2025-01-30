<?php

    use App\Http\Controllers\AdminController;
    use App\Http\Controllers\ApplicationController;
    use Illuminate\Support\Facades\Route;



    Route::middleware('auth', 'role:admin')->group(function () {
        Route::get('/change-status-application/{application}', [AdminController::class, 'changeStatusApplication'])->name('change.status.application');
        Route::patch('/change-status-application/{application}', [AdminController::class, 'updateStatusApplication']);
    });
    Route::middleware('auth', 'role:admin,manager', 'checkActive')->group(function () {
        // Добавление заявки
        Route::get('/add-applications', [ApplicationController::class, 'createAddApplication'])->name('add.application');
        Route::post('/add-applications', [ApplicationController::class, 'storeAddApplication']);
        //просмотр заявок
        Route::get('/show-application/{application}', [ApplicationController::class, 'showApplication'])->name('show.application');
        
    });


