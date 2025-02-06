<?php
use App\Http\Controllers\DeleteController;
use Illuminate\Support\Facades\Route;

    Route::middleware('auth', 'role:admin')->group(function () {
        //удаление user, contract
        Route::delete('/delete-user/{user}', [DeleteController::class, 'deleteUser'])->name('delete.user');
        Route::delete('/delete-contract/{contract}', [DeleteController::class, 'deleteContract'])->name('delete.contract');
    });