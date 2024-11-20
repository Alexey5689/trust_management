<?php
    use App\Http\Controllers\ApplicationController;
    use Illuminate\Support\Facades\Route;
    Route::middleware('auth')->group(function () {
        // Добавление заявки
        Route::get('/add-applications', [ApplicationController::class, 'createAddApplication'])->name('add.application');
        Route::post('/add-applications', [ApplicationController::class, 'storeAddApplication']);
        //просмотр заявок
        Route::get('/show-application/{application}', [ApplicationController::class, 'showApplication'])->name('show.application');
        Route::get('/change-status-application/{application}', [ApplicationController::class, 'changeStatusApplication'])->name('change.status.application')->middleware(['role:admin']);
        Route::patch('/change-status-application/{application}', [ApplicationController::class, 'updateStatusApplication'])->middleware(['role:admin']);
    });


